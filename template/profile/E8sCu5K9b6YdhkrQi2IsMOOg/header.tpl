<div id="header" class="header" itemscope="itemscope" itemtype="https://schema.org/WPHeader">
	<div class="frame">
		<div class="menu-box">
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
		<div class="logo-box">
			<div class="logo-wrap">
				<a href="<?php echo FE_rel; ?>">
					<?php if($zulu->template->logo_url!=NULL) { ?>
					<!--<p class="logo site-logo"><?php echo SITE_title; ?></p>-->
					<img class="site-logo" src="<?php echo $zulu->template->logo_url; ?>" alt="<?php echo htmlspecialchars(SITE_title); ?>">
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
			</div>
		</div>
		<div class="account-box">
			<a href="/members/" class="button btn-variant-1">Account</a>
			<a href="/members/new-listing/" class="button btn-variant-2">Sell Now</a>
		</div>
		<button class="mobile-menu-trigger" aria-label="Open menu" aria-expanded="false"><i class="ti ti-menu-2" aria-hidden="true"></i></button>
    </div>
</div>
