<div class="footer">
	<div class="frame">
		<div class="foot-menu-box brand-box">
			<img class="site-logo" src="<?php echo $zulu->template->logo_url; ?>" alt="<?php echo SITE_title; ?>">
			<?php if(isset($setting['ws_meta_description']) && $setting['ws_meta_description']){?><p><?php echo $setting['ws_meta_description'];?></p><?php } ?>
			<div class="region-wrapper">
				<h4>Change region...</h4>
				<div class="region-flags">
					<?php foreach($footer_locations as $footer_location) { ?>
                    <a href="<?= $zulu->front_link(LINK_browse, ['query'=>['location_set'=>$footer_location->id]]); ?>" title="<?= $footer_location->name; ?>">
						<img src="<?= $footer_location->image(); ?>" alt="<?= $footer_location->name; ?>" />
					</a>
                    <?php } ?>
					<!-- <?php print_r($location_options); ?> -->
				</div>
				<?php if(isset($_POST['location_id']) && $_POST['location_id'] == 1){ ?>
				<div class="mt-5 footer-contact-usa">
					<h4 class="mb-4">Need Help, Contact us at:</h4>
					<ul>
						<li class="rlink"><a href="tel:(609) 437 7838" class="menulink" target="_self"><b>PH:</b> (609) 437 7838</a></li>
						<li class="rlink"><a href="mailto:harnessbid22@gmail.com" class="menulink" target="_self"><b>EM:</b> harnessbid22@gmail.com</a></li>
					</ul>
				</div>
				<?php } ?>
			</div>
		</div>
		<div class="foot-menu-box">
			<p class="head h4">Browse</p>
			<?php echo $class_website_menu->build('foot_1',['wrapper'=>['class'=>['foot-menu']]]); ?>
		</div>
		<div class="foot-menu-box">
			<p class="head h4">Information</p>
			<?php echo $class_website_menu->build('foot_2',['wrapper'=>['class'=>['foot-menu']]]); ?>
		</div>
		<div class="foot-menu-box">
			<p class="head h4">About</p>
			<?php echo $class_website_menu->build('foot_3',['wrapper'=>['class'=>['foot-menu','contact']]]); ?>
			<!--<ul class="foot-menu contact">
				<?php if($setting['ws_contact_phone']){ ?>
				<li class="rlink"><a href="tel:<?php echo $setting['ws_contact_phone']; ?>" class="menulink" target="_self"><b>PH</b> <?php echo $setting['ws_contact_phone']; ?></a></li>
				<?php } ?>
				<?php if($setting['ws_contact_email']){ ?>
				<li class="rlink"><a href="mailto:<?php echo $setting['ws_contact_email']; ?>" class="menulink" target="_self"><b>EM</b> <?php echo $setting['ws_contact_email']; ?></a></li>
				<?php } ?>
				<?php if($setting['ws_addr_addr'] || $setting['ws_addr_suburb'] || $setting['ws_addr_post'] || $setting['ws_addr_city']){ ?>
				<li class="rlink"><a class="menulink" target="_self"><b>AD</b> <?php echo $setting['ws_addr_addr']; ?>,<br> <?php echo $setting['ws_addr_suburb']; ?>, <?php echo $setting['ws_addr_post']; ?>, <?php echo $setting['ws_addr_city']; ?></a></li>
				<?php } ?>
			</ul>-->
		</div>
	</div>
</div>
<div class="sub-footer">
	<div class="frame">
			<img class="site-logo" src="<?php echo $zulu->template->logo_url; ?>" alt="<?php echo SITE_title; ?>">
			<div class="copyright">
				<?php if(MASTER_mode=='web') { ?>
				<p>©<?php echo date("Y")." ".SITE_title; ?> <a href="http://www.razorweb.co.nz?click=<?php echo MAIN_company; ?>" title="Another professional web site design by RAZOR." target="_blank">Site by RAZOR</a></p>
				<?php } else { ?>
				<p>©<?php echo date("Y")." ".SITE_title; ?>. <a href="<?php echo URL_infosite; ?>" target="_blank">Site by <?php echo MAIN_name; ?></a></p>
				<?php } ?>
			</div>
		</div>
	</div>
<!--footer-->
