<?php
//(C)2007-2014 RAZOR WEB DESIGN LIMITED
//ZULU SHOPPING SYSTEM v2.1.1
//BUILT ON PHP & MySQL

//SESSION
session_start();
define(PAGE_file,'membership_purchase');
define(AUTHORISE,1);


//INCLUDES
include("../includes/loader.php");


//BC
$zulu->template->breadcrumb[] = ['link'=>FE_rel.'members/membership/','label'=>'Subscription and payment'];
$zulu->template->breadcrumb[] = ['label'=>'Purchase Subscription'];

?>
<!DOCTYPE html>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<?php echo $meta_TAGS; ?>
	<?php echo $meta_SCRIPT; ?>
   	 
	<?php include("../template/meta.php"); ?>
	
</head>

<body class="<?php echo implode(" ",$zulu->template->body_class); ?>">

<?php include("../template/head.php"); ?>

<!--Body Content-->
<div id="wrapper">
    
    <div class="wrapper-content">
    	<div class="frame">
    		
            <?php echo member_breadcrumb(); ?>
            
            <h1>Purchase Subscription</h1>
    		<?php echo notification(); ?>
	                            
            <div class="membership-block mb-alt">
				<div class="member-box member-premium">
					<h3 class="no-margin"><i class="fas fa-crown"></i> <?php echo $template_row['title']; ?> <b>$<?php echo $template_row['price']; ?> <span class="mini">/<?php echo ($template_row['renew_interval']>1?$template_row['renew_interval']." ":null).strtolower($class_renew->config->renew_scale[$template_row['renew_scale']]); ?></span></b></h3>
				</div>
            </div>
            
            <form class="sub-form" method="post" name="update" id="update" action="">
                
                <?php /*?><div class="sub-column">
                <h3 class="form_subcaption">Your Details</h3>
                <div class="form-block style">
                <div class="field">
                    <label>First Name <span class="denote">*</span></label>
                    <?php echo $form_edit->input_html('input','name_first',$_POST['name_first']); ?>
                </div>
                <div class="field">
                    <label>Last Name <span class="denote">*</span></label>
                    <?php echo $form_edit->input_html('input','name_last',$_POST['name_last']); ?>
                </div>
                <div class="field">
                    <label>Email Address <span class="denote">*</span></label>
                    <?php echo $form_edit->input_html('email','email',$_POST['email']); ?>
                </div>
                <div class="field">
                    <label>Phone Number <span class="denote">*</span></label>
                    <?php echo $form_edit->input_html('tel','phone',$_POST['phone']); ?>
                </div>
                </div>
                </div><?php */?>

                <div class="sub-column">
                    <h3 class="form_subcaption">Billing Address</h3>
                    <div class="form-block style">
                        <div class="field">
                            <label>Address <span class="denote">*</span></label>
                            <?php echo $form_edit->input_html('input','bill_address',$_POST['bill_address']); ?>
                        </div>
                        <div class="field">
                            <label>Suburb</label>
                            <?php echo $form_edit->input_html('input','bill_suburb',$_POST['bill_suburb']); ?>
                        </div>
                        <div class="field">
                            <label>City <span class="denote">*</span></label>
                            <?php echo $form_edit->input_html('input','bill_city',$_POST['bill_city']); ?>
                        </div>
                        <div class="field w50">
                            <label>Postcode</label>
                            <?php echo $form_edit->input_html('input','bill_post',$_POST['bill_post']); ?>
                        </div>
                        <div class="field w50">
                            <label>Country <span class="denote">*</span></label>
                            <?php if($class_setting->data['ws_shop_country_lock'] != NULL) { ?>
                                <p class="text-regular"><?php echo $class_setting->data['ws_shop_country_lock']; ?></p>
                                <?php echo $form_edit->input_html('hidden','bill_country',$class_setting->data['ws_shop_country_lock']); ?>
                            <?php } else { ?>
                                <?php echo $form_edit->input_html('select','bill_country',$_POST['bill_country'],['option'=>[''=>'Select...']+$form_edit->country_option()]); ?>
                            <?php } ?>
                        </div>
                    </div>
                </div>
				                
                <div class="sub-column">
                    <div class="discount-block">
                        <h3>Discount Code</h3>
                        <div class="form-block style member-discount-form">
                        <div class="field w50">
                            <?= $form_edit->input_html('input','code',$_POST['code'],['placeholder'=>'Enter your code...','class'=>['code-field']]); ?>
                        </div>
                        <div class="field w50">
                            <button type="button" name="submit-code" class="submit-code">Apply</button>
                        </div>
                        <div class="field code-info">
                            <?php if($has_coupon) { ?>
                            <?= $coupon_html; ?>
                            <?php } ?>	
                        </div>
                        </div>
                    </div>
                    
                    <h3 class="form_subcaption">Payment</h3>
                    <div class="form-block style">
                        <?php echo $module_data['html']; ?>
                        <div class="field submit">
                            <?php echo $form_edit->input_html('submit','submit','Pay Now'); ?>
                            <?php echo $form_edit->input_html('hidden','action','submit'); ?>
                        </div>
                    </div>
                </div>
                
            </form>
            
    </div>
            
</div>
</div>

<?php include("../template/foot.php"); ?>
