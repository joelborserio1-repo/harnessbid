
<div class="header">
	<div class="coltable vmiddle">
		<div class="col logo-wrap">
			<a href="<?php echo FE_rel; ?>">
                <?php if($zulu->template->logo_url!=NULL) { ?>
                <p class="logo site-logo"><?php echo SITE_title; ?></p>
                <?php } else { ?>
                <p class="logo text"><?php echo SITE_title; ?></p>
                <?php } ?>
			</a>
            <button class="mobile-menu-trigger" aria-label="Open menu" aria-expanded="false"><i class="ti ti-menu-2" aria-hidden="true"></i></button>
		</div>
		<div class="col">
			<form method="GET" action="<?php echo FE_rel; ?>browse/">
			<div class="top-search">
				<input name="search" placeholder="Search..." value="" type="text"><button type="submit" aria-label="Search"><i class="ti ti-search" aria-hidden="true"></i></button>
			</div>
			</form>
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
            <a href="#" class="mobile-menu-close"><i class="ti ti-x" aria-hidden="true"></i></a>
        </div>
        <ul class="menu" itemscope="itemscope" itemtype="https://schema.org/SiteNavigationElement">
            <?php echo $class_website_menu->build("default",['wrapper'=>false,'schema'=>true]); ?>

			<?php if(SITE_program=='ZULUSHP') { ?>
			<li class="rlink cartlink"><a href="<?php echo FE_rel; ?>checkout/basket.php" class="menulink"><i class="ti ti-shopping-cart" aria-hidden="true"></i> <?php echo ($global_cart_data['_data']['cart_unit']>0?$global_cart_data['_data']['cart_unit']:'0').' Item'.$zulu->s($global_cart_data['_data']['cart_unit']); ?><?Php echo ($global_cart_data['_data']['cart_total']>0?' ('.LOCALE_currency.$zulu->dollar($global_cart_data['_data']['cart_total'],true).')':NULL); ?></a></li>
			<li class="rlink buylink"><a href="<?php echo FE_rel; ?>checkout/index.php" class="menulink"><i class="ti ti-arrow-right" aria-hidden="true"></i> Checkout</a></li>
			<?php } ?>
        </ul>
	</div>
</div>
