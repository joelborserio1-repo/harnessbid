<?php

use Illuminate\Database\Eloquent\SoftDeletes;

class ProductBrand extends Model{
    use SoftDeletes;
	
	protected $table = 'product_brand';
    
    public static $file_folder = "product-brand/";
	
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
    	
	public function products() {
        return $this->hasMany('Products','brand_id', 'id');
    }
    
    public function newQuery() {
        global $class_user;
        return parent::newQuery()
            ->where('user_id', '=', $class_user->authorised->id);
    }
    
    public static function file_rel() {
        return MAIN_rel."file/".self::$file_folder;
    }
    
    public static function file_path() {
        return MAIN_path."file/".self::$file_folder;
    }
    
    public static function optionArray() {
        $options = [];
        $brands = self::orderBy('title','ASC')->get();
        foreach($brands as $brand) {
            $options[$brand->id] = $brand->title;
        }
        return $options;
    }
    
    public function feURL($full=false) {
        global $zulu;
        return ($full?FE_url:FE_rel)."brands/".$this->slug."/";
    }
    
    public function image() {
		
		$main_image = $this->id."/".$this->image;
		if($this->image != null && file_exists(self::file_path().$main_image)) {
			$return = self::file_rel().$main_image; 
		} else {
            $return = null;
        }
		
		return $return;
	}
    
    public function getColour() {
        if($this->colour_manual != null) {
            return $this->colour_manual;
        } else {
            return $this->colour_default;
        }
    }
    
    public function htmlBlock() {
        global $zulu;
        
        $image = $this->image();
        $colour = $this->getColour();
        
        $html = "
        <div class=\"box brand-box\"".($colour!=null?" style='background-color:".$colour."'":null).">
            <a href='".$this->feURL()."'>
                ".($image!=null?"
                <div class=\"image\">
                    <img src=\"".$zulu->thumb(FE_crm.$image,"w=400&h=150&far=1&bg=FFFFFF")."\" border=\"0\" alt=\"logo for ".$this->title." brand\" class='responsive' />
                </div>
                ":"
                <div class=\"title\">".$this->title."</div>
                ")."
            </a>
        </div>";
        
        return $html;
    }
    
    public function breadcrumbLinks($home=false, $last_link=false, $main_link=true, $category=null, $view_mode=null) {
        global $class_setting;
        
        $links = [];
        
        if(isset($class_setting->data['ws_shop_category']) && $class_setting->data['ws_shop_category'] > 0) {
            $root_category_id = $class_setting->data['ws_shop_category'];
        } else {
            $root_category_id = 0;
        }
        if($category != null) {
            $last_link = true;
        }
        
        // home link
        if($home && $root_category_id <= 0) {
            $links[] = Products::homeBreadcrumbLink();
        }
        // main brands link
        if($main_link) {
            $links[] = self::homeBreadcrumbLink();
        }
        
        // view mode link
        $cat_last_link = false;
        if($view_mode != null) {
            $view_mode_crumb = Products::viewModeBreadcrumbLink($view_mode);
            if($view_mode_crumb != null) {
                $cat_last_link = $last_link = true;
            }
        }
        
        // this brand link
        $links[] = $this->breadcrumbLink($last_link);
                
        // category links
        if($category != null) {
            $category_crumbs = $category->breadcrumbLinks(false, $cat_last_link, null, false, null, true);
            $links[] = $category_crumbs;
        }
        
        if($view_mode_crumb != null) {
            $links[] = $view_mode_crumb;
        }
        
        return "<div class='breadcrumbs category-breadcrumbs'>".implode(Products::$breadcrumb_divider, $links)."</div>";
    }
    
    public function breadcrumbLink($link=true) {
        global $zulu;

        $label = stripslashes($this->title);
        if($link) {
            $label = "<a href='".$zulu->front_link($this->feURL())."'>".$label."</a>";
        }
        $crumb = "<div class='breadcrumb'>".$label."</div>";
        
        return $crumb;
    }
    
    public static function homeBreadcrumbLink() {
        global $zulu;
        
        $crumb = "<div class='breadcrumb'><a href='".$zulu->front_link(FE_rel."brands/")."'>Brands</a></div>";
        
        return $crumb;
    }
    
    public static function charList($char) {
        global $zulu;
        
        $brands = self::selectRaw("title")->whereRaw("SUBSTRING(slug, 1, 1) = '".$char."'")->orderBy('title','ASC')->get();
        $list = [];
        foreach($brands as $brand) {
            $list[] = "<li><a href='".$zulu->front_link($brand->feURL())."'>".$brand->title."</a></li>";
        }
        
        return "<ul>".implode('',$list)."</ul>";
    }
	
}