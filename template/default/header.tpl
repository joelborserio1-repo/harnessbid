
<div class="header" id="header" itemscope="itemscope" itemtype="https://schema.org/WPHeader">
	<div class="frame">
        <div class="coltable col2 vmiddle" itemscope itemtype="http://schema.org/Organization">
            <div class="col">
            	<a href="<?php echo FE_rel; ?>">
                    <?php if($zulu->template->logo_url!=NULL) { ?>
                    <p class="logo site-logo"><?php echo SITE_title; ?></p>
                    <link itemprop="logo" href="<?php echo $zulu->template->logo_url; ?>" />
                    <link itemprop="url" href="<?php echo FE_url; ?>" />
                    <meta itemprop="description" content="<?php echo $META_description; ?>">
                    <meta itemprop="legalName" content="<?php echo SITE_title; ?>">
                    <?php } else { ?>
                    <p class="logo text"><?php echo SITE_title; ?></p>
                    <span itemprop="description" itemscope="" itemtype="http://schema.org/description">
                        <meta itemprop="description" content="<?php echo $META_description; ?>">
                    </span>
                    <?php } ?>
                </a>
                <a href="#" class="mobile-menu-trigger"><i class="ti ti-menu-2"></i></a>
            </div>
            <div class="col slogan">
                <p class="h3"><?php echo SITE_slogan; ?></p>
				<?php if(SITE_program=='ZULUSHP') { ?>
					<p><span class="head-cart-data"><i class="ti ti-shopping-cart"></i> <?php echo $global_cart_data['_data']['cart_unit']; ?> (<?Php echo LOCALE_currency; ?><?php echo $zulu->dollar($global_cart_data['_data']['cart_total'],true); ?>)</span> <a href="<?php echo FE_rel; ?>checkout/basket.php" class="button bt-outline"><i class="ti ti-shopping-cart"></i> My Basket</a> <a href="<?php echo FE_rel; ?>checkout/index.php" class="button bt-outline"><i class="ti ti-arrow-right"></i> Checkout</a></p>
				<?php } else { ?>
					<?php if($setting['ws_contact_phone']!=NULL?$setting['ws_contact_phone']:CONTACT_phone) { ?>
					<p class="phone">Phone <i class="ti ti-phone"></i><span itemprop="telephone"><?php echo ($setting['ws_contact_phone']!=NULL?$setting['ws_contact_phone']:CONTACT_phone); ?></span></p>
					<?php } ?>
				<?php } ?>
            </div>

			<?php if($class_website->has_social_icons()) { ?>
			<div class="col vmiddle">
				<?php echo $class_website->social_icon_html(); ?>
			</div>
            <?php } ?>

        </div>
    </div>
</div>

<div class="hb-hl-context-bar">
  <div class="frame">
    <div class="hb-hl-context-bar__inner">
      <i class="ti ti-arrow-left" aria-hidden="true"></i>
      <a href="https://harnesslink.com">Back to HarnessLink</a>
      <span class="hb-hl-context-bar__sep">/</span>
      <span>HarnessBid</span>
    </div>
  </div>
</div>

<div class="navigation">
	<div class="frame">
        <div class="mobile-nav-head">
            <a href="<?php echo FE_rel; ?>">
                <?php if($zulu->template->logo_url!=NULL) { ?>
                <p class="logo site-logo"><?php echo SITE_title; ?></p>
                <?php } else { ?>
                <p class="logo text"><?php echo SITE_title; ?></p>
                <?php } ?>
            </a>
            <a href="#" class="mobile-menu-close"><i class="ti ti-x"></i></a>
        </div>
        <ul class="menu" itemscope="itemscope" itemtype="https://schema.org/SiteNavigationElement">
            <?php echo $class_website_menu->build("default",['wrapper'=>false,'schema'=>true]); ?>
            <li class="rlink tab-marketplace<?php echo (defined('PAGE_file') && PAGE_file == 'marketplace' ? ' active sel' : ''); ?>" itemprop="name"><a class="menulink" itemprop="url" href="<?php echo FE_rel; ?>marketplace/"><i class="ti ti-tag"></i> Marketplace</a></li>
            <?php $hb_host = strtolower(isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : ''); ?>
            <?php if(strpos($hb_host, 'harnesslink.com') !== false) { ?>
            <li class="rlink tab-harnesslink" itemprop="name"><a class="menulink" itemprop="url" href="https://harnesslink.com/"><i class="ti ti-news"></i> HarnessLink</a></li>
            <?php } ?>
        </ul>
	</div>
</div>
