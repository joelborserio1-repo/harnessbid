<?php

use Illuminate\Database\Eloquent\SoftDeletes;

class ProductReview extends Model {
	use SoftDeletes;
	
	protected $table = 'product_review';
    public static $stars = 5;
    public static $star_half_min = 0.4;
    public static $star_half_max = 0.7;
    public static $featured_count = 3;
	
	/**
	* The attributes that are mass assignable.
	*
	* @var array
	*/

	protected $fillable = [
        'user_id',
		'client_id',
		'product_id',
	];

	/**
	* The attributes that should be hidden for arrays.
	*
	* @var array
	*/

	protected $hidden = [
		
	];
	
	protected static $api_queryable = [
		'stat_add',
		'stat_update',
	];
	
	protected $dates = ['created_at', 'updated_at', 'deleted_at'];
	
    public function product() {
        return $this->belongsTo('Products','product_id', 'id');
    }
    
	public function client() {
        return $this->belongsTo('Clients','client_id', 'id');
    }
    
    public function newQuery() {
        global $class_user;
        return parent::newQuery()
            ->where('user_id', '=', $class_user->authorised->id);
    }
    
    public function feHTML($shorten=false) {
        global $zulu;
        
        $client = $this->client;
        
        $rating_stars = [];
        for($i=1; $i<=ProductReview::$stars; $i++) {
            if($this->rating >= $i) {
                $class = "full";
                $icon = "fas fa-star";
            } else {
                $class = "empty";
                $icon = "far fa-star";
            }
            $rating_stars[] = "<span class='rating-star ".$class."'><i class='".$icon."'></i></span>";
        }
        if($shorten) {
            $this->content = $zulu->shorten($this->content, 400);
        }
        
        return "
        <div class='review-row' id='review-".$this->id."'>
            <h4 class='review-title'>".$this->title."</h4>
            <div class='review-stars'>".implode('',$rating_stars)."</div>
            <p class='review-byline'>Reviewed ".($client->name_first!=null?" by ".$client->name_first:null)." on ".$zulu->dateDecode($this->stat_add,'jS F, Y').($this->verified?" <span class='review-verified'><i class='fas fa-check'></i> Verified Purchaser</span>":null)."</p>
            <p class='review-content'>".$this->content."</p>
        </div>
        ";
    }
    
    public function viewModal() {
        global $zulu;
        
        $buttons = [];
        if($this->status == 'pending') {
            $buttons[] = ['link'=>$zulu->link_page(PAGE_file,['self'=>true,'query'=>['Do'=>'approve','id'=>$this->id]]),'label'=>'Approve','icon'=>'check','class'=>'success'];
            $buttons[] = ['link'=>$zulu->link_page(PAGE_file,['self'=>true,'query'=>['Do'=>'disapprove','id'=>$this->id]]),'label'=>'Disapprove','icon'=>'times','class'=>'danger','class_append'=>['confirm']];
        } elseif($this->status == 'hidden') {
            $buttons[] = ['link'=>$zulu->link_page(PAGE_file,['self'=>true,'query'=>['Do'=>'show','id'=>$this->id]]),'label'=>'Show','icon'=>'plus-octagon','class'=>'danger'];
        }
        if($this->status == 'live') {
            if($this->feature) {
                $buttons[] = ['link'=>$zulu->link_page(PAGE_file,['self'=>true,'query'=>['Do'=>'defeature','id'=>$this->id]]),'label'=>'Defeature','icon'=>'star','class'=>'primary'];
            } else {
                $buttons[] = ['link'=>$zulu->link_page(PAGE_file,['self'=>true,'query'=>['Do'=>'feature','id'=>$this->id]]),'label'=>'Feature','icon'=>'star','class'=>'primary'];
            }
        }
        if($this->status == 'hidden' || $this->status == 'live') {
            if($this->verified) {
                $buttons[] = ['link'=>$zulu->link_page(PAGE_file,['self'=>true,'query'=>['Do'=>'deverify','id'=>$this->id]]),'label'=>'Deverify','icon'=>'user-times','class'=>'success'];
            } else {
                $buttons[] = ['link'=>$zulu->link_page(PAGE_file,['self'=>true,'query'=>['Do'=>'verify','id'=>$this->id]]),'label'=>'Verified','icon'=>'user-check','class'=>'success'];
            }
        }
        if($this->status == 'live') {
            $buttons[] = ['link'=>$zulu->link_page(PAGE_file,['self'=>true,'query'=>['Do'=>'hide','id'=>$this->id]]),'label'=>'Hide','icon'=>'minus-octagon','class'=>'danger'];
        }
        
        $html = "
        <div class='row'>
            <div class='col-md-12'>
                <p>".$zulu->button_render($buttons)."</p>
                <p>
                    <b>Rating:</b> ".$this->rating." / ".self::$stars."<br>
                    <b>Title:</b> ".$this->title."<br>
                    <b>Review:</b><br>".str_replace(chr(10),'<br>',$this->content)."
                </p>
            </div>
        </div>
        ";
        
        $modal = zulu::modal($html, "View Review", "review-".$this->id, ['size'=>'md']);
        return $modal;
    }
    
    public function approveNotify() {
        global $zulu;
        
        $client = $this->client;
        $product = $this->product;
        if($client != null && $product != null && $client->email != null) {
            $message = "
            <p>Hi ".$client->name_first.",</p>
            <p>Your review on the product, <b>".$product->name."</b>, has now been published for everyone to see. Thank you for the feedback.</p>
            <p>
                Rating: ".$this->rating." / ".self::$stars."<br>
                Title: ".$this->title."<br>
                Review: ".str_replace(chr(10), '<br>', $this->content)."
            </p>
            ";
            $to = $client->email;
            $zulu->mail_send($to, "", $message, '', false, ['client'=>true]);
        }
    }
    
    public function reviewSchema() {
        global $zulu;
        
        if($this->status == 'live' && $this->title != null) {
            $client = $this->client;
            if($client != null && $client->name_first != null) {
                $author = $client->name_first;
            } else {
                $author = 'Anonymous';
            }
            $schema_array = [
                '@type'         =>  'Review',
                'author'        =>  $author,
                'datePublished' =>  $zulu->date($this->stat_add, 'Y-m-d'),
                'description'   =>  $this->content,
                'name'          =>  $this->title,
                'reviewRating'  =>  [
                    '@type'         =>  'Rating',
                    'bestRating'    =>  self::$stars,
                    'ratingValue'   =>  $this->rating,
                    'worstRating'   =>  1,
                ]
            ];
        }
        
        return $schema_array;
    }
	
}