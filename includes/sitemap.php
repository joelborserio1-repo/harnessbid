<?php
//(C)2007-2015 RAZOR WEB DESIGN LIMITED
//ZULU SHOPPING SYSTEM v2.1.1
//BUILT ON PHP & MySQL
//error_reporting(E_ALL);ini_set('display_errors', 1);

session_start();

//INCLUDES
include("loader.php");

$xml = new SimpleXMLElement('<urlset/>');

$xml->addAttribute('xmlns', 'http://www.sitemaps.org/schemas/sitemap/0.9');
$xml->addAttribute('xmlns:xsi', 'http://www.w3.org/2001/XMLSchema-instance');
$xml->addAttribute('xsi:schemaLocation', 'http://www.sitemaps.org/schemas/sitemap/0.9');

//Posts
$post_pages = $class_post->post_data(['type'=>'page','status'=>'published']);
$post_galleries = $class_post->post_data(['type'=>'gallery','status'=>'published']);
$post_news = $class_post->post_data(['type'=>'news','status'=>'published']);

foreach($post_pages as $page){
	if($page['_meta']['sitemap_show']){
		$loc = $class_post->post_url($page['id']);
		$url = $xml->addChild('url');
		$url->addChild('loc', $loc);
		$url->addChild('changefreq', "weekly");
		$url->addChild('priority', ($page['slug']!='index'?'0.8':'1'));
	}
}
foreach($post_galleries as $gallery){
	if($gallery['_meta']['sitemap_show']){
		$loc = $class_post->post_url($gallery['id']);
		$url = $xml->addChild('url');
		$url->addChild('loc', $loc);
		$url->addChild('changefreq', "weekly");
		$url->addChild('priority', "0.5");
	}
}
foreach($post_news as $news){
	if($news['_meta']['sitemap_show']){
		$loc = $class_post->post_url($news['id']);
		$url = $xml->addChild('url');
		$url->addChild('loc', $loc);
		$url->addChild('changefreq', "weekly");
		$url->addChild('priority', "0.5");
	}
}

if($class_website->config->program == 'ZULUSHP' || true) {
	$products = $class_product->product_data(['type'=>'product','type_variant'=>[0,1],'sys'=>'0','hide'=>'0','live'=>'1']);
	$categories = $class_product->product_data(['type'=>'category']);
	foreach($products as $product){
		$loc = $class_product->product_url($product['id'],NULL,true,true);
		$url = $xml->addChild('url');
		$url->addChild('loc', $loc);
		$url->addChild('changefreq', "weekly");
		$url->addChild('priority', "0.6");
	}
	foreach($categories as $category){
		$loc = $class_product->product_url($category['id'],NULL,true,true);
		$url = $xml->addChild('url');
		$url->addChild('loc', $loc);
		$url->addChild('changefreq', "weekly");
		$url->addChild('priority', "0.6");
	}
    if($BRAND_enabled) {
        $brands = ProductBrand::where([['hide','0']])->get();
        foreach($brands as $brand) {
            $loc = $brand->feURL(true);
            $url = $xml->addChild('url');
            $url->addChild('loc', $loc);
            $url->addChild('changefreq', "weekly");
            $url->addChild('priority', "0.6");
        }
    }
}


header('Content-type: text/xml');
print($xml->asXML());

//echo "EOF";
exit;

?>
