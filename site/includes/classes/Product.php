<?php

use Picqer\Barcode\BarcodeGeneratorHTML as BarcodeGeneratorHTML;
use Illuminate\Database\Eloquent\SoftDeletes;

class Products extends Model{
	use SoftDeletes;

	protected $table = 'product';

	/**
	* The attributes that are mass assignable.
	*
	* @var array
	*/

	protected $fillable = [

	];

	/**
	* The attributes that should be hidden for arrays.
	*
	* @var array
	*/

	protected $hidden = [

	];

	protected $dates = ['created_at', 'updated_at', 'deleted_at'];

	protected static $api_queryable = [
	];

    public static $breadcrumb_divider = "<div class='breadcrumb-divider'><span class='far fa-chevron-right'></span></div>";

	public static $gait_options = [
		'Pacer'			=>	'Pacer',
		'Trotter'		=>	'Trotter',
		'Road Horse'	=>	'Road Horse',
	];
	public static $sex_options = [
		'Broodmare'	=>	'Broodmare',
		'Colt'		=>	'Colt',
		'Filly'		=>	'Filly',
		'Gelding'	=>	'Gelding',
		'Mare'		=>	'Mare',
		'Stallion'	=>	'Stallion',
	];
	public static $colour_options = [
		'Appalossa'	=>	'Appalossa',
		'Bay'		=>	'Bay',
		'Black'		=>	'Black',
		'Brown'		=>	'Brown',
		'Buckskin'	=>	'Buckskin',
		'Chestnut'	=>	'Chestnut',
		'Dun'		=>	'Dun',
		'Grey'		=>	'Grey',
		'Palomino'	=>	'Palomino',
		'White'		=>	'White',
	];
    public static $marketplace_category_options = [
        'sulkies'       =>  'Sulkies',
        'harness'       =>  'Harness',
        'carts'         =>  'Carts',
        'gear'          =>  'Gear',
        'transport'     =>  'Transport',
        'stable'        =>  'Stable',
    ];
    public static $marketplace_condition_options = [
        'new'           =>  'New',
        'excellent'     =>  'Excellent',
        'good'          =>  'Good',
        'fair'          =>  'Fair',
        'parts'         =>  'Parts / project',
    ];
    public static $marketplace_payment_mode_options = [
        'contact'       =>  'Buyer contacts seller',
        'deposit'       =>  'Take a deposit online',
        'full_payment'  =>  'Take full payment online',
    ];
    public static $marketplace_fulfilment_options = [
        'pickup'            =>  'Pickup',
        'freight'           =>  'Freight available',
        'buyer_to_arrange'  =>  'Buyer to arrange',
    ];

	public function newQuery() {
        global $class_user;
        return parent::newQuery()
            ->where('product.user_id', '=', $class_user->authorised->id)
			->where('product.status', 1);
    }

	public static function boot() {
        parent::boot();

        self::deleting(function($model) {
			$model->status = 0;
			$model->save();

			$listings = $model->listings;
			foreach($listings as $listing) {
				$listing->delete();
			}
        });
    }

	public function productMeta(){
        return $this->hasMany('ProductMeta','identifier', 'id');
    }

    public function meta() {
        return $this->hasMany('ProductMeta','identifier', 'id');
    }

    public function metaValue($field, $default=null) {
        $meta = $this->metaArray($this->meta);
        return (isset($meta[$field]) && $meta[$field] !== '' ? $meta[$field] : $default);
    }

    public function listingKind() {
        return $this->metaValue('listing_kind', 'horse');
    }

    public function isMarketplace() {
        return $this->listingKind() == 'marketplace';
    }

    public function marketplaceCategory() {
        return $this->metaValue('marketplace_category', 'Equipment');
    }

    public function marketplaceMeta() {
        $meta = $this->metaArray($this->meta);
        $fields = [
            'marketplace_category',
            'marketplace_condition',
            'marketplace_make',
            'marketplace_model',
            'marketplace_year',
            'marketplace_payment_mode',
            'marketplace_deposit_amount',
            'marketplace_fulfilment',
            'marketplace_dimensions',
        ];
        $return = [];
        foreach($fields as $field) {
            if(isset($meta[$field]) && $meta[$field] !== '') {
                $return[$field] = $meta[$field];
            }
        }
        return $return;
    }

    public function children() {
        return $this->hasMany('Products','parent_id', 'id');
    }

    public function parent() {
        return $this->belongsTo('Products','parent_id', 'id');
    }

    public function brand() {
        return $this->belongsTo('ProductBrand','brand_id', 'id');
    }

	public function price(){
		return ($this->price_special>0?$this->price_special:$this->price);
	}

    public function reviews() {
        return $this->hasMany('ProductReview','product_id', 'id');
    }

	public function client() {
        return $this->belongsTo('Clients','client_id', 'id');
    }

	public function location() {
        return $this->belongsTo('Location','location_id', 'id');
    }

	public function region() {
        return $this->belongsTo('LocationRegion','region_id', 'id');
    }

	public function currency() {
        return $this->belongsTo('Currency','currency_id', 'id');
    }

	public function listings() {
        return $this->hasMany('ProductListing','product_id', 'id');
    }

	public function listing() {
		return $this->belongsTo('ProductListing','listing_id', 'id');
	}

	public function watchlists() {
        return $this->hasMany('ProductWatchlist','product_id', 'id');
    }

	public function clientsLost() {
        return $this->belongsToMany('Clients', 'product_lost', 'product_id', 'client_id');
    }

	public function saleRecord() {
		return $this->belongsTo('SaleRecord','sale_record_id', 'id');
	}

    public function categories() {
        $categories = self::join('product_meta','product.id','=','product_meta.value')
            ->where([['identifier',$this->id],['field','category'],['type','category'],['status',1],['hide',0]])
            ->orderBy('product_meta.id','ASC')
            ->get();

		return $categories;
	}

    public function feURL($full=false) {
        if($this->type =='product') {
            $url = "listing/".$this->id."/".$this->slug."/";
        } else {
            $url = "category/".$this->slug."/";
        }
        if(!$full) {
            $url = FE_rel.$url;
        } else {
            $url = FE_url.$url;
        }

        return $url;
    }

    public function feURLBrand($full=false, $brand=null) {

        $url = $this->feURL($full);
        if($brand != null) {
            $brand_url = $brand->feURL($full);
            if(!$full) {
                $url = str_replace(FE_rel.$this->type."/",$brand_url,$url);
            } else {
                $url = str_replace(FE_url.$this->type."/",$brand_url,$url);
            }
        }

        return $url;
    }

    public function feURLReviews($full=false) {
        $url = $this->feURL($full);
        $url .= "reviews/";
        return $url;
    }

    public function feURLReview($full=false) {
        $url = $this->feURL($full);
        $url .= "review/";
        return $url;
    }

    public function beLink() {
        global $zulu;
        return "<a href='".$zulu->link_page('product',['query'=>['Action'=>'edit','id'=>$this->id]])."'>".$this->name."</a>";
    }

    public function collectionIds() {
        $ids = [$this->id];
        $children = $this->children;
        foreach($children as $child) {
            $ids[] = $child->id;
        }
        return $ids;
    }

    function images($config=[]) {
		global $class_user,$zulu,$class_product;

        $data = ['main'=>null,'gallery'=>[],'_all'=>[]];
		foreach(glob($class_product->image_path.$this->id."/*.{jpg,jpeg,png,gif}", GLOB_BRACE) as $row) {
			if(is_file($row)) {
				$file = str_replace($class_product->image_path.$this->id."/","",$row);
				if($config['variant_main'] == $file || ($this->image == $file && $data['main'] == NULL)) {
					$data['main'] = $class_product->image_fold.$this->id."/".$file;
				} else {
					$data['gallery'][] = $class_product->image_fold.$this->id."/".$file;
				}
				$data['_all'][] = $class_product->image_fold.$this->id."/".$file;
			}
		}

        if($data['main']==NULL) {
			$data['main'] = $data['gallery'][0];
			unset($data['gallery'][0]);

			if(trim($data['main']) == NULL) {
                if($this->type_variant == '2' && (!isset($config['variant_check']) || !$config['variant_check'])) {
                    $parent = $this->parent;
                    $data = $parent->images(['variant_main'=>$this->image]);
                    $skip_rest = true;
                } elseif($this->type_variant == '1') {
                    $child_products = $this->children;
                    foreach($child_products as $child_product) {
                        $data = $child_product->images(['variant_check'=>true]);
                        if(trim($data['main']) != NULL) {
                            $skip_rest = true;
                            break;
                        }
                    }
                }
			}
		} else {
            $main_key = array_search($data['main'], $data['_all']);
            if($main_key > 0) {
                unset($data['_all'][$main_key]);
                $data['_all'] = array_merge([$data['main']], $data['_all']);
            }
        }
		if(!$skip_rest) {
			$data['_path'] = $class_product->image_fold.$this->id."/";
			if(count($data['_all']) < 1) {
				$data = NULL;
			}
		}
		return $data;
	}

    public function parentArray($array=[]) {
		if($this->parent_id > 0) {
			$parent = $this->parent;
            $array[] = $parent;
            $array = $parent->parentArray($array);
		}
		return $array;
	}

    public static function breadcrumbsBuild($links=[], $home=false) {
        if($home) {
            array_unshift($links, self::homeBreadcrumbLink());
        }
        return "<div class='breadcrumbs category-breadcrumbs'>".implode(self::$breadcrumb_divider, $links)."</div>";
    }

    public function breadcrumbLinks($last_link=false, $brand=null) {
        global $class_setting;

        $links = [];

        if(isset($class_setting->data['ws_shop_category']) && $class_setting->data['ws_shop_category'] > 0) {
            $root_category_id = $class_setting->data['ws_shop_category'];
        } else {
            $root_category_id = 0;
        }

        if($this->type == 'product') {
            $categories = $this->categories();
            if(count($categories) > 0) {
                $category = $categories[0];
                $parents = array_merge(array_reverse($category->parentArray()), [$category]);
            }
        } else {
            $parents = array_reverse($this->parentArray());
        }
        foreach($parents as $parent) {
            if($root_category_id != $parent->id) {
                $links[] = $parent->breadcrumbLink(true, $brand);
            }
		}
        if($this->type == 'category') {
            $links[] = $this->breadcrumbLink($last_link, $brand);
        }

        return implode(self::$breadcrumb_divider, $links);
    }

    public function breadcrumbLink($link=true, $brand=null) {
        global $zulu;

        $label = stripslashes($this->name);
        $rel = ($brand!=null?$this->feURLBrand(false,$brand):$this->feURL());
        $link_filter = ['Pg','view','category','brand_view','brand','ProductID','ProductTitle'];

        if($link) {
            $label = "<a href='".$zulu->front_link($rel, ['self'=>true, 'filter'=>$link_filter])."'>".$label."</a>";
        }
        $crumb = "<div class='breadcrumb'>".$label."</div>";

        return $crumb;
    }

    public static function homeBreadcrumbLink() {
        global $zulu;

        $crumb = "<div class='breadcrumb home'><a href='".$zulu->front_link(FE_rel."browse/")."'><span class='fas fa-home'></span></a></div>";

        return $crumb;
    }

    public static function searchBreadcrumbLink($link=false) {
        global $zulu;

        $label = "Search Results";
        if($link) {
            $label = "<a href='".$zulu->front_link(FE_rel."browse/", ['self'=>true, 'filter'=>['Pg','view','category','brand_view']])."'>".$label."</a>";
        }

        return "<div class='breadcrumb search'>".$label."</div>";
    }

    public static function searchBreadcrumbLinks($link=false) {

        $links = [
            self::homeBreadcrumbLink(),
            self::searchBreadcrumbLink($link),
        ];

        return "<div class='breadcrumbs category-breadcrumbs'>".implode(self::$breadcrumb_divider, $links)."</div>";
    }

    public static function viewModeBreadcrumbLink($view=null) {
        global $class_product;

        if($view != null && isset($class_product->config->view_modes)) {
            $crumb = "<div class='breadcrumb view'>".$class_product->config->view_modes[$view]['title']."</div>";
        } else {
            $crumb = null;
        }

        return $crumb;
    }

    public static function viewModeBreadcrumbLinks($view=null) {
        global $class_product;

        $links = [
            self::homeBreadcrumbLink(),
            self::viewModeBreadcrumbLink($view),
        ];

        return "<div class='breadcrumbs category-breadcrumbs'>".implode(self::$breadcrumb_divider, $links)."</div>";
    }

    public function reviewsBreadcrumbLink($link=false) {
        global $zulu;

        $label = "Reviews";
        if($link) {
            $label = "<a href='".$zulu->front_link($this->feURLReviews())."'>".$label."</a>";
        }

        return "<div class='breadcrumb reviews'>".$label."</div>";
    }

    public function reviewBreadcrumbLink($link=false) {
        global $zulu;

        $label = "Write a Review";
        if($link) {
            $label = "<a href='".$zulu->front_link($this->feURLReview())."'>".$label."</a>";
        }

        return "<div class='breadcrumb review'>".$label."</div>";
    }

    public function reviewSummary() {
        global $zulu;

        $reviews = $this->reviews;
        $review_total = count($reviews);
        $review_ratings = $this->getReviewRating();

        if($review_total > 0) {

            $rating_rows = [];
            $rating_average = $review_ratings['average'];
            for($i=ProductReview::$stars; $i>0; $i--) {
                $rating_count = $review_ratings[$i];
                $rating_percent = $rating_count / $review_total * 100;
                $rating_rows[] = "
                <div class='rating-row'>
                    <div class='rating-label'>".$i." star</div>
                    <div class='rating-bar-container'>
                        <div class='rating-bar'>
                            <div class='rating-bar-filled' style='width:".$rating_percent."%'></div>
                        </div>
                    </div>
                    <div class='rating-count'>".$rating_count."</div>
                </div>
                ";
            }

            $review_summary = "
            <div class='review-summary'>
                ".$this->reviewRatingStars()."
                <p>".$rating_average." out of ".ProductReview::$stars." stars from ".$review_total." rating".$zulu->s($review_total)."</p>
                <div class='rating-rows'>".implode('',$rating_rows)."</div>
            </div>
            ";
        } else {
            $review_summary = null;
        }

        return $review_summary;
    }

    public function featuredReviews() {
        global $class_setting;

        if($class_setting->data['ws_shop_product_review_feature_max'] > 0) {
            $count = $class_setting->data['ws_shop_product_review_feature_max'];
        } else {
            $count = ProductReview::$featured_count;
        }

        return $this->reviews()->where([['status','live'],['title','!=','']])->orderBy('feature','DESC')->orderBy('verified','DESC')->orderBy('id','DESC')->take($count)->get();
    }

    public function setReviewRating() {
        global $zulu;

        $reviews = $this->reviews;
        $review_total = count($reviews);
        $rating_totals = [];

        if($review_total > 0) {
            foreach($reviews as $review) {
                if(!isset($rating_totals[$review->rating])) {
                    $rating_totals[$review->rating] = 0;
                }
                $rating_totals[$review->rating]++;
            }
            $rating_sum = 0;
            foreach($rating_totals as $key=>$val) {
                $rating_sum += $key * $val;
            }

            $rating_average = sprintf("%.1f", ($rating_sum / $review_total));
            $rating_whole = floor($rating_average);
            $rating_dec = $rating_average - $rating_whole;
            if($rating_dec <= 0) {
                $rating_average = $rating_whole;
            }

        } else {
            $rating_average = 0;
        }

        ProductMeta::updateOrCreate([
            'identifier'    =>  $this->id,
            'field'         =>  'review_rating',
        ],[
            'value'         =>  $rating_average,
        ]);
        for($i=ProductReview::$stars; $i>0; $i--) {
            $rating_count = (isset($rating_totals[$i])?$rating_totals[$i]:0);
            ProductMeta::updateOrCreate([
                'identifier'    =>  $this->id,
                'field'         =>  'review_rating_'.$i,
            ],[
                'value'         =>  $rating_count,
            ]);
        }

        return;
    }

    public function getReviewRating() {
        $reviews = $this->reviews;
        $meta = $this->metaArray($this->meta);
        $return = ['average'=>$meta['review_rating'], 'total'=>count($reviews)];
        for($i=ProductReview::$stars; $i>0; $i--) {
            $return[$i] = $meta['review_rating_'.$i];
        }
        return $return;
    }

    public function reviewRatingStars() {
        $ratings = $this->getReviewRating();
        $rating_average = $ratings['average'];

        if($rating_average > 0) {
            $rating_stars = [];
            $rating_whole = floor($rating_average);
            $rating_dec = $rating_average - $rating_whole;

            for($i=1; $i<=ProductReview::$stars; $i++) {
                if($rating_whole >= $i || ($rating_whole == $i-1 && ($rating_dec > ProductReview::$star_half_max))) {
                    $class = "full";
                    $icon = "fas fa-star";
                } elseif($rating_whole == $i-1 && ($rating_dec >= ProductReview::$star_half_min)) {
                    $class = "half";
                    $icon = "fas fa-star-half-alt";
                } else {
                    $class = "empty";
                    $icon = "far fa-star";
                }
                $rating_stars[] = "<span class='rating-star ".$class."'><i class='".$icon."'></i></span>";
            }

            return "<div class='rating-stars'>".implode('',$rating_stars)."</div>";
        }

        return null;
    }

    public function ratingSchema() {
        $rating_summary = $this->getReviewRating();

        return json_encode([
            '@type'         =>  'AggregateRating',
            'ratingValue'   =>  $rating_summary['average'],
            'reviewCount'   =>  $rating_summary['total'],
        ]);
    }

    public function reviewSchema() {

        $reviews = $this->reviews()->where([['status','live'],['title','!=','']])->orderBy('feature','DESC')->orderBy('verified','DESC')->orderBy('id','DESC')->get();
        $schema_array = [];
        foreach($reviews as $review) {
            $schema_array[] = $review->reviewSchema();
        }

        return json_encode($schema_array);
    }

	public function isAuction() {
		if($this->listing_type == 'auction') {
			return true;
		}
		return false;
	}

	public function locationFlag() {
		if($this->location > 0) {
			$location = $this->location;
			$image = $location->image();
			return $image;
		}

		return '';
	}

	public function locationText() {
		if($this->location_id > 0) {
			$location = $this->location;
			return $location->name;
		}
		return 'Other';
	}

	public function regionText() {
		if($this->region_id > 0) {
			$region = $this->region;
			return $region->name;
		}
		$meta = $this->metaArray($this->meta);
		return $meta['location_other'];
	}

	public function locationFullText() {
		$parts = [];
		$parts[] = $this->regionText();
		if($this->location_id > 0) {
			$parts[] = $this->locationText();
		}
		return implode(', ', $parts);
	}

	public function isLive() {
		return $this->live;
	}

	public function listingBannerHTML($client=false) {
		global $zulu;

		$listing = $this->listing;
		$location_name = $this->locationFullText();
		$location_flag = $this->locationFlag();
		$images = $this->images();
		$price = $this->price;
		$is_auction = $this->isAuction();
		$is_live = $this->isLive();
		$current_views = $this->statViews();
		$current_watchers = $this->statWatchers();
		$listing_class = $auction_status_label = '';
		$client_has_bid = $client_bid_leader = false;
		$currency_code = $this->currencyCode();
		$has_sold = $this->hasSold();

		if($is_live) {
			$status_label = 'Active';
		}
		if($is_auction) {
			$bids = $listing->bids;
			$current_bids = count($bids);
			$price_label = 'Starting price';
			$reserve_met = $listing->hasMetReserve();
			$no_reserve = $listing->hasNoReserve();
			if($current_bids > 0) {
				$price_label = 'Current Bid';
				if(CLIENT_auth && $listing->clientHasBid($_SESSION['user']['id'])) {
					$client_has_bid = true;
					if($listing->isClientBidLeader($_SESSION['user']['id'])) {
						$client_bid_leader = true;
						$price_label = "Your Bid";
						$listing_class = 'status-green';
						$auction_status_label = 'You Lead!';
					} else {
						$listing_class = 'status-red';
						$auction_status_label = 'You Were Outbid!';
					}
				} else {
					if($reserve_met) {
						$auction_status_label = 'Reserve Met';
						$status_label = 'Reserve is Met!';
						if($client) {
							$listing_class = 'status-green';
						}
					} else {
						$auction_status_label = 'Reserve Not Met';
					}
				}
			} else {
				if($no_reserve) {
					$auction_status_label = 'No Reserve';
				} else {
					$auction_status_label = 'Reserve Not Met';
				}
			}

			if(!$is_live) {
				$auction_status_label = '';
	            if($has_sold) {
	                $price_label = "Sold For";
					$status_label = 'Sold';
	            } else {
	                $price_label = "Unsold";
					$status_label = 'Unsold';
	            }
				if($client) {
					$listing_class = 'status-grey';
				}
	        }
		} else {
			$price_label = 'Asking price';
			if(!$is_live) {
	           $listing_class = 'status-grey';
			   $status_label = 'Ended';
		   }
		}
		if($client) {
			$auction_status_label = null;
		}

		return "<div class='listing-banner box ".$listing_class."'>
			<a href='".$listing->feURL()."' class='listing-link'>
				<div class='row'>
					".($images['main']?"<div class='col-4'>
						<div class='image-container'>
							<img src='".MAIN_rel.$images['main']."' alt='".$this->name."' class='main' />
							".($location_flag?"<img src='".$location_flag."' alt='".$location_name." flag' class='flag' />":null)."
						</div>
					</div>":null)."
					<div class='col-8'>
						<p class='location'>".$location_name."</p>
						<h4 class='listing-name'>".stripslashes($this->name)."</h4>

						".($client?"
						<div class='listing-status'>
							<span class='listing-status-text'>".$status_label."</span>
						</div>
						<p>
							".($is_auction?"
							".$zulu->icon('pennant', 's')." Bids: ".$current_bids."<br />
							".$zulu->icon('binoculars', 's')." Bidders/Watchers: ".$current_watchers."<br />
							":"
							".$zulu->icon('binoculars', 's')." Watchers: ".$current_watchers."<br />
							")."
							".$zulu->icon('eye', 's')." Views: ".$current_views."<br />
						</p>":"
						<div class='listing-status'>
							".$zulu->icon('check-circle', 'r')."
						</div>
						<p>
							".$zulu->icon('calendar', 'r')." ".$this->spec_age." year".$zulu->s($this->spec_age)." of age<br />
							".$zulu->icon('venus', 's')." ".$this->spec_dam."<br />
							".$zulu->icon('mars', 's')." ".$this->spec_sire."<br />
						</p>")."

						<div class='price-info'>
							<p class='price-status'>".$price_label."</p>
							<p class='price'>".($this->is_poa ? "Contact the Seller" : $currency_code." $".number_format($price))."</p>
						</div>
						<div class='time-info'>
							".($auction_status_label?"<p class='auction-status'>".$auction_status_label."</p>":null)."
							<p class='time-summary red'>".$listing->timeSummary()."</p>
						</div>
					</div>
				</div>
			</a>
		</div>";
	}

	public function feURLWithdraw($full=false) {
		$url = $this->feURL($full);
        $url .= "withdraw/";
        return $url;
    }

	public static function enquiryForm() {
		global $form_edit, $class_setting;

        return "
		<form method=\"post\" action=\"\">
			<div class=\"form-block single\">
				<div class=\"field\">
					".$form_edit->input_html('input', 'name', $_POST['name'], ['placeholder'=>'Your Name *'])."
				</div>
				<div class=\"field\">
					".$form_edit->input_html('input', 'phone', $_POST['phone'], ['placeholder'=>'Your Phone Number *'])."
				</div>
				<div class=\"field\">
					".$form_edit->input_html('email', 'email', $_POST['email'], ['placeholder'=>'Your Email Address'])."
				</div>
				<div class=\"field\">
					".$form_edit->input_html('textarea', 'message', $_POST['message'], ['placeholder'=>'Your message to the seller *'])."
				</div>
				<div class=\"field submit\">
					".$form_edit->input_html('submit', 'submit', 'Send')."
					".$form_edit->input_html('recaptcha', 'form_listing_contact', null, ['google_captcha_key'=>$class_setting->data['ws_module_google_captcha_api_key']])."
					".$form_edit->input_html('hidden', 'action', 'listing_enquiry')."
				</div>
			</div>
		</form>
		";
    }

	public function addWatchlist($client_id) {
		$watchlist = ProductWatchlist::where([['product_id', $this->id], ['client_id', $client_id]])->first();
		if(!$watchlist) {
			$watchlist = new ProductWatchlist;
			$watchlist->product_id = $this->id;
			$watchlist->client_id = $client_id;
			$watchlist->save();
		}
	}

	public function removeWatchlist($client_id) {
		$watchlist = ProductWatchlist::where([['product_id', $this->id], ['client_id', $client_id]])->first();
		if($watchlist) {
			$watchlist->delete();
		}
	}

	public function onWatchlist($client_id) {
		$watchlist = ProductWatchlist::where([['product_id', $this->id], ['client_id', $client_id]])->first();
		return $watchlist ? true : false;
	}

	public function statViews() {
		$listing = $this->listing;
		if(!$listing) {
			return 0;
		}
		return $listing->statViews();
	}

	public function statWatchers() {
		$watchlists = ProductWatchlist::where([['product_id', $this->id]])->get();
		return count($watchlists);
	}

	public function hasSold() {
		if($this->sale_record_id > 0) {
			return true;
		}
		return false;
	}

	public function currencyCode() {
		$currency = $this->currency;
		if($this->currency_id <= 0 || !$currency) {
			$location = Location::find(PriceList::$default_location_id);
			$currency = $location->currency;
		}

		return $currency->code;
	}

	public function canEdit() {
		$listing = $this->listing;
		if(!$listing) {
			return false;
		}
		return $listing->canEdit();
    }

	public function canRelist() {
		$listing = $this->listing;
		if(!$listing) {
			return false;
		}
		return $listing->canRelist();
    }

	public function sessionReset() {
		$upload_dir = "file/temp/listing_".session_id()."/";
		$upload_dir_images = $upload_dir."images/";
		$upload_dir_pedigree = $upload_dir."pedigree/";
		$upload_path = MAIN_path.$upload_dir;
		$upload_path_images = MAIN_path.$upload_dir_images;
		$upload_path_pedigree = MAIN_path.$upload_dir_pedigree;
		if(isset($_SESSION['LISTING']['images'])) {
			foreach($_SESSION['LISTING']['images'] as $image) {
				@unlink($upload_path_images.$image);
			}
		}
		if(isset($_SESSION['LISTING']['meta']['pedigree_file']) && $_SESSION['LISTING']['meta']['pedigree_file']) {
			@unlink($upload_path_pedigree.$filename);
		}
		@rmdir($upload_path_images);
		@rmdir($upload_path_pedigree);
		@rmdir($upload_path);

		unset($_SESSION['LISTING']);
	}

	private function loadSession() {
		global $zulu;

		$_SESSION['LISTING'] = [
			'meta'		=>	[],
			'images'	=>	[],
			'token'		=>	$zulu->serial(),
		];

		$product_row = $this->getAttributes();
		$product_meta = $this->metaArray($this->meta);
		$listing = $this->listing;
		$listing_row = $listing->getAttributes();
		foreach($product_row as $key=>$val) {
			$_SESSION['LISTING'][$key] = stripslashes($val);
		}
		foreach($product_meta as $key=>$val) {
			$_SESSION['LISTING']['meta'][$key] = stripslashes($val);
		}
		foreach($listing_row as $key=>$val) {
			$_SESSION['LISTING'][$key] = $val;
		}
		$_SESSION['LISTING']['date_close'] = $_SESSION['LISTING']['time_close'];
		$_SESSION['LISTING']['time_close'] = $zulu->dateTimezone($_SESSION['LISTING']['time_close'], 'g:ia');
		$_SESSION['LISTING']['date_close_fixed'] = $_SESSION['LISTING']['meta']['date_close_fixed'];

		//-- load in files
		$upload_dir = "file/temp/listing_".session_id()."/";
		$upload_dir_images = $upload_dir."images/";
		$upload_dir_pedigree = $upload_dir."pedigree/";
		$upload_path = MAIN_path.$upload_dir;
		$upload_path_images = MAIN_path.$upload_dir_images;
		$upload_path_pedigree = MAIN_path.$upload_dir_pedigree;
		@mkdir($upload_path);
		@mkdir($upload_path_images);
		@mkdir($upload_path_pedigree);

		$product_dir = MAIN_path."file/product/".$this->id."/";
		$images = $this->images();
		foreach($images['_all'] as $image) {
			copy(MAIN_path.$image, $upload_path_images.basename($image));
			$_SESSION['LISTING']['images'][] = basename($image);
		}
		if($product_meta['pedigree_file']) {
			copy($product_dir."pedigree/".$product_meta['pedigree_file'], $upload_path_pedigree.$product_meta['pedigree_file']);
		}

	}

	public function loadSessionEdit() {
		$this->loadSession();
		$_SESSION['LISTING']['edit_id'] = $this->id;
	}

	public function loadSessionRelist() {
		$this->loadSession();
		$_SESSION['LISTING']['relist_id'] = $this->id;
		unset($_SESSION['LISTING']['sale_id']);
	}

	public function feURLEdit() {
		$url = LINK_listing_edit.$this->id."/";
        return $url;
    }

	public function feURLRelist() {
		$url = LINK_listing_relist.$this->id."/";
        return $url;
    }

	public function imageSlider() {
		global $class_website, $zulu;

		$images = $this->images();
		$html = '';
		$slides = [];

		if(count($images['_all']) > 0) {
			foreach($images['_all'] as $image) {
				$slides[] = "<div class='slide'>
					<a href='".MAIN_rel.$image."' rel='image'>
						<img alt='gallery image of ".stripslashes($this->name)."' src='".$zulu->thumb($image,'w=500&h=350&far=1&bg=ffffff')."' />
					</a>
				</div>";
			}

			$result = $class_website->slider_build(0, [
				'slides'	=>	$slides,
				'autoplay'	=>	false,
			]);
			if($result['success']) {
				$html = "<div class='listing-image-slider'>".$result['html']."</div>";
			}
		}

        return $html;
    }

	public static function featuredListingSlider() {
		global $class_website, $zulu, $class_user, $class_product;

		$limit = 12;
		$sql_where = [];
		$sql_where[] = "status = 1";
		$sql_where[] = "live = 1";
		$sql_where[] = "hide = 0";
		$sql_where[] = "user_id = '".$class_user->authorised->id."'";
		$sql_where[] = "type = 'product'";
		$sql_where[] = "sys = '0'";
		$sql_where[] = "(type_variant = '1' OR type_variant = '0')";
		$sql_where[] = "NOT EXISTS(select * from product_meta pm_listing_kind where pm_listing_kind.identifier=product.id and pm_listing_kind.field='listing_kind' and pm_listing_kind.value='marketplace')";
		$sql_where[] = "EXISTS(select * from product_listing pl where product.listing_id=pl.id and add_feature=1)";

		//Query FULL
		$row_PRODF = $zulu->table_data($class_product->SQL_table_product,0,['join'=>$join,'sort'=>'sort ASC, name ASC','field'=>['*,product.id AS id'],'where'=>$sql_where,'group'=>'product.id','test'=>false,'limit'=>$limit]);

		if(count($row_PRODF) <= 0) {
			return null;
		}

		$pblock = [];
		foreach($row_PRODF as $row_PROD) {
			$class_product->vars->data = $row_PROD;
			$pblock[] = "<li>".$class_product->product_block()."</li>";
		}

		$token = $zulu->serial(6);
		$html = "<div class='featured-listing-slider'>
			<ul class=\"product-box row4 ls-master\" id='slider-".$token."'>".implode('', $pblock)."</ul>
		</div>";

		$zulu->template->jquery_code[] = "let slider_".$token." = tns({
			container: '#slider-".$token."',
			items: 4,
			slideBy: 4,
			loop: true,
			controls: true,
			controlsPosition: 'bottom',
			autoplay: false,
			nav: false,
			navPosition: 'bottom',
			mouseDrag: true,
			lazyload: true,
			gutter: 10,
			responsive: {
				0: {
					items: 1,
					slideBy: 1,
				},
				600: {
					items: 3,
					slideBy: 3,
				},
				1024: {
					items: 4,
					slideBy: 4,
				}
			},
		});";
		$zulu->include_tiny_slider();

        return $html;
    }

	public function listingNewEmail() {
		global $zulu;

		$client = $this->client;
		$listing = $this->listing;

		$to = $client->email;
		$sub = "New Listing Created";
		$mess = "<p>Hi ".stripslashes($client->name_first).".</p>
		<p>Your listing <b>".stripslashes($this->name)."</b> is now live.</p>
		<p>
			Starting Price: $".number_format($this->price)."<br />
			End Date: ".$zulu->dateTimezone($listing->time_close)."<br />
		</p>
		<p><a href='".$this->feURL(true)."'>View your listing here</a></p>";

		$zulu->mail_send($to, $sub, $mess, '', false, ['client'=>true, 'user_id'=>$client->user_id, 'toggle'=>'1', 'object'=>'product', 'object_id'=>$this->id]);
	}

	public function listingWithdrawnEmail() {
		global $zulu;

		$client = $this->client;
		$listing = $this->listing;

		$to = $client->email;
		$sub = "Listing Withdrawn";
		$mess = "<p>Hi ".stripslashes($client->name_first).".</p>
		<p>Your listing <b>".stripslashes($this->name)."</b> has been withdrawn.</p>";

		$zulu->mail_send($to, $sub, $mess, '', false, ['client'=>true, 'user_id'=>$client->user_id, 'toggle'=>'1', 'object'=>'product', 'object_id'=>$this->id]);
	}

	public function listingRelistEmail() {
		global $zulu;

		$client = $this->client;
		$listing = $this->listing;

		$to = $client->email;
		$sub = "Listing Relisted";
		$mess = "<p>Hi ".stripslashes($client->name_first).".</p>
		<p>Your listing <b>".stripslashes($this->name)."</b> has been relisted.</p>
		<p>
			Starting Price: $".number_format($this->price)."<br />
			End Date: ".$zulu->dateTimezone($listing->time_close)."<br />
		</p>
		<p><a href='".$this->feURL(true)."'>View your listing here</a></p>";

		$zulu->mail_send($to, $sub, $mess, '', false, ['client'=>true, 'user_id'=>$client->user_id, 'toggle'=>'1', 'object'=>'product', 'object_id'=>$this->id]);
	}

	public function familyTreeHTML() {
		$product_meta = $this->metaArray($this->meta);
		if(!$product_meta['sire']) {
			$product_meta['sire'] = $this->spec_sire;
		}
		if(!$product_meta['dam']) {
			$product_meta['dam'] = $this->spec_dam;
		}
		if($this->spec_sire) {
			$product_meta['sire'] = $this->spec_sire;
		}
		if($this->spec_dam) {
			$product_meta['dam'] = $this->spec_dam;
		}

		$html = "<span class='label' title='{$this->name}'>{$this->name}</span>";
		if($product_meta['sire'] && $product_meta['dam']) {
			$html .= "<div class='branch lv1'>
				<div class='entry'>
					<span class='label' title='{$product_meta['sire']}'>{$product_meta['sire']}</span>";
			if($product_meta['sireofsire'] && $product_meta['damofsire']) {
				$html .= "<div class='branch lv2'>
					<div class='entry'>
						<span class='label' title='{$product_meta['sireofsire']}'>{$product_meta['sireofsire']}</span>
					</div>
					<div class='entry'>
						<span class='label' title='{$product_meta['damofsire']}'>{$product_meta['damofsire']}</span>
					</div>
				</div>";
			}
			$html .= "</div>
				<div class='entry'>
					<span class='label' title='{$product_meta['dam']}'>{$product_meta['dam']}</span>";
			if($product_meta['sireofdam'] && $product_meta['damofdam']) {
				$html .= "<div class='branch lv2'>
					<div class='entry'>
						<span class='label' title='{$product_meta['sireofdam']}'>{$product_meta['sireofdam']}</span>
					</div>
					<div class='entry'>
						<span class='label' title='{$product_meta['damofdam']}'>{$product_meta['damofdam']}</span>
					</div>
				</div>";
			}
			$html .= "</div>
			</div>";
		}

		return $html;
	}

	public function getFamilyData() {
		$usta = new UstaApi;

		$horse = $usta->horseSearch($this->name);
		//print_r($horse);exit;
		if($horse) {
			if($horse->url) {
				$result = $usta->getHorseDetails($horse->url);
			} else {
				$result = null;
			}
			//print_r($result);exit;
			if(!$result) {
				$result = $horse;
				$sire = $usta->horseSearch($horse->sireName);
				if($sire) {
					$result->sireOfSireName = $sire->sireName;
					$result->damOfSireName = $sire->damName;
				}
				$dam = $usta->horseSearch($horse->damName);
				if($dam) {
					$result->sireOfDamName = $dam->sireName;
					$result->secondDamName = $dam->damName;
				}
			}

		} else {
			//-- lookup from user entered sire and dam
			$result = new stdClass;
			if($this->spec_sire) {
				$result->sireName = $this->spec_sire;
				$sire = $usta->horseSearch($this->spec_sire);
				if($sire) {
					$result->sireOfSireName = $sire->sireName;
					$result->damOfSireName = $sire->damName;
				}
			}
			if($this->spec_dam) {
				$result->damName = $this->spec_dam;
				$dam = $usta->horseSearch($this->spec_dam);
				if($dam) {
					$result->sireOfDamName = $dam->sireName;
					$result->secondDamName = $dam->damName;
				}
			}
		}

		$data = [
			'sire' => ucwords(strtolower($result->sireName)),
			'dam' => ucwords(strtolower($result->damName)),
			'sireofsire' => ucwords(strtolower($result->sireOfSireName)),
			'damofsire' => ucwords(strtolower($result->damOfSireName)),
			'sireofdam' => ucwords(strtolower($result->sireOfDamName)),
			'damofdam' => ucwords(strtolower($result->secondDamName)),
			'usta' => json_encode($result),
		];
		//print_r($data);exit;
		foreach($data as $key=>$val) {
			ProductMeta::updateOrCreate([
	            'identifier'    =>  $this->id,
	            'field'         =>  $key,
	        ],[
	            'value'         =>  $val,
	        ]);
		}

		return true;
	}

}
