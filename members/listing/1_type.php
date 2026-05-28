<?php

define('PAGE_file','new_listing_type');
define('AUTHORISE',1);
include dirname(__FILE__)."/../../includes/loader.php";
include FE_abs."template/head.php";
include "header.php";

$selected_mode = isset($_POST['listing_mode']) ? $_POST['listing_mode'] : '';
if($selected_mode == '') {
    if(isset($_POST['listing_kind']) && $_POST['listing_kind'] == 'marketplace') {
        $selected_mode = 'marketplace';
    } elseif(isset($_POST['listing_type']) && $_POST['listing_type'] == 'classified') {
        $selected_mode = 'horse_buynow';
    } elseif(isset($_POST['listing_type']) && $_POST['listing_type'] == 'auction') {
        $selected_mode = 'horse_auction';
    }
}
$selected_kind = ($selected_mode == 'marketplace' ? 'marketplace' : 'horse');
$selected_type = ($selected_mode == 'horse_auction' ? 'auction' : 'classified');
?>

<input type="hidden" name="listing_mode" id="listing_mode_input" value="<?php echo htmlspecialchars($selected_mode, ENT_QUOTES); ?>">
<input type="hidden" name="listing_kind" id="listing_kind_input" value="<?php echo htmlspecialchars($selected_kind, ENT_QUOTES); ?>">
<input type="hidden" name="listing_type" id="listing_type_input" value="<?php echo htmlspecialchars($selected_type, ENT_QUOTES); ?>">

<div class="hb-wizard">
  <div class="hb-wizard__steps">
    <?php
    $wizard_steps = ['Listing type','Category','Details','Media','Contact','Confirm'];
    foreach($wizard_steps as $idx=>$label) {
    ?>
      <div class="hb-wizard__step<?php echo ($idx == 0 ? ' active' : ''); ?>">
        <div class="hb-wizard__step-inner">
          <div class="hb-wizard__step-num"><?php echo $idx + 1; ?></div>
          <span class="hb-wizard__step-label"><?php echo $label; ?></span>
        </div>
      </div>
      <?php if($idx < count($wizard_steps) - 1) { ?><div class="hb-wizard__step-connector"></div><?php } ?>
    <?php } ?>
  </div>

  <div class="hb-wizard__body">
    <h2 class="hb-wizard__heading">What are you listing?</h2>
    <p class="hb-wizard__sub">Choose your listing type to get started.</p>

    <div class="hb-mode-grid">
      <div class="hb-mode-card<?php echo ($selected_mode == 'horse_auction' ? ' selected' : ''); ?>" data-mode="horse_auction" data-kind="horse" data-type="auction">
        <i class="ti ti-gavel" aria-hidden="true"></i>
        <div class="hb-mode-card__title">Horse auction</div>
        <div class="hb-mode-card__desc">Timed auction — buyers bid, horse goes to highest bidder</div>
      </div>
      <div class="hb-mode-card<?php echo ($selected_mode == 'horse_buynow' ? ' selected' : ''); ?>" data-mode="horse_buynow" data-kind="horse" data-type="classified">
        <i class="ti ti-horse" aria-hidden="true"></i>
        <div class="hb-mode-card__title">Horse — buy now</div>
        <div class="hb-mode-card__desc">Fixed price — buyer purchases immediately</div>
      </div>
      <div class="hb-mode-card<?php echo ($selected_mode == 'marketplace' ? ' selected' : ''); ?>" data-mode="marketplace" data-kind="marketplace" data-type="classified">
        <i class="ti ti-tag" aria-hidden="true"></i>
        <div class="hb-mode-card__title">Marketplace gear</div>
        <div class="hb-mode-card__desc">Sell equipment, floats, tack, memorabilia and more</div>
      </div>
    </div>

    <div class="hb-marketplace-extra<?php echo ($selected_mode == 'marketplace' ? ' active' : ''); ?>" id="marketplace-extra-fields">
      <h3>Marketplace listing details</h3>
      <div class="row">
        <div class="col-sm-6">
          <label>Category</label>
          <select name="meta[marketplace_category]" class="form-control">
            <option value="">Choose a category later</option>
            <?php
            $cat_result = $db->mysqli->query("SELECT slug,name FROM marketplace_category WHERE active = 1 ORDER BY sort_order ASC, name ASC");
            if($cat_result) {
                while($cat_row = $cat_result->fetch_assoc()) {
                    $checked = (isset($_POST['meta']['marketplace_category']) && $_POST['meta']['marketplace_category'] == $cat_row['slug'] ? 'selected' : '');
                    echo '<option value="'.htmlspecialchars($cat_row['slug'], ENT_QUOTES).'" '.$checked.'>'.htmlspecialchars($cat_row['name'], ENT_QUOTES).'</option>';
                }
            }
            ?>
          </select>
        </div>
        <div class="col-sm-6">
          <label>Condition</label>
          <select name="meta[marketplace_condition]" class="form-control">
            <option value="">Choose condition later</option>
            <option value="new">New</option>
            <option value="used_excellent">Used - excellent</option>
            <option value="used_good">Used - good</option>
            <option value="used_fair">Used - fair</option>
            <option value="parts">Parts</option>
          </select>
        </div>
        <div class="col-sm-6">
          <label>Brand</label>
          <input type="text" name="meta[marketplace_brand]" value="<?php echo htmlspecialchars(isset($_POST['meta']['marketplace_brand']) ? $_POST['meta']['marketplace_brand'] : '', ENT_QUOTES); ?>" class="form-control">
        </div>
        <div class="col-sm-6">
          <label>Year</label>
          <input type="number" name="meta[marketplace_year]" value="<?php echo htmlspecialchars(isset($_POST['meta']['marketplace_year']) ? $_POST['meta']['marketplace_year'] : '', ENT_QUOTES); ?>" class="form-control">
        </div>
        <div class="col-sm-6">
          <label>Contact preference</label>
          <select name="meta[contact_method]" class="form-control">
            <option value="message">Message</option>
            <option value="phone">Phone</option>
            <option value="both">Both</option>
            <option value="checkout">Checkout</option>
          </select>
        </div>
        <div class="col-sm-6">
          <label>Listing tier</label>
          <select name="meta[listing_tier]" class="form-control">
            <option value="free">Free</option>
            <option value="standard">Standard</option>
            <option value="featured">Featured</option>
          </select>
        </div>
      </div>
    </div>

    <?php if(!CLIENT_subscribed) { ?>
      <div class="field listing-membership-sell">
        <i class="ti ti-user-plus" aria-hidden="true"></i>
        <p class="heading">Do you want to save $<?php echo number_format($saving_value); ?>?</p>
        <p>Plus enjoy other Premier member benefits. Join instantly to become a member and start your listing. (From $<?php echo $membership_subscription['price']; ?> per month)</p>
        <p class="radio-input">
          <label class="d-flex align-items-center mr-md-4 mb-md-0 mb-2">
            <?php echo $form_edit->input_html('radio', 'premier_sub', '1', ['class'=>['checkbox-sub-join', 'mr-1'],'checked'=>(isset($_POST['premier_sub']) && $_POST['premier_sub']=='1')]); ?>
            <b>Yes please sign me up</b>
          </label>
          <label class="d-flex align-items-center">
            <?php echo $form_edit->input_html('radio', 'premier_sub', '0', ['class'=>['checkbox-sub-no', 'mr-1'],'checked'=>(isset($_POST['premier_sub']) && $_POST['premier_sub']=='0')]); ?> No, skip please
          </label>
        </p>
      </div>
    <?php } ?>
  </div>

  <div class="hb-wizard__footer">
    <div></div>
    <button class="hb-wizard__next" id="btn-mode-next" type="submit" <?php echo ($selected_mode == '' ? 'disabled' : ''); ?>>
      Continue <i class="ti ti-arrow-right" aria-hidden="true"></i>
    </button>
  </div>
</div>

<script>
document.querySelectorAll('.hb-mode-card').forEach(function(card) {
  card.addEventListener('click', function() {
    document.querySelectorAll('.hb-mode-card').forEach(function(c) { c.classList.remove('selected'); });
    card.classList.add('selected');
    document.getElementById('btn-mode-next').disabled = false;
    document.getElementById('listing_mode_input').value = card.dataset.mode;
    document.getElementById('listing_kind_input').value = card.dataset.kind;
    document.getElementById('listing_type_input').value = card.dataset.type;
    var extra = document.getElementById('marketplace-extra-fields');
    if(extra) {
      extra.classList.toggle('active', card.dataset.mode === 'marketplace');
    }
  });
});
</script>

<?php
include "footer.php";
include FE_abs."template/foot.php";
?>
