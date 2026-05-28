
<div class="footer">
	<div class="frame">
   		<div class="column w8 col-center">
       	<div class="coltable col3 vtop">
			<?php if($theme_setting['footer_col1_title']!=NULL) { ?>
        	<div class="col">
            	<p class="head h4"><?php echo $theme_setting['footer_col1_title']; ?></p>
				<div class="break"></div>
                <?php echo $class_website_menu->build('foot_1',['wrapper'=>['class'=>['foot-menu']]]); ?>
            </div>
			<?php } ?>
			<?php if($theme_setting['footer_col2_title']!=NULL) { ?>
        	<div class="col">
            	<p class="head h4"><?php echo $theme_setting['footer_col2_title']; ?></p>
				<div class="break"></div>
                <?php echo $class_website_menu->build('foot_2',['wrapper'=>['class'=>['foot-menu','contact']]]); ?>
            </div>
			<?php } ?>
			<?php if($theme_setting['footer_col3_title']!=NULL) { ?>
        	<div class="col">
            	<p class="head h4"><?php echo $theme_setting['footer_col3_title']; ?></p>
				<div class="break"></div>
                <?php echo $class_website_menu->build('foot_3',['wrapper'=>['class'=>['foot-menu','contact']]]); ?>
            </div>
			<?php } ?>
        </div>
        </div>
    </div>
</div>
<!--footer-->

<div class="copyright">
	<div class="frame">
    	<?php if(MASTER_mode=='web') { ?>
    	<p>©<?php echo date("Y")." ".SITE_title; ?>. <a href="http://www.razorweb.co.nz?click=<?php echo MAIN_company; ?>" title="Another professional web site design by RAZOR Web Design." target="_blank">Site by RAZOR Web Design</a></p>
   		<?php } else { ?>
    	<p>©<?php echo date("Y")." ".SITE_title; ?>. <a href="<?php echo URL_infosite; ?>" target="_blank">Site by <?php echo MAIN_name; ?></a></p>
    	<?php } ?>
    </div>
</div>
<!--footer-->
