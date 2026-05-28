<!-- /.row -->
<div class="row">
	<?php if(PAGE_action==NULL) { ?>
    
    <div class="col-lg-12">
		<p><a href="<?php echo $zulu->link_page('user',array('query'=>array('Action'=>'edit'))); ?>"><button class="btn btn-primary" type="button"><span class="fas fa-plus-circle"></span> Add New User</button></a></p>
        <div class="panel panel-default">
            <!-- /.panel-heading -->
            <div class="panel-body">
    			<?php echo $zulu->template->body; ?>
            </div>
    	</div>
    </div>
    <?php } ?>
	<?php if(PAGE_action=='welcome') { ?>
    
    <div class="col-md-12">
    	<p>Here are some great resources to help you get started, but it's pretty easy to use, so don't feel pressured to remember everything!</p>
        <div class="alert alert-warning alert-dismissable">
            <button aria-hidden="true" data-dismiss="alert" class="close" type="button">×</button>
            <span class="fas fa-thumbs-up"></span> You can always come back to this page! Just click 'Welcome' in the 'Help' tab in the top right of any page.
        </div>
        
        <hr>
        
        <?php if(MASTER_mode=='web') { ?> 
        <div class="row">
        	<div class="col-md-6">
            	<div class="panel panel-primary">
                	<div class="panel-heading">
                    	<h3 class="margin-0"><span class="fas fa-rocket"></span> Make a start...</h3>
                    </div>
                    <div class="panel-body">
						<p>To get started, all you need to do is follow the below steps.</b></p>
                        <hr>
                         <p class="text-medium">
                         	<b><a target="_blank" href="<?php echo $zulu->link_page('website',['query'=>['Action'=>'site']]); ?>">1. Setup your business details</a></b> so people can find you on the web.<Br>
                         	<b><a target="_blank" href="<?php echo $zulu->link_page('website',['query'=>['Page'=>'post','type'=>'page']]); ?>">2. Setup your web pages</a></b> and add the content / images. <span class="opt opt-grey">We'll tidy this up after for you!</span><Br>
                         	<b><a target="_blank" href="<?php echo $zulu->link_page('website',['query'=>['Page'=>'product']]); ?>">3. Products</a></b>, if you're selling online consider loading your products in for sale.<Br>
                         	<?php /* <b><a target="_blank" href="<?php echo $zulu->link_page('rule'); ?>">4. Create mail rules</a></b> and keep your clients in the loop with regular follow-ups.<Br>
                         	<b><a target="_blank" href="<?php echo $zulu->link_page('project'); ?>">5. Add your projects</a></b> and start tracking expenses &amp; tasks.<Br>
                         	<b><a target="_blank" href="<?php echo $zulu->link_page('renew'); ?>">6. Recurring subscriptions</a></b> can be setup if you offer ongoing services / subscriptions.</p>*/ ?>
                        <hr>
                        <h4><span class="fas fa-check"></span> We'll be in contact to start your professional template!</h4>
	                    <p>One of the Zulu team will be in contact to get your logo & branding to start creating your website.</p>
                        <hr>
                        <p class="text-medium"><i class="far fa-credit-card"></i> But before we start, please ensure you've subscribed and paid the first months plan fee.<br><a href="<?Php echo $zulu->link_page('account',['query'=>['Action'=>'subscription']]); ?>" class="btn btn-danger"><i class="fas fa-rocket"></i> Subscribe &amp; get started!</a>
                    </div>
                </div>
            </div>
        	<div class="col-md-6">
				<img class="responsive" src="https://zulusys.nz/images/web-plan/lineup-trio.png" alt="trio of websites" />
            </div>
        </div>
        <?php } else { ?>
        <div class="row">
        	<div class="col-md-6">
            	<div class="panel panel-primary">
                	<div class="panel-heading">
                    	<h3 class="margin-0"><span class="fas fa-play"></span> Key setup steps to get you ahead</h3>
                    </div>
                    <div class="panel-body">
						<p>Zulu is an all-in-one tool that allows you to manage your business easily. Use the below steps to get started!</p>
                         <p><b><span class="fas fa-save"></span> Have spreadsheets? Import your clients and products <a href="<?php echo $zulu->link_page('data'); ?>">here</a>.</b></p>
                        <hr>
                         <p class="text-medium">
                         	<b><a target="_blank" href="<?php echo $zulu->link_page('client'); ?>">1. Add your clients</a></b> so you can use <?php echo MAIN_name; ?>'s key features.<Br>
                         	<b><a target="_blank" href="<?php echo $zulu->link_page('product'); ?>">2. Add your inventory</a></b> so you can create sales and quotes easily.<Br>
                         	<b><a target="_blank" href="<?php echo $zulu->link_page('user'); ?>">3. Add users</a></b>, your staff can login and use the timesheet tool and complete tasks.<Br>
                         	<b><a target="_blank" href="<?php echo $zulu->link_page('rule'); ?>">4. Create mail rules</a></b> and keep your clients in the loop with regular follow-ups.<Br>
                         	<b><a target="_blank" href="<?php echo $zulu->link_page('project'); ?>">5. Add your projects</a></b> and start tracking expenses &amp; tasks.<Br>
                         	<b><a target="_blank" href="<?php echo $zulu->link_page('renew'); ?>">6. Recurring subscriptions</a></b> can be setup if you offer ongoing services / subscriptions.</p>
                        <hr>
                        <h4><span class="fas fa-check"></span> Well done, but wait:</h4>
                        <p class="text-medium">There are many other features available, just browse through the left sidebar for more.</p>
                    </div>
                </div>
            </div>
        	<div class="col-md-6">
            	<div class="panel panel-primary">
                	<div class="panel-heading">
                    	<h3 class="margin-0"><span class="fas fa-play"></span> Must-try features for any business</h3>
                    </div>
                    <div class="panel-body">
	                    	<p>Zulu is built to make your business easier to run and more efficient when it comes to time management. It's also built to help build better relationships with your clients, therefore generating more business and a better bottom line.</p>
                        <hr>
                         <ul class="bullet-list text-medium">
							<li><b><a href="<?php echo $zulu->link_page('project'); ?>">Projects</a></b> can track expenses and labour spent for client jobs.</li>
							<li><b><a href="<?php echo $zulu->link_page('task'); ?>">Tasks</a></b> can track individual jobs you do for clients and bill them easily.</li>
							<li><b><a href="<?php echo $zulu->link_page('rule'); ?>">Mail</a></b> allows you to automatically check up with leads and clients.</li>
							<li><b><a href="<?php echo $zulu->link_page('sell'); ?>">Point of sale</a></b> tool can create sales on the go or in your store.</li>
							<li><b><a href="<?php echo $zulu->link_page('renew'); ?>">Subscriptions</a></b> let you track &amp; charge client recurring costs.</li>
							<li><b><a href="<?php echo $zulu->link_page('timesheet'); ?>">Timesheets</a></b> can give your staff the ability to log their day.</li>
                         </ul>
                      	<hr>
                        <h4><span class="fas fa-check"></span> Well done, but wait:</h4>
                        <p class="text-medium">There are many other features available, just browse through the left sidebar for more.</p>
                    </div>
                </div>
            </div>
        </div>
        <?php } ?>
        <div class="alert alert-default">
        	<div class="coltable col2 vmiddle">
            	<div class="col">
                	<h2>Feeling stuck?</h2>
                    <h4>Click the 'Help' icon in the top right of each page for tailored articles for each feature.</h4>
                </div>
                <div class="col">
                	<img src="<?php echo TPL_rel; ?>images/misc/help-toggle.jpg" />
                </div>
            </div>
        </div>	
        
        <div class="alert alert-info">
        	<span class="fas fa-life-ring"></span> If you feel you still need help while using our site: <b>Talk to our dedicated support team!</b> <span class="far fa-envelope"></span> <a href="mailto:<?Php echo SUPPORT_email; ?>"><?Php echo SUPPORT_email; ?></a> or <span class="fas fa-phone"></span> <?Php echo SUPPORT_phone; ?>
        </div>
        
        <hr>
                            
        <p><a href="<?Php echo $zulu->link_page('account',array('query'=>array('Action'=>'welcome_dismiss'))); ?>"><button class="btn btn-primary" type="button"><span class="fas fa-rocket"></span> Continue &amp; Get Started!</button></a></p>
    </div>
    
    <?php } ?>
	<?php if(PAGE_action=='statement') { ?>
    <div class="col-md-12">
    <p>Your account statement is shown below, this includes payments in and out of your account. <a href="<?php echo $zulu->link_page(PAGE_file,['query'=>['Action'=>'subscription']]); ?>"><button type="button" class="btn btn-xs btn-default"><span class="fas fa-ticket-alt"></span> View Subscription</button></a></p>
    <?php echo $zulu->template->account_statement; ?>
    </div>
    <?php } ?>
	<?php if(PAGE_action=='subscription') { ?>
    
    <?php if(!$subscribed['autobilling']&&$subscribed['active']&&$subscribed['trial_used']&&$subscribed['expire_time']>time()) { ?>
    <div class="col-md-12">
    <div class="alert alert-warning">
    <span class="fas fa-exclamation-triangle"></span> Your account is active <?php echo $user_data['name_first']; ?> until <?php echo $zulu->date($subscribed['expire_time']); ?>. You can setup your subscription below and renew your plan to keep your services all active.
    </div>
    </div>
    <?php } ?>
	<?php if($subscribed['autobilling']&&$subscribed['active']) { ?>
    <div class="col-md-12">
    <p>Thanks for being a subscriber, <?php echo $user_data['name_first']; ?>. Your account summary is shown below.</p>
    
    <div class="row">
        <div class="col-md-6">
        	<div class="panel panel-success">
                <div class="panel-heading"><span class="fas fa-ticket-alt"></span> Your Subscription</div>
                <div class="panel-body">
                <ul class="spec-list text-medium">
                <li>
                    <p class="label">Subscription</p>
                    <p class="value"><?php echo $subscribed['plan']['name']; ?></p>
                </li>
                <li>
                    <p class="label">Cost Per Renewal</p>
                    <p class="value">$<?php echo $subscription->nextBillAmount; ?></p>
                </li>
                <li>
                    <p class="label"><?php if($meta['plan_cancelled']<=0) { ?>Next Renewal<?php } else { ?>Expires<?php } ?></p>
                    <p class="value"><?php echo date("d/m/Y",$paidthrough->getTimestamp()); ?></p>
                </li>
                <li>
                    <p class="label">Tax (GST)</p>
                    <p class="value"><?php echo (strstr($subscription->planId,'nogst')?"Tax exempt (you are outside New Zealand)":"Above includes GST ($".number_format(($subscription->nextBillAmount/1.15),2)." excl.)"); ?></p>
				</li>
                </ul>
            </div>
            </div><div class="panel panel-warning">
                <div class="panel-heading"><span class="fas fa-list"></span> Options</div>
                <div class="panel-body">
                
                <?php if($meta['plan_cancelled']>0) { ?>
                <div class="alert alert-danger">
                    <p class="margin-bottom-0">Your account was cancelled, you can reactivate your account by <a href="<?php echo URL_contactsite; ?>" target="_blank">contacting us</a>.</p>
                </div>
                <?php } else { ?>
                <p>You can cancel your automatic payments below...</p>
                <p class="color-grey text-small">Your account will remain active until the date of renewal. Any account data in Zulu after the expiry date is subject to deletion.</p>
                <p class="margin-bottom-0"><a class="confirm-prompt" data-msg="Are you sure you want to cancel your subscription? You will have to re-subscribe after your current expiry lapses." href="<?php echo $zulu->link_page('account',array('query'=>array('Action'=>'subscription','Do'=>'Suspend'))); ?>"><button type="button" class="btn btn-danger"><span class="fas fa-times"></span> Cancel Subscription</button></a></p>
				<?php } ?>
                </div>
            </div>
         </div>
        <div class="col-md-6">
        	<div class="panel panel-default">
                <div class="panel-heading"><span class="far fa-money-bill"></span> Account Statement</div>
                <div class="panel-body">
                	<p>Your current account balance is <b>$<?php echo $class_user->account_balance(); ?></b>.</p>
			     <?php echo $zulu->template->account_statement; ?>
                </div>
            </div>
        </div>
        </div>
    </div>
    </div>
    <?php } else { ?>
    <div class="col-md-12">
    <?php if($freetrial) { ?>
    <div class="panel panel-success">
        <div class="panel-heading">Free Trial! Thats a good reason to have a go!</div>
        <div class="panel-body">
        Just select any of the plans below and you'll get a FREE 30-day trial.
        </div>
    </div>
    <?php } ?>
    
    <?php if($_GET['plan']) { ?>
    <p>To finalise your subscription &amp; get instant access, please complete your payment details below. Need support? Please contact us <a href="<?php echo $main_URL_REL; ?>support/">here</a>.</p>
    
    <div class="row">
        <div class="col-md-6">
            <div class="panel panel-info">
              <div class="panel-heading"><span class="far fa-credit-card"></span> How are you paying?</div>
              <div class="panel-body">
              <div class="modules">
                <div class="module module-cc">
<h3><span class="far fa-credit-card"></span> Instant Credit Card</h3>
                    <form id="checkout" method="post" action="">
                      <div id="payment-form"></div>
                      <button class="btn btn-success" type="submit"><span class="fas fa-chevron-right"></span> Subscribe Now</button>
                      <input type="hidden" name="action" value="bt_card" />
                    </form>
                     <p><br><a href="https://www.braintreegateway.com/merchants/<?php echo BRAINTREE_merchant; ?>/verified" target="_blank"><img src="https://s3.amazonaws.com/braintree-badges/braintree-badge-wide-light.png" width="240px" border="0"/></a></p>
                </div>
                <div class="module module-bankdep">
<h3><span class="fas fa-university"></span> Bank Deposit</h3>
<p>Please use '<?php echo strtoupper($_SESSION['zl_user']['name']); ?> - <?php echo strtoupper($_SESSION['zl_user']['id']); ?>' as a reference.</p>
<p><b><i>030406 0758073 00 - Westpac Bank New Zealand</i></b></p>
                </div>
              </div>
              </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="panel panel-success order-statement">
              <div class="panel-heading"><span class="fas fa-thumbs-up"></span> Thanks <?php echo $user_data['name_first']; ?>, Your Order...</div>
              <div class="panel-body">
              <ul class="spec-list text-medium">
                <li>
                    <p class="label">Subscription</p>
                  <p class="value"><?php echo $plan_data['name']; ?></p>
                </li>
                <li>
                    <p class="label">Period</p>
                  <p class="value"><?php echo $period_short; ?> Monthly</p>
                </li>
                <li>
                    <p class="label">Subscription Renews</p>
                  <p class="value"><?php echo date("d/m/Y",$expiry); ?></p>
                </li>
                <li>
                    <p class="label">Cost Per Month</p>
                  <p class="value">$<?Php echo $plan_data['price']['monthly']; ?></p>
                </li>
                <li class="order-total">
                    <p class="label"><span class="far fa-money-bill"></span> Order Total</p>
                  <p class="value">$<?Php echo $plan_data['price']['period']; ?></p>
                </li>
              </ul>
              <p class="text-small color-grey margin-0 no-margin"><span class="fas fa-info-circle"></span> You can change your plan at any time. <?php echo $tax_suffix; ?></p>
            </div>
            </div>
       </div>
    </div>
    
    <hr>
    
    <?php if($row_MEM['account_balance'] >= $total_pay) { ?>
    <h2>Use Account Credit</h2>
    <p>Your Credit: $<?php echo $row_MEM['account_balance']; ?><br>
    <a class="button" href="subscribe.php?Action=PayCredit&plan=<?php echo $_GET['plan']; ?>">Use Now</a></p>
    <hr>
        <?php } ?>
      
    <?php /*
    <hr>
    <h2>PayPal</h2>

    <form name="_xclick" action="https://www.paypal.com/cgi-bin/webscr" method="post">
        <input type="hidden" name="amount" value="<?php echo $total_pay; ?>">
        <input type="hidden" name="cmd" value="_xclick">
        <input type="hidden" name="business" value="pazams@yahoo.co.nz">
        <input type="hidden" name="currency_code" value="NZD">
        <input type="hidden" name="item_name" value="<?php echo $main_COMPANY; ?> Subscription">
        <input type="hidden" name="custom" value="<?php echo urlencode(serialize($custom)); ?>">
        <input type="hidden" name="notify_url" value="<?php echo $main_URL; ?>includes/paypal/ipn.php">
        <input type="hidden" name="return" value="<?php echo $main_URL; ?>members/subscribe.php?Action=Paid">
        <input type="hidden" name="image_url" value="<?php echo $main_URL; ?>images/paypal_logo.jpg" />
        <input type="hidden" name="no_shipping" value="1" />
        <input type="hidden" name="rm" value="2" />
        <input type="image" src="http://www.paypal.com/en_US/i/btn/btn_buynow_LG.gif" border="0" name="submit" alt="Make payments with PayPal - it's fast, free and secure!">
    </form>
    */ ?>
    
    <?php } else { ?>
    
        <?php if($_GET['Action']=='Credit') { ?>
        
        <h2>Add Credit for Text &amp; Other Services </h2>
        <p>This will add credit instantly to your account, via credit card, using <a href="http://www.paypal.com" target="_blank">PayPal.com</a>.</p>
        <hr>
        
            <form name="_xclick" action="https://www.paypal.com/cgi-bin/webscr" method="post">
            <div class="form-block">
            <div class="field">
                <label>Amount to topup:</label>
                <input type="text" name="amount" value="$<?php echo dollar($_POST['amount'],1); ?>">
            </div>
            </div>
            <div class="form-block">
            <div class="field submit" style="min-height:0;">
                <input type="hidden" name="cmd" value="_xclick">
                <input type="hidden" name="business" value="pazams@yahoo.co.nz">
                <input type="hidden" name="currency_code" value="NZD">
                <input type="hidden" name="item_name" value="<?php echo $main_COMPANY; ?> Credit">
                <input type="hidden" name="custom" value="<?php echo urlencode(serialize(array('member'=>$member_ID))); ?>">
                <input type="hidden" name="notify_url" value="<?php echo $main_URL; ?>includes/paypal/ipn.php">
                <input type="hidden" name="return" value="<?php echo $main_URL; ?>members/subscribe.php?Action=Paid">
                <input type="hidden" name="image_url" value="<?php echo $main_URL; ?>images/paypal_logo.jpg" />
                <input type="hidden" name="no_shipping" value="1" />
                <input type="hidden" name="rm" value="2" />
                <input type="image" src="http://www.paypal.com/en_US/i/btn/btn_buynow_LG.gif" border="0" name="submit" alt="Make payments with PayPal - it's fast, free and secure!">
            </div>
            </div>
            
            <p class="disclaimer">Important Note: You will be taken to complete your payment with PayPal. You will be returned to <?php echo $main_COMPANY; ?> once your payment is made &amp; your account balance will instantly update.</p>
            
            </form>
        
        <?php } else { ?>
    
            <?php if($row_MEM['account_trial']>0) { ?>
            <p class="capsule c-info"><span class="fas fa-wrench"></span> <b>How it works...</b> Each plan is billed in advance, if you select 1 Month, you will be billed every month. If you select 12 months you will billed the discounted rate for the 12 month period in advance. Our plans automatically renew, however, you can always cancel instantly if you wish. We also <span class="fas fa-heart"></span> feedback, so with your subscription, you'll help make Roost more productive!</p>
            <?php } ?>
            
            <p><a href="<?php echo $zulu->link_page('account',array('query'=>array('Action'=>'statement'))); ?>" class="" title="You can view a log of your account transactions here."><button class="btn btn-warning"><span class="fas fa-list"></span> Transaction Statement</button></a></p>
            
            <hr>
            <h2>Select your subscription...</h2>
            <form action="" method="get">
                <div class="">
                	<p><button type="submit" name="go" class="btn btn-success"><span class="fas fa-check"></span> Continue &amp; Complete Subscription...</button></p>
                </div>
                
            	<?php echo $plan; ?>
                
                <?php echo $form_edit->input_html('hidden','Page',PAGE_file); ?>
                <?php echo $form_edit->input_html('hidden','Action',PAGE_action); ?>
            </form>

        <?php } ?>
        
    <?php } ?>
    <?php } ?>
    <?php } ?>
</div>