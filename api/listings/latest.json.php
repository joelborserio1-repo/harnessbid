<?php
define('PAGE_file', 'api_latest_listings');
include dirname(__FILE__)."/../../includes/loader.php";

header('Content-Type: application/json');
header('Cache-Control: public, max-age=300');
header('Access-Control-Allow-Origin: https://harnesslink.com');

$base_url = 'https://harnessbid.com';
$image_base = $base_url.'/site/file/store/';
$now = time();

$sql = "
  SELECT
    p.id,
    p.name AS title,
    p.listing_mode,
    p.listing_type,
    p.price,
    p.image,
    p.condition,
    p.pickup_location,
    p.listing_tier,
    p.featured_until,
    p.created_at,
    p.stat_add,
    mc.name AS category,
    mc.icon AS category_icon,
    pl.time_close,
    pl.price_bid,
    pl.add_feature,
    (SELECT COUNT(*) FROM product_listing_bid bid WHERE bid.product_id = p.id) AS bid_count
  FROM product p
  LEFT JOIN product_listing pl ON pl.id = p.listing_id
  LEFT JOIN marketplace_category mc ON mc.id = p.marketplace_cat_id
  WHERE p.live = 1
    AND p.hide = 0
    AND p.hide_public = 0
    AND p.sys = 0
    AND p.type = 'product'
    AND p.sold_at IS NULL
    AND (pl.time_close IS NULL OR pl.time_close = 0 OR pl.time_close > ".$now.")
  ORDER BY
    (p.featured_until IS NOT NULL AND p.featured_until > NOW()) DESC,
    pl.add_feature DESC,
    COALESCE(p.created_at, FROM_UNIXTIME(NULLIF(p.stat_add, 0))) DESC
  LIMIT 6
";

$rows = [];
$result = $db->mysqli->query($sql);
if($result) {
    while($row = $result->fetch_assoc()) {
        $rows[] = $row;
    }
}

$listings = array_map(function($r) use ($base_url, $image_base) {
    $mode = $r['listing_mode'];
    if($mode == 'horse_auction' && $r['listing_type'] == 'classified') {
        $mode = 'horse_buynow';
    }
    $price = ($r['listing_type'] == 'auction' && $r['price_bid'] > 0 ? $r['price_bid'] : $r['price']);
    $close_date = ((int)$r['time_close'] > 0 ? date('c', (int)$r['time_close']) : null);
    $image_url = null;
    if(!empty($r['image'])) {
        $image_url = (preg_match('/^https?:\/\//', $r['image']) ? $r['image'] : $image_base.ltrim($r['image'], '/'));
    }

    return [
        'id'             => (int)$r['id'],
        'title'          => stripslashes($r['title']),
        'type'           => $mode,
        'category'       => $r['category'],
        'category_icon'  => $r['category_icon'],
        'price'          => (float)$price,
        'bid_count'      => (int)$r['bid_count'],
        'featured'       => (!empty($r['featured_until']) || (int)$r['add_feature'] > 0 || $r['listing_tier'] == 'featured'),
        'closes_at'      => $close_date,
        'condition'      => $r['condition'],
        'location'       => $r['pickup_location'],
        'image_url'      => $image_url,
        'url'            => $base_url.'/browse/product.php?id='.(int)$r['id'],
    ];
}, $rows);

echo json_encode(['listings' => $listings, 'generated_at' => date('c')], JSON_PRETTY_PRINT);
