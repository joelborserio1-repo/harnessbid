<?php
//(C)2007-2014 RAZOR WEB DESIGN LIMITED
//ZULU SHOPPING SYSTEM v2.1.1
//BUILT ON PHP & MySQL

//SESSION
session_start();
define(PAGE_file,'membership_view');
define(AUTHORISE,1);


//INCLUDES
include("../includes/loader.php");


//BC
$zulu->template->breadcrumb[] = ['link'=>'membership.php','label'=>'My Memberships'];
$zulu->template->breadcrumb[] = ['label'=>'Membership Details'];

?>
<!DOCTYPE html>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<title>Membership Information | <?php echo META_title; ?></title>
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
            
            <?php if($view==NULL) { ?>
            <h1><?php echo stripslashes($renew_data['title']); ?></h1>
    		<?php echo notification(); ?>
            <p>Your membership information will appear below.</p>
            
            <div class="coltable col<?php echo ($renew_data['credit_value']>0?'4':'3'); ?> vtop padcol">
            	<div class="col">
                	<div class="box min-80">
                    	<h3>Status</h3>
                        <p><?php echo $renew_status; ?><br><?php echo $auto_status; ?></p>
                    </div>
                </div>
            	<div class="col">
                	<div class="box min-80">
                        <?php if($renew_data['status'] != 3) { ?>
                        <h3><?php echo (!$renew_data['cancel']?'Renews':'Ends'); ?></h3>
                        <p><?php echo $zulu->dateDecode($renew_data['renew_next']); ?></p>
                        <?php } else { ?>
                        <h3>Renews</h3>
                        <p>Never</p>
                        <?php } ?>
                    </div>
                </div>
                <div class="col">
                	<div class="box min-80">
                    	<h3>Annual Fee</h3>
                        <p>$<?php echo number_format($renew_data['price'],2); ?></p>
                    </div>
                </div>
                <?php if($renew_data['credit_value'] > 0) { ?>
                <div class="col">
                	<div class="box min-80">
                    	<h3>Credit Balance</h3>
                        <p><i class="far fa-ticket-alt"></i> <?php echo ($renew_meta['credit_value']>0?$renew_meta['credit_value']:0); ?></p>
                    </div>
                </div>
                <?php } ?>
            </div>
            <hr>
            <h2>Your renewals</h2>
            <?php echo $line_table; ?>
            <p>&nbsp;</p>
            <p>
                <?php if($renew_data['status'] == '1' && !$renew_data['cancel']) { ?>
                    <a href="#" class="button" id="update-pay-bt"><span class="far fa-credit-card"></span> Update Payment Method</a> 
                <?php } ?>
                <?php if($can_renew) { ?>
                    <a href="<?php echo FE_rel; ?>browse/product.php?Action=AddCart&Renew=<?php echo $token; ?>" class="button red"><span class="fas fa-sync-alt"></span> Renew Now</a> 
                <?php } ?>
                <?php if($renew_data['status'] != '3' && !$renew_data['cancel']) { ?>
                    <?php echo $auto_bt; ?> 
                    <a href="<?php echo $main_URL_REL."members/membership_view.php?token=".$renew_data['token']."&Action=Cancel"; ?>" class="button confirm-membership"><span class="fas fa-times"></span> Cancel Membership</a> 
                    <?php if($class_setting->data['renew_switch']==1) { ?>
                        <a href="<?php echo $main_URL_REL."members/membership_switch.php?token=".$renew_data['token']; ?>" class="button" title="You can switch this membership to another one."><span class="fas fa-exchange-alt"></span> Switch Membership</a> 
                    <?php } ?>
                <?php /* if($template_data['file_id']>0) { ?><a href="<?php echo $main_URL_REL."members/content.php#tpl-{$template_data['id']}"; ?>" class="button btn-orange" title="View restricted content."><span class="fas fa-lock"></span> View Restricted Content</a><?php }*/ ?>
                <?php } ?>
            </p>
            <div id='update-pay-block'>
                <?php if($has_current_pay) { ?>
                <h4>Current Method:</h4>
                <p><?php echo stripslashes($module_row['name_client']); ?></p>
                <?php } ?>
                <h4>Select Method:</h4>
                <p>
                    <?php foreach($module_options['options'] as $module_option) { ?>
                    <a href='<?php echo $zulu->front_link(true,['query'=>['token'=>$token,'action'=>'update_pay','method'=>$module_option['module']]]); ?>' class="button"><?php echo stripslashes($module_option['name_client']); ?></a> 
                    <?php } ?>
                </p>
            </div>
        	<?php } ?>
        </div>
            
    </div>
</div>
<?php include("../template/foot.php"); ?>
