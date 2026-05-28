
<div class="global-contact">
	<ul class="item">
		<div class="gc-corner"></div>
		<li><a href="<?php echo FE_rel; ?>members/login.php"><i class="ti ti-user-circle" aria-hidden="true"></i> Sign In</a></li>
		<li><a href="<?php echo FE_rel; ?>members/register.php"><i class="ti ti-pencil" aria-hidden="true"></i> Register</a></li>
	</ul>
</div>

<div id="header" class="header" itemscope="itemscope" itemtype="https://schema.org/WPHeader">
	<div class="frame">

		<div class="coltable col3 vmiddle">
			<div class="col">
                <div class="navigation">
                    <div class="mobile-nav-head">
                        <a href="<?php echo FE_rel; ?>">
                            <?php if($zulu->template->logo_url!=NULL) { ?>
                            <p class="logo site-logo"><?php echo SITE_title; ?></p>
                            <?php } else { ?>
                            <p class="logo text"><?php echo SITE_title; ?></p>
                            <?php } ?>
                        </a>
                        <a href="#" class="mobile-menu-close"><i class="ti ti-x" aria-hidden="true"></i></a>
                    </div>
                    <ul class="menu" itemscope="itemscope" itemtype="https://schema.org/SiteNavigationElement">
                        <?php echo $class_website_menu->build("default",['wrapper'=>false,'schema'=>true]); ?>
                    </ul>
                </div>
			</div>
			<div class="logo-wrap">
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
                <button class="mobile-menu-trigger" aria-label="Open menu" aria-expanded="false"><i class="ti ti-menu-2" aria-hidden="true"></i></button>
			</div>
			<div class="col">
				<ul class="menu shop-menu" itemscope="itemscope" itemtype="https://schema.org/SiteNavigationElement">
					<li class="rlink pricebox"><i class="ti ti-shopping-cart" aria-hidden="true"></i> <?php echo $global_cart_data['_data']['cart_unit']; ?> <span class="val"><?Php echo LOCALE_currency; ?><?php echo $zulu->dollar($global_cart_data['_data']['cart_total'],true); ?></span></li>
					<li class="rlink"><a href="<?php echo FE_rel; ?>checkout/basket.php"><i class="ti ti-shopping-bag" aria-hidden="true"></i> My Basket</a></li>
					<li class="rlink"><a href="<?php echo FE_rel; ?>checkout/index.php"><i class="ti ti-arrow-right" aria-hidden="true"></i> Checkout</a></li>
				</ul>
			</div>

			<?php if($class_website->has_social_icons()) { ?>
			<div class="col vmiddle">
				<?php echo $class_website->social_icon_html(); ?>
			</div>
            <?php } ?>
		</div>

    </div>
</div>
