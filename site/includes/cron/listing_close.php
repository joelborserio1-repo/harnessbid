<?php
//(C)2007-2015 RAZOR WEB DESIGN LIMITED
//ZULU SHOPPING SYSTEM v2.1.1
//BUILT ON PHP & MySQL

//SESSION
session_start();

//INCLUDES
include("../loader.php");
$class_user->authorised->id = 5;
//error_reporting(E_ALL);ini_set('display_errors', 1);

//$listings = ProductListing::where([['time_close', '<=', time()]])->whereRaw("exists(select * from product where id=product_listing.product_id and live=1)")->get();
$products = Products::where('live', 1)->whereRaw("exists(select * from product_listing where product.listing_id=product_listing.id and time_close<=".time().")")->get();
//print_r($listings);exit;

foreach($products as $product) {
	$listing = $product->listing;
	if($listing) {
		$listing->closeListing();
	}
}

echo 'eof';
exit;
