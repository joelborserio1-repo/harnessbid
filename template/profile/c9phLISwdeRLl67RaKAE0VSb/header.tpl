
<div class="header">
	<div class="frame">
		<div class="coltable vbottom">
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

			<div class="col nav">
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

			<?php if($class_website->has_social_icons()) { ?>
			<div class="col vmiddle">
				<?php echo $class_website->social_icon_html(); ?>
			</div>
            <?php } ?>
		</div>
    </div>
</div>
