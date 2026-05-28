
<div class="footer" itemscope="itemscope" itemtype="https://schema.org/WPFooter">
	<div class="frame">
    	<div class="coltable col vtop">
        	<div class="col">
            	<p class="head h4">Information</p>
                <?php echo $class_website_menu->build('foot_1',['schema'=>true,'wrapper'=>['class'=>['foot-menu']]]); ?>
            </div>
        	<div class="col">
            	<p class="head h4">Contact Us</p>
                <?php echo $class_website_menu->build('foot_2',['schema'=>true,'wrapper'=>['class'=>['foot-menu','contact']]]); ?>
            </div>
        	<div class="col">
            	<p class="head h4">Socialise</p>
                <?php echo $class_website_menu->build('foot_3',['schema'=>true,'wrapper'=>['class'=>['foot-menu','contact']]]); ?>
            </div>

        </div>
    </div>
</div>
<!--footer-->

<div class="copyright">
	<div class="frame">
   	    <a href="<?php echo MAIN_url."web/feed.php?type=".$_GET['TabName']; ?>" alt="subscribe via rss"><i class="ti ti-rss hb-rss-icon" aria-hidden="true"></i></a>
    	<p>&copy;<?php echo date("Y")." ".SITE_title; ?>. <a href="http://www.razorweb.co.nz?click=<?php echo MAIN_company; ?>" title="Another professional web site design by RAZOR Web Design." target="_blank">Site by RAZOR Web Design</a></p>
    </div>
</div>
<!--footer-->
