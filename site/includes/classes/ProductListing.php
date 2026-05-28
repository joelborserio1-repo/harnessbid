<?php

use Illuminate\Database\Eloquent\SoftDeletes;

class ProductListing extends Model{
    use SoftDeletes;

	protected $table = 'product_listing';

    public static $file_folder = "product-listing/";

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

    public static $bid_increments = [
		10000		=>	100,
		100000		=>	1000,
		1000000		=>	10000,
		10000000	=>	100000,
		100000000	=>	1000000,
	];
    public static $listing_extension = '1 minute';
    public static $listing_extension_timeframe = '1 minute';

	public function product() {
        return $this->belongsTo('Products','product_id', 'id');
    }

    public function client() {
        return $this->belongsTo('Clients','client_id', 'id');
    }

    public function bids() {
        return $this->hasMany('ProductListingBid','listing_id', 'id');
    }

    public static function boot() {
        parent::boot();

        self::creating(function($model) {
			global $class_user, $zulu;
			$model->user_id = $class_user->authorised->id;
        });
    }

    public function newQuery() {
        global $class_user;
        return parent::newQuery()
            ->where('product_listing.user_id', '=', $class_user->authorised->id);
    }

    public function name() {
        $product = $this->product;
        return stripslashes($product->name);
    }

    public static function file_rel() {
        return MAIN_rel."file/".self::$file_folder;
    }

    public static function file_path() {
        return MAIN_path."file/".self::$file_folder;
    }

    public function feURL($full=false) {
        $product = $this->product;
        return ($full?FE_url:FE_rel)."listing/".$product->id."/".$product->slug."/";
    }

    public function priceBoxHTML() {
        global $zulu, $form_edit;

        $product = $this->product;
        $is_auction = $product->isAuction();
        $client = $product->client;
        $box_footer = $bid_fields = $reserve_text = $watchlist_buttons = null;
        $is_live = $this->isLive();
        $owned_listing = $on_watchlist = false;
        $currency_code = $product->currencyCode();

        if(CLIENT_auth) {
            if($client->id == $_SESSION['user']['id']) {
                $owned_listing = true;
            }
            if($product->onWatchlist($_SESSION['user']['id'])) {
                $on_watchlist = true;
            }
        }

        if($is_auction) {
            $met_reserve = $this->hasMetReserve();
            $no_reserve = $this->hasNoReserve();
            $bids = $this->bids()->latest()->get();
            $bid_count = count($bids);
            $bid_day_arr = [];
            $container_class = '';
            $client_has_bid = $client_bid_leader = false;
            $bid_increment = $this->bidIncrement();
            $min_bid = $this->minNextBid();
            foreach($bids as $bid) {
                $day = strtotime('today', $bid->stat_add);
                if(!isset($bid_day_arr[$day])) {
                    $bid_day_arr[$day] = [];
                }
                $bid_day_arr[$day][] = $bid;
            }
            krsort($bid_day_arr);
            $bid_history_html = "";
            foreach($bid_day_arr as $day=>$day_bids) {
                $day_bid_arr = [];
                foreach($day_bids as $day_bid) {
                    $own_bid = CLIENT_auth && $day_bid->client_id == $_SESSION['user']['id'] ? true : false;
                    $day_bid_arr[] = "<dt class='".($own_bid?'own-bid':null)."'>$".number_format($day_bid->bid_amount)."</dt>
                    <dd class='".($own_bid?'own-bid':null)."'>".$zulu->dateTimezone($day_bid->stat_add, 'h:ia')."</dd>";
                }
                $bid_history_html .= "<p class='day'>".$zulu->dateTimezone($day, 'D, j M')."</p>
                <dl class='dl-horizontal dt-left dd-right'>
                    ".implode('', $day_bid_arr)."
                </dl>";
            }

            if($bid_count > 0) {
                $heading = "Current Bid";
                if(CLIENT_auth && $this->clientHasBid($_SESSION['user']['id'])) {
                    $client_has_bid = true;
                    if($this->isClientBidLeader($_SESSION['user']['id'])) {
                        $client_bid_leader = true;
                        $heading = "You Lead!";
                        $container_class = 'status-green';
                    } else {
                        $heading = "You're Outbid!";
                        $container_class = 'status-red';
                    }
                }
            } else {
                $heading = "Starting Price";
            }
            $price = $this->price_bid > 0 ? $this->price_bid : $this->price;
            $box_footer = "<hr />
            <div class='bid-history'>
                <p class='heading'>Bid History (".$bid_count." Bids)</p>
                ".$bid_history_html."
            </div>";
            if(!$owned_listing) {
                $bid_fields = "<form method='post' action='' id='bid-form'>
                    ".$form_edit->input_html('number', 'bid_amount', $_POST['bid_amount'], ['placeholder'=>'', 'custom'=>['step'=>$bid_increment, 'min'=>$min_bid]])."
                    ".$form_edit->input_html('submit', 'submit_bid', 'Place Bid', ['class'=>['bt-block']])."
                    ".$form_edit->input_html('hidden', 'action', 'submit_bid')."
                </form>";
                /*if(CLIENT_auth) {
                    $client_in = Clients::find($_SESSION['user']['id']);
                    if(!$client_in->isVerified()) {
                        $bid_fields = "<a href='".$zulu->front_link(LINK_account_verify, ['query'=>['action'=>'resend', 'notify'=>'0']])."' class='button'>Verify your account</a>";
                    }
                }*/
            }
            if($no_reserve) {
                $reserve_text = "<p class='reserve-text'>No Reserve</p>";
            } elseif($met_reserve) {
                $reserve_text = "<p class='reserve-text'>Reserve Met</p>";
            }

        } else {
            $heading = "Asking Price";
            $price = $this->price;
            if(!$owned_listing && $is_live) {
                $box_footer = "<hr /><h4 class='form-heading'>Contact Seller</h4>".Products::enquiryForm();
            }
        }

        if(!$owned_listing) {
            $watchlist_buttons = ($on_watchlist?"
            <a class='button bt-block bt-grey".($client_has_bid?' disabled':null)."' ".($client_has_bid?'aria-disabled="true"':"href='".$zulu->front_link($product->feURL(), ['query'=>['Action'=>'watchlist_remove']])."'")." role='link'>Remove from Watchlist</a>":"
            <a href='".$zulu->front_link($product->feURL(), ['query'=>['Action'=>'watchlist_add']])."' class='button bt-block'>Add to Watchlist</a>");
        }
        if(!$is_live) {
            $watchlist_buttons = $bid_fields = null;

            if($this->hasSold()) {
                $heading = "Sold";
                if($client_has_bid) {
                    if($client_bid_leader) {
                        $heading = "You Won!";
                    } else {
                        $heading = "You Lost!";
                    }
                }
            } else {
                $heading = "Unsold";
            }
        }

        $currency_convert = "";
        if(!$product->is_poa) {
            $currencyOptions = [''=>''] + (array)json_decode(file_get_contents('https://cdn.jsdelivr.net/gh/fawazahmed0/currency-api@1/latest/currencies.json'));
            $userCurrencyCode = $_SESSION['currencyCode'];
            if($userCurrencyCode) {
                $currencyData = json_decode(file_get_contents('https://cdn.jsdelivr.net/gh/fawazahmed0/currency-api@1/latest/currencies/'.strtolower($currency_code).'/'.$userCurrencyCode.'.json'));
                $conversionPrice = $price * $currencyData->{$userCurrencyCode};
            }
            $currency_convert = "<div class='currency-selection'>
                ".($userCurrencyCode && $conversionPrice ? "
                <p><small>Approximately $".number_format($conversionPrice)." ".strtoupper($userCurrencyCode)." <a href='#' class='currency-select-trigger'>(change)</a></small></p>
                " : "
                <p><small><a href='#' class='currency-select-trigger'>View in my currency</a></small></p>
                ")."
                <div id='currency-select-form' hidden>
                    <form method='post' action=''>
                        ".$form_edit->input_html('select', 'currency_code', $userCurrencyCode, ['option'=>$currencyOptions, 'class'=>['select2']])."
                        ".$form_edit->input_html('submit', 'submit', 'Select')."
                        ".$form_edit->input_html('hidden', 'action', 'set_currency')."
                    </form>
                </div>
            </div>";
            $zulu->template->jquery_code[] = "
            $('.select2').select2({'width':'100%'});
            $('.currency-select-trigger').click(function(e) {
                e.preventDefault();
                $('#currency-select-form').slideToggle();
            });
            ";
            $zulu->include_select2();
        }

        return "<div class='box price-box text-center ".$container_class."'>
            <h4 class='heading'>".$heading."</h4>
            <h3 class='price'>".($product->is_poa ? "Contact the Seller" : "{$currency_code} $".number_format($price))."</h3>
            ".$currency_convert."
            ".$bid_fields."
            ".$watchlist_buttons."
            ".$reserve_text."
            <p class='time-summary red'>".$this->timeSummary()."</p>
            ".$box_footer."
        </div>";
    }

    public function timeSummary() {
        global $zulu;

        $html = "";
        $pre_timestamp_text = "";
        $timezone = $_SESSION['TIMEZONE'];

        if($this->isLive()) {
            $html .= $zulu->icon('clock', 'r')." ".$this->timeLeft()." left<br />";
            $timestamp = $this->time_close;
        } elseif($this->time_withdrawn > 0) {
            $pre_timestamp_text = "Withdrawn at ";
            $timestamp = $this->time_close;
        } else {
            $pre_timestamp_text = "Closed at ";
            $timestamp = $this->time_close;
        }

        $timestamp_text = $zulu->dateTimezone($timestamp, 'D jS M, h:ia');

        $html .= $pre_timestamp_text.$timestamp_text;
        return $html;
    }

    public function withdraw() {
        $this->time_withdrawn = time();
        $this->save();

        $product = $this->product;
        $product->live = 0;
        $product->save();
        $product->listingWithdrawnEmail();
    }

    public function isLive() {
        if($this->time_close <= time()) {
            return false;
        }
        $product = $this->product;
        $live_listing = $product->listing;
        if($live_listing->id != $this->id || !$product->isLive()) {
            return false;
        }

        return true;
    }

    public function hasBids() {
        $bids = $this->bids;
        return count($bids) > 0 ? true : false;
    }

    public function timeLeft() {
        $time_close = $this->time_close;
        $time_now = time();

        $time_diff = $time_close - $time_now;
        if($time_diff <= 0) {
            return "00d:00h:00m";
        }

        $date1 = new DateTime();
        $date1->setTimestamp($time_close);
        $date2 = new DateTime();
        $date2->setTimestamp($time_now);
        $interval = $date1->diff($date2);
        $days = $interval->days;
        $hours = $interval->h;
        $minutes = $interval->i;
        if($hours < 0) {
            $days -= abs($hours);
            if($days < 0) {
                $days = 0;
            }
            $hours = 24 - abs($hours);
        }

        /*$time_diff = $time_close - $time_now;
        if($time_diff <= 0) {
            return "00d:00h:00m";
        }
        $time_diff = floor($time_diff / 60);

        //-- get days
        $days = floor($time_diff / (24 * 60));
        $time_diff -= $days * (24 * 60);

        //-- get hours
        $hours = floor($time_diff / 60);
        $time_diff -= $hours * 60;

        //-- get minutes
        $minutes = $time_diff;*/

        if(strlen($days) == 1) {
            $days = "0".$days;
        }
        if(strlen($hours) == 1) {
            $hours = "0".$hours;
        }
        if(strlen($minutes) == 1) {
            $minutes = "0".$minutes;
        }
        return $days."d:".$hours."h:".$minutes."m";
    }

    public function placeBid($amount, $client_id) {
        $bid = ProductListingBid::where([['listing_id', $this->id], ['client_id', $client_id], ['bid_amount', $amount]])->first();
        if(!$bid) {
            $last_bid = $this->lastBid();

            //-- create bid
            $bid = new ProductListingBid;
            $bid->product_id = $this->product_id;
            $bid->client_id = $client_id;
            $bid->listing_id = $this->id;
            $bid->bid_amount = $amount;
            $bid->save();

            //-- update listing
            $this->price_bid = $amount;
            $this->save();

            $product = $this->product;
            if($product) {
                //-- update product
                $product->price = $amount;
                $product->save();

                //-- force add to watchlist
                $product->addWatchlist($client_id);
            }

            //-- extend listing check
            if($this->inExtensionTime()) {
                $this->extendListing();
            }

            //-- outbid notify
            if($last_bid && $last_bid->client_id != $client_id) {
                $this->outbidNotify($last_bid->client_id);
            }

            //-- notify owner of new bid
            $this->bidNotify($bid);

        }
    }

    public function hasMetReserve() {
        if($this->price == $this->price_reserve) {
            return true;
        } elseif($this->price_reserve <= $this->price_bid) {
            return true;
        }
        return false;
    }

    public function hasNoReserve() {
        if($this->price >= $this->price_reserve) {
            return true;
        }
        return false;
    }

    public function minNextBid() {
        $current_bid = $this->price_bid;
        $bid_increment = $this->bidIncrement();
        $min_next = $current_bid + $bid_increment;

        return $min_next;
    }

    public function bidIncrement() {
        $product = $this->product;
        if($product) {
            $meta = $product->metaArray($product->meta);
            if($meta['min_bid_increment'] > 0) {
                return $meta['min_bid_increment'];
            }
        }

        $current_bid = $this->price_bid;
        $bid_increment = $bid_increment_last = 0;
        foreach(self::$bid_increments as $key=>$val) {
            if($current_bid <= $key) {
				$bid_increment = self::$bid_increments[$key];
				break;
			}
			$bid_increment_last = self::$bid_increments[$key];
        }
        if($bid_increment == 0) {
			$bid_increment = $bid_increment_last;
		}

        return $bid_increment;
    }

    public function lastBid() {
        return $this->bids()->latest()->first();
    }

    public function clientHasBid($client_id) {
        $bid = $this->bids()->where('client_id', $client_id)->first();
        if($bid) {
            return true;
        }
        return false;
    }

    public function isClientBidLeader($client_id) {
        $last_bid = $this->lastBid();
        if($last_bid && $last_bid->client_id == $client_id) {
            return true;
        }
        return false;
    }

    public function extendListing() {
        $time_close = strtotime('+'.self::$listing_extension, $this->time_close);
        $this->time_close = $time_close;
        $this->save();
    }

    public function inExtensionTime() {
        if(time() >= strtotime('-'.self::$listing_extension_timeframe, $this->time_close)) {
            return true;
        }
        return false;
    }

    public function outbidNotify($client_id) {
        global $zulu;

        $client = Clients::find($client_id);
        if(!$client) {
            return false;
        }

        $last_bid = $this->lastBid();
        $last_client_bid = $this->bids()->where('client_id', $client_id)->latest()->first();
        if(!$last_client_bid) {
            return false;
        }

        $to = $client->email;
        $sub = 'You were outbid';
        $mess = "<p>Hi ".stripslashes($client->name_first).",</p>
        <p>Your bid of <b>$".number_format($last_client_bid->bid_amount)."</b> has been outbid on the listing <b>".$this->name()."</b>.</p>
        <p>View the listing on the link below to place another bid.<br />".$this->feURL(true)."</p>";
        $zulu->mail_send($to, $sub, $mess, '', false, ['client'=>true, 'user_id'=>$client->user_id, 'toggle'=>'1', 'object'=>'product_listing', 'object_id'=>$this->id]);
    }

    public function closeListing() {
        global $zulu, $class_setting;

        //-- close product
        $product = $this->product;
        $product->live = 0;
        $product->save();

        $is_auction = $product->isAuction();
        $client = $product->client;
        $login_link = FE_url.DIR_account."login.php";

        if(!$is_auction) {
            //-- if classified then notify seller
            $to = $client->email;
            $sub = "Your lising has ended";
            $mess = "<p>Hi ".stripslashes($client->name_first).".</p>
            <p>Your listing <b>".stripslashes($product->name)."</b> has ended.</p>
            <p>Log in below if your would like to relist it.</p>
            <p><a href='".$login_link."' class='button'>Log In</a></p>";
            $zulu->mail_send($to, $sub, $mess, '', false, ['client'=>true, 'user_id'=>$client->user_id, 'toggle'=>'1', 'object'=>'listing', 'object_id'=>$this->id]);
        } else {
            //-- if auction
            $reserve_met = $this->hasMetReserve();
            $last_bid = $this->lastBid();
            if($reserve_met) {
                $client_buyer = $last_bid->client;
            }
            //-- notify seller
            $to = $client->email;
            if($reserve_met) {
                $sub = "Your lising has sold";
                $mess = "<p>Hi ".stripslashes($client->name_first).".</p>
                <p>Your listing <b>".stripslashes($product->name)."</b> has sold for <b>$".number_format($this->price_bid)."</b>.</p>
                <p>
                    Here are the buyers details to arrange the rest of the sale:<br />
                    Name: ".$client_buyer->nameFull()."<br />
                    Email: ".$client_buyer->email."<br />
                    ".($client_buyer->phone?"Phone: ".$client_buyer->phone."<br />":null)."
                </p>
                <p>Log in below for more details.</p>
                <p><a href='".$login_link."' class='button'>Log In</a></p>";
            } else {
                $sub = "Your lising has ended";
                $mess = "<p>Hi ".stripslashes($client->name_first).".</p>
                <p>Your listing <b>".stripslashes($product->name)."</b> has ended and did not sell.</p>
                <p>Log in below if your would like to relist it.</p>
                <p><a href='".$login_link."' class='button'>Log In</a></p>";
            }
            $zulu->mail_send($to, $sub, $mess, '', false, ['client'=>true, 'user_id'=>$client->user_id, 'toggle'=>'1', 'object'=>'listing', 'object_id'=>$this->id]);

            //-- if has buyer
            if($reserve_met) {
                //-- create sale record
                $sale_record = new SaleRecord;
                $sale_record->product_id = $this->product_id;
                $sale_record->listing_id = $this->id;
                $sale_record->bid_id = $last_bid->id;
                $sale_record->client_sell_id = $client->id;
                $sale_record->client_buy_id = $client_buyer->id;
                $sale_record->sale_amount = $this->price_bid;
                $sale_record->sale_date = time();
                $sale_record->horse_name = $product->name;
                $sale_record->spec_sire = $product->spec_sire;
                $sale_record->spec_dam = $product->spec_dam;
                $sale_record->source = $class_setting->data['ws_site_name'];
                $sale_record->sale_currency = $product->currencyCode();
                $sale_record->save();
                $this->sale_record_id = $sale_record->id;
                $this->save();
                $product->sale_record_id = $sale_record->id;
                $product->save();

                //-- notify buyer
                $to = $client_buyer->email;
                $sub = "You won ".stripslashes($product->name);
                $mess = "<p>Hi ".stripslashes($client_buyer->name_first).".</p>
                <p>You won the listing <b>".stripslashes($product->name)."</b> for <b>$".number_format($this->price_bid)."</b>.</p>
                <p>
                    Here are the sellers details to arrange the rest of the sale:<br />
                    Name: ".$client->nameFull()."<br />
                    Email: ".$client->email."<br />
                    ".($client->phone?"Phone: ".$client->phone."<br />":null)."
                </p>
                <p>Log in below for more details.</p>
                <p><a href='".$login_link."' class='button'>Log In</a></p>";
                $zulu->mail_send($to, $sub, $mess, '', false, ['client'=>true, 'user_id'=>$client_buyer->user_id, 'toggle'=>'1', 'object'=>'listing', 'object_id'=>$this->id]);
            }

        }

        //-- add lost records
        $watchlists = $product->watchlists();
        foreach($watchlists as $watchlist) {
            $product->clientsLost()->attach($watchlist->client_id);

            //-- clear watchlist
            $watchlist->delete();
        }

    }

    public function isAuction() {
        $product = $this->product;
		if(!$product) {
			return false;
		}
		return $product->isAuction();
	}

    public function hasSold() {
        $product = $this->product;
		if(!$product) {
			return false;
		}
		return $product->hasSold();
	}

    public function canEdit() {
        //-- is it active
        if(!$this->isLive()) {
            return false;
        }
        //-- is it a classified
        if(!$this->isAuction()) {
            return true;
        }
        //-- does it have bids
        if($this->hasBids()) {
            return false;
        }
        return true;
    }

    public function canRelist() {
        //-- is it active
        if($this->isLive()) {
            return false;
        }
        //-- has it been sold
        if($this->hasSold()) {
            return false;
        }
        return true;
    }

    public function statViews() {
		return $this->stat_view;
	}

    public function addStatView() {
        $this->stat_view++;
        $this->save();
	}

    public function bidNotify($bid) {
        global $zulu;

        $client = $this->client;
        if(!$client) {
            return false;
        }
        $product = $this->product;

        $to = $client->email;
        $sub = 'New bid for '.stripslashes($product->name);
        $mess = "<p>Hi ".stripslashes($client->name_first).",</p>
        <p>There's been a new bid on <b>".stripslashes($product->name)."</b> for <b>$".number_format($bid->bid_amount)."</b>.</p>
        <p><a href='".$product->feURL(true)."'>View your listing here</a></p>";

        $zulu->mail_send($to, $sub, $mess, '', false, ['client'=>true, 'user_id'=>$client->user_id, 'toggle'=>'1', 'object'=>'product_listing', 'object_id'=>$this->id]);

    }

}
