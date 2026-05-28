<?php
//(C)2007-2015 RAZOR WEB DESIGN LIMITED
//ZULU SHOPPING SYSTEM v2.1.1
//BUILT ON PHP & MySQL
//error_reporting(E_ALL);ini_set('display_errors', 1);

//INCLUDES
include("loader.php");

//-- User token

//-- Output Header
$CSV[] = explode(',','id,brand,gtin,title,description,price,condition,availability,image_link,link');

//-- Product Load
$product_data = $class_product->product_data(['status'=>1,'type_variant'=>[0,1],'sys'=>'0','hide'=>'0']);
foreach($product_data as $pd) {
	$pm = $zulu->meta_array($class_product->product_meta($pd['id']));
	$pp = $class_product->price($pd['id']);
	$pi = $class_product->image_data($pd['id']);
    
    if($pd['brand_id'] > 0) {
        if(!isset($brand_cache[$pd['brand_id']])) {
            $brand = ProductBrand::find($pd['brand_id']);
            if($brand != null) {
                $brand_cache[$pd['brand_id']] = $brand;
            }
        }
        $brand = $brand_cache[$pd['brand_id']];
        if($brand != null) {
            $brand_title = $brand->title;
        } else {
            $brand_title = null;
        }
    } else {
        $brand_title = null;
    }
    
    $descr = trim(strip_tags(str_replace(array("\n", "\t", "\r","\""),[" "," "," ","'"],str_replace('</',' </',stripslashes($pd['description'])))));
	$CSV[] = [
		$pd['sku'],
		'"'.trim($brand_title).'"',
		$pm['barcode'],
		'"'.trim(strip_tags($pd['name'])).'"',
		'"'.$zulu->shorten($descr,100).'"',
		$pp['price'],
		'new',
		($pm['stock']>0?'in stock':'out of stock'),
		FE_host.'/'.$pi['main'],
		FE_host.$zulu->path_clean($class_product->product_url($pd['id'])),
	];
}
foreach($CSV as $CSV_row) {
	echo implode(',',$CSV_row);
	echo PHP_EOL;
}

exit;