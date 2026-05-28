<?php
// JSON feed for HarnessLink widgets and other approved integrations.

define('PAGE_file','marketplace_feed');
include dirname(__FILE__)."/../includes/loader.php";

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');

$limit = (isset($_GET['limit']) && (int)$_GET['limit'] > 0 ? min((int)$_GET['limit'], 24) : 8);

$query = Products::join('product_meta as pm_listing_kind', 'product.id', '=', 'pm_listing_kind.identifier')
    ->join('product_listing as pl', 'product.listing_id', '=', 'pl.id')
    ->where('pm_listing_kind.field', 'listing_kind')
    ->where('pm_listing_kind.value', 'marketplace')
    ->where('product.type', 'product')
    ->where('product.live', 1)
    ->where('product.hide', 0)
    ->where('product.hide_public', 0)
    ->where('product.sys', 0)
    ->select('product.*', 'pl.time_close', 'pl.price_bid', 'pl.add_feature')
    ->groupBy('product.id')
    ->orderBy('pl.add_feature', 'DESC')
    ->orderBy('product.updated_at', 'DESC')
    ->take($limit)
    ->get();

$items = [];
foreach($query as $product) {
    $images = $product->images();
    $image = (isset($images['main']) && $images['main'] ? FE_url.$images['main'] : null);
    $meta = $product->marketplaceMeta();
    $price = ($product->listing_type == 'auction' && $product->price_bid > 0 ? $product->price_bid : $product->price);

    $items[] = [
        'id'            =>  (int)$product->id,
        'title'         =>  stripslashes($product->name),
        'url'           =>  $product->feURL(true),
        'image'         =>  $image,
        'listing_type'  =>  $product->listing_type,
        'category'      =>  $product->marketplaceCategory(),
        'condition'     =>  (isset($meta['marketplace_condition']) ? $meta['marketplace_condition'] : null),
        'currency'      =>  $product->currencyCode(),
        'price'         =>  (float)$price,
        'time_close'    =>  ($product->time_close ? date('c', $product->time_close) : null),
        'updated_at'    =>  ($product->updated_at ? date('c', strtotime($product->updated_at)) : null),
    ];
}

echo json_encode([
    'source'    =>  'HarnessBid Marketplace',
    'url'       =>  FE_url.'marketplace/',
    'count'     =>  count($items),
    'items'     =>  $items,
], JSON_PRETTY_PRINT);
exit;
