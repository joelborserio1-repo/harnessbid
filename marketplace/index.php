<?php
define('PAGE_file','marketplace');
include dirname(__FILE__)."/../includes/loader.php";

$META_title_item = ['Marketplace'];
$META_description = 'Buy and sell harness racing equipment, gear, sulkies, carts and stable essentials through HarnessBid.';

$conditions = [
    'new' => 'New',
    'used_excellent' => 'Used - excellent',
    'used_good' => 'Used - good',
    'used_fair' => 'Used - fair',
    'parts' => 'Parts',
];

function hb_sql_escape($value) {
    global $db;
    return $db->mysqli->real_escape_string(trim($value));
}

function hb_marketplace_query_url($params=[]) {
    $query = $_GET;
    foreach($params as $key=>$value) {
        if($value === null || $value === '') {
            unset($query[$key]);
        } else {
            $query[$key] = $value;
        }
    }
    return FE_rel.'marketplace/'.(count($query) ? '?'.http_build_query($query) : '');
}

function hb_condition_badge_class($condition) {
    return [
        'new' => 'badge-new',
        'used_excellent' => 'badge-excellent',
        'used_good' => 'badge-good',
        'used_fair' => 'badge-fair',
        'parts' => 'badge-parts',
    ][$condition] ?? 'badge-good';
}

function hb_format_condition($condition) {
    return ucwords(str_replace(['used_', '_'], ['used ', ' '], (string)$condition));
}

function hb_time_remaining($timestamp) {
    $timestamp = (int)$timestamp;
    if($timestamp <= time()) return null;
    $seconds = $timestamp - time();
    $days = floor($seconds / 86400);
    if($days > 0) return $days.'d left';
    $hours = floor($seconds / 3600);
    if($hours > 0) return $hours.'h left';
    return max(1, floor($seconds / 60)).'m left';
}

$categories = [];
$category_result = $db->mysqli->query("SELECT * FROM marketplace_category WHERE active = 1 ORDER BY sort_order ASC, name ASC");
if($category_result) {
    while($row = $category_result->fetch_assoc()) {
        $categories[] = $row;
    }
}

$cat = isset($_GET['cat']) ? hb_sql_escape($_GET['cat']) : (isset($_GET['category']) ? hb_sql_escape($_GET['category']) : '');
$condition = isset($_GET['condition']) ? hb_sql_escape($_GET['condition']) : '';
$min_price = isset($_GET['min_price']) && is_numeric($_GET['min_price']) ? (float)$_GET['min_price'] : null;
$max_price = isset($_GET['max_price']) && is_numeric($_GET['max_price']) ? (float)$_GET['max_price'] : null;
$location = isset($_GET['location']) ? hb_sql_escape($_GET['location']) : '';
$sort = isset($_GET['sort']) ? $_GET['sort'] : 'newest';
$page = isset($_GET['pg']) && $_GET['pg'] > 0 ? (int)$_GET['pg'] : 1;
$limit = 24;
$offset = ($page - 1) * $limit;

$joins = [
    "LEFT JOIN product_listing pl ON pl.id = p.listing_id",
    "LEFT JOIN marketplace_category mc ON mc.id = p.marketplace_cat_id",
];
$where = [
    "p.type = 'product'",
    "p.live = 1",
    "p.hide = 0",
    "p.hide_public = 0",
    "p.sys = 0",
    "p.sold_at IS NULL",
    "(p.listing_mode = 'marketplace' OR EXISTS(SELECT 1 FROM product_meta pm_kind WHERE pm_kind.identifier = p.id AND pm_kind.field = 'listing_kind' AND pm_kind.value = 'marketplace'))",
];

if($cat != '') {
    $where[] = "(mc.slug = '".$cat."' OR EXISTS(SELECT 1 FROM product_meta pm_cat WHERE pm_cat.identifier = p.id AND pm_cat.field = 'marketplace_category' AND pm_cat.value = '".$cat."'))";
}
if($condition != '' && isset($conditions[$condition])) {
    $where[] = "p.condition = '".$condition."'";
}
if($min_price !== null) {
    $where[] = "p.price >= ".$min_price;
}
if($max_price !== null) {
    $where[] = "p.price <= ".$max_price;
}
if($location != '') {
    $where[] = "(p.pickup_location LIKE '%".$location."%' OR p.description LIKE '%".$location."%')";
}

$order = "COALESCE(p.created_at, FROM_UNIXTIME(NULLIF(p.stat_add, 0))) DESC";
if($sort == 'price_asc') {
    $order = "p.price ASC";
} elseif($sort == 'price_desc') {
    $order = "p.price DESC";
} elseif($sort == 'closing') {
    $order = "pl.time_close ASC";
}

$from = "FROM product p ".implode(' ', $joins)." WHERE ".implode(' AND ', $where);
$total = 0;
$count_result = $db->mysqli->query("SELECT COUNT(DISTINCT p.id) AS total ".$from);
if($count_result) {
    $total_row = $count_result->fetch_assoc();
    $total = (int)$total_row['total'];
}

$listings = [];
$sql = "SELECT p.*, mc.name AS category_name, mc.slug AS category_slug, mc.icon AS category_icon, pl.time_close, pl.price_bid, pl.add_feature ".$from." GROUP BY p.id ORDER BY ".$order." LIMIT ".$offset.",".$limit;
$listing_result = $db->mysqli->query($sql);
if($listing_result) {
    while($row = $listing_result->fetch_assoc()) {
        $listings[] = $row;
    }
}

include FE_abs."template/head.php";
?>

<div class="pb-section pb-section-row-1 section-variant-1 section-pad-1 page-title marketplace-title">
  <div class="frame frame-master">
    <h1>Marketplace</h1>
    <p>Equipment, tack, floats, services and racing gear from the HarnessBid community.</p>
  </div>
</div>

<div class="wrapper-product wrapper-marketplace hb-marketplace">
  <div class="frame">
    <div class="hb-cat-grid">
      <?php foreach($categories as $category) { ?>
        <a class="hb-cat-card<?php echo ($cat == $category['slug'] ? ' active' : ''); ?>" href="<?php echo hb_marketplace_query_url(['cat'=>$category['slug'], 'pg'=>null]); ?>">
          <i class="ti <?php echo htmlspecialchars($category['icon'], ENT_QUOTES); ?>" aria-hidden="true"></i>
          <span class="hb-cat-card__label"><?php echo htmlspecialchars($category['name'], ENT_QUOTES); ?></span>
        </a>
      <?php } ?>
    </div>

    <div class="row">
      <aside class="col-md-3">
        <form method="get" action="<?php echo FE_rel; ?>marketplace/" class="sidebox hb-marketplace-filter">
          <h3>Filter marketplace</h3>
          <div class="links">
            <a href="<?php echo FE_rel; ?>marketplace/">All categories</a>
            <?php foreach($categories as $category) { ?>
              <a class="<?php echo ($cat == $category['slug'] ? 'active' : ''); ?>" href="<?php echo hb_marketplace_query_url(['cat'=>$category['slug'], 'pg'=>null]); ?>"><?php echo htmlspecialchars($category['name'], ENT_QUOTES); ?></a>
            <?php } ?>
          </div>

          <input type="hidden" name="cat" value="<?php echo htmlspecialchars($cat, ENT_QUOTES); ?>">
          <div class="hb-filter-condition">
            <label>Condition</label>
            <?php foreach($conditions as $value=>$label) { ?>
              <label class="hb-check">
                <input type="checkbox" name="condition" value="<?php echo $value; ?>" <?php echo ($condition == $value ? 'checked' : ''); ?>>
                <?php echo $label; ?>
              </label>
            <?php } ?>
          </div>

          <div class="hb-filter-price">
            <label>Price range</label>
            <div id="marketplace-price-slider"></div>
            <div class="row">
              <div class="col-xs-6"><input type="number" name="min_price" placeholder="Min" value="<?php echo htmlspecialchars((string)$min_price, ENT_QUOTES); ?>"></div>
              <div class="col-xs-6"><input type="number" name="max_price" placeholder="Max" value="<?php echo htmlspecialchars((string)$max_price, ENT_QUOTES); ?>"></div>
            </div>
          </div>

          <div>
            <label>Location</label>
            <input type="text" name="location" value="<?php echo htmlspecialchars($location, ENT_QUOTES); ?>" placeholder="Town, state or region">
          </div>

          <div>
            <label>Sort</label>
            <select name="sort">
              <option value="newest" <?php echo ($sort == 'newest' ? 'selected' : ''); ?>>Newest</option>
              <option value="price_asc" <?php echo ($sort == 'price_asc' ? 'selected' : ''); ?>>Price: low to high</option>
              <option value="price_desc" <?php echo ($sort == 'price_desc' ? 'selected' : ''); ?>>Price: high to low</option>
              <option value="closing" <?php echo ($sort == 'closing' ? 'selected' : ''); ?>>Closing soon</option>
            </select>
          </div>

          <button type="submit" class="button btn-variant-1">Apply filters</button>
        </form>
      </aside>

      <main class="col-md-9">
        <div class="hb-listing-row">
          <strong><?php echo number_format($total); ?> marketplace listings</strong>
          <a href="<?php echo FE_rel; ?>members/new-listing/">List gear</a>
        </div>

        <?php if(count($listings) > 0) { ?>
          <div class="hb-card-grid">
            <?php foreach($listings as $listing) {
              $is_auction = ($listing['listing_type'] == 'auction');
              $is_featured = ($listing['listing_tier'] == 'featured' || $listing['add_feature'] > 0 || (!empty($listing['featured_until']) && strtotime($listing['featured_until']) > time()));
              $condition_value = $listing['condition'] ?: 'used_good';
              $close_label = hb_time_remaining($listing['time_close']);
              $icon = $listing['category_icon'] ?: 'ti-tag';
              $image = trim($listing['image']);
              $price = ($is_auction && $listing['price_bid'] > 0 ? $listing['price_bid'] : $listing['price']);
            ?>
              <article class="hb-listing-card">
                <a href="<?php echo FE_rel; ?>browse/product.php?id=<?php echo (int)$listing['id']; ?>" class="hb-listing-card__image">
                  <?php if($image != '') { ?>
                    <img src="<?php echo htmlspecialchars($image, ENT_QUOTES); ?>" alt="<?php echo htmlspecialchars(stripslashes($listing['name']), ENT_QUOTES); ?>">
                  <?php } else { ?>
                    <span class="hb-listing-card__image--placeholder"><i class="ti <?php echo htmlspecialchars($icon, ENT_QUOTES); ?>" aria-hidden="true"></i></span>
                  <?php } ?>
                  <?php if(!empty($listing['sold_at'])) { ?><span class="hb-listing-card__sold">SOLD</span><?php } ?>
                  <button class="hb-listing-card__watchlist" type="button" aria-label="Watch listing"><i class="ti ti-heart" aria-hidden="true"></i></button>
                </a>
                <div class="hb-listing-card__body">
                  <div class="hb-listing-card__badges">
                    <span class="<?php echo ($is_auction ? 'badge-auction' : 'badge-fixed'); ?>"><?php echo ($is_auction ? 'Auction' : 'Fixed price'); ?></span>
                    <span class="<?php echo hb_condition_badge_class($condition_value); ?>"><?php echo hb_format_condition($condition_value); ?></span>
                    <?php if($is_featured) { ?><span class="badge-featured">Featured</span><?php } ?>
                  </div>
                  <h2><a href="<?php echo FE_rel; ?>browse/product.php?id=<?php echo (int)$listing['id']; ?>"><?php echo htmlspecialchars(stripslashes($listing['name']), ENT_QUOTES); ?></a></h2>
                  <p class="hb-listing-card__meta"><?php echo htmlspecialchars($listing['category_name'] ?: 'Marketplace', ENT_QUOTES); ?><?php echo ($listing['pickup_location'] ? ' / '.htmlspecialchars($listing['pickup_location'], ENT_QUOTES) : ''); ?></p>
                  <div class="hb-listing-card__footer">
                    <strong><?php echo LOCALE_currency.$zulu->dollar($price, true); ?></strong>
                    <?php if($close_label) { ?><span class="hb-countdown"><?php echo $close_label; ?></span><?php } ?>
                  </div>
                </div>
              </article>
            <?php } ?>
          </div>

          <?php if($total > $limit) {
            $pages = ceil($total / $limit);
          ?>
            <div class="pagination hb-pagination">
              <?php for($i=1; $i<=$pages; $i++) { ?>
                <a class="<?php echo ($i == $page ? 'active' : ''); ?>" href="<?php echo hb_marketplace_query_url(['pg'=>$i]); ?>"><?php echo $i; ?></a>
              <?php } ?>
            </div>
          <?php } ?>
        <?php } else { ?>
          <div class="hb-empty-state hb-marketplace-empty">
            <i class="ti ti-tags" aria-hidden="true"></i>
            <div class="hb-empty-state__heading">No marketplace listings yet</div>
            <p class="hb-empty-state__sub">Gear, tack, floats and services will appear here as sellers add marketplace listings.</p>
            <a class="button btn-variant-1" href="<?php echo FE_rel; ?>members/new-listing/">List gear</a>
          </div>
        <?php } ?>
      </main>
    </div>
  </div>
</div>

<?php include FE_abs."template/foot.php"; ?>
