<?php
//(C)2007-2015 RAZOR WEB DESIGN LIMITED
//ZULU SHOPPING SYSTEM v2.1.1
//BUILT ON PHP & MySQL

//INCLUDES
include("loader.php");

$xml = new SimpleXMLElement('<rss/>');
$xml->addAttribute('version', '2.0');
$channel = $xml->addChild('channel');

//define('RSS_post_feeds', ['page','gallery','news']);

$post_data = [];
$product_data = [];
$channel_title = "";
$channel_link = "";
$channel_description = "";
$type = "";
//Dependent on type var show a different channel
switch ($_GET['type']) {
    case 'page':
		$post_data = $class_post->post_data(['type'=>'page','status'=>'published']);
		$channel_title = "Pages";
		$channel_link = MAIN_url;
		$channel_description = "Pages from";
		$type = 'post';
        break;
    case 'gallery':
		$post_data = $class_post->post_data(['type'=>'gallery']);
		//Get the page for this post type
		$post_index_meta = $zulu->table_data('post_meta as pm',0,['where'=>["field='post_index'", "value='gallery'"],'first'=>true]);
		if($post_index_meta['identifier'] >0){
			$page_data = $class_post->post_data(['id'=>$post_index_meta['identifier']]);
			if($page_data['id']>0){
				$channel_link = MAIN_url.$page_data['slug'];
			}
		}else{
			$channel_link = MAIN_url;
		}
		$channel_title = "Galleries";
		$channel_description = "Galleries from";
		$type = 'post';
        break;
    case 'news':
		$post_data = $class_post->post_data(['type'=>'news']);
		$post_index_meta = $zulu->table_data('post_meta as pm',0,['where'=>["field='post_index'", "value='news'"],'first'=>true]);
		if($post_index_meta['identifier'] >0){
			$page_data = $class_post->post_data(['id'=>$post_index_meta['identifier']]);
			if($page_data['id']>0){
				$channel_link = MAIN_url.$page_data['slug'];
			}
		}else{
			$channel_link = MAIN_url;
		}
		$channel_title = "News";
		$channel_description = "News articles from";
		$type = 'post';
        break;
	case 'browse':
		$product_data = $class_product->product_data(['type'=>'product']);
		$channel_title = "Products";
		$channel_link = MAIN_url;
		$channel_description = "Products from";
		$type = 'product';
        break;
	case 'category':
		$product_data = $class_product->product_data(['type'=>'category']);
		$channel_title = "Categories";
		$channel_link = MAIN_url;
		$channel_description = "Product Categories from";
		$type = 'product';
        break;
	default:
		//Show pages feed by default
		$post_data = $class_post->post_data(['type'=>'page','status'=>'published']);
		$channel_title = "Pages";
		$channel_link = MAIN_url;
		$channel_description = "Pages from";
		$type = 'post';
        break;
}
$channel->addChild('title', SITE_title." ".$channel_title);
$channel->addChild('link', $channel_link);
$channel->addChild('description', $channel_description." ".SITE_title);

if($type == 'product'){
	foreach($product_data as $product){
		//get parent if any
		$product_title = "";
		if($product['type_variant'] == 2 && $product['parent_id']>0){
			$product_parent_data = $class_product->product_data(['id'=>$product['parent_id']]);
			$product_title = $product_parent_data['name'];
		}else{
			$product_title = $product['name'];
		}
		$description_text = strip_tags(substr($product['description'], 0, 250));
		$url_string = $class_product->product_url($product['id']);
		$item = $channel->addChild('item');
		$item->addChild('title', $product_title);
		$item->addChild('link', MAIN_url.$url_string);
		$item->addChild('description', $description_text);
	}
}else if($type == 'post'){
	foreach($post_data as $post){
		$url_string = $class_post->post_url($post['id']);
		$description_text = strip_tags(substr($post['content'], 0, 250));
		$item = $channel->addChild('item');
		$item->addChild('title', $post['title']);
		$item->addChild('link', $url_string);
		$item->addChild('description', $description_text);
	}
}

header('Content-type: text/xml');
print($xml->asXML());

//echo "EOF";
//exit;

?>
