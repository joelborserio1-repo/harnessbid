<div class="uc-page-wrapper">
	<div class="uc-container">
		<p class="logo" style="background-image:url(<?php echo $zulu->template->logo_url; ?>);"><?php echo SITE_title; ?></p>

	    <?php if($zulu->template->error_screen) { ?>
	    <h1>Oops, <?php echo $zulu->template->error_type; ?> error</h1>
	    <p><?php echo $zulu->template->error_msg; ?></p>
	    <?php } ?>

	    <?php if(SITE_status==0) { ?>
	    <h1>Coming Soon</h1>
	    <p><?php echo ($zulu->template->site_status_msg!=NULL?$zulu->template->site_status_msg:"Our website is under construction, please check back soon."); ?></p>
	    <?php } elseif(SITE_status==2) { ?>
	    <h1>Maintenance Mode</h1>
	    <p><?php echo ($zulu->template->site_status_msg!=NULL?$zulu->template->site_status_msg:"We're undergoing maintenance on our website, please check back soon."); ?></p>
	    <?php } ?>
	</div>
</div>

</body>
</html>

<?php exit; ?>
