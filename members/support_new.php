<?php
//(C)2007-2014 RAZOR WEB DESIGN LIMITED
//ZULU SHOPPING SYSTEM v2.1.1
//BUILT ON PHP & MySQL

define('PAGE_file','support_new');
define('AUTHORISE',1);
include dirname(__FILE__)."/../includes/loader.php";
include FE_abs."template/head.php";

?>

<div class="frame">

    <?php echo member_breadcrumb(); ?>

    <h1>New Support Ticket <?php echo $object_link_html ?></h1>

    <?php echo $zulu->notification(); ?>

    <div class="form_table">
        <form method="post" name="ticket_submit" action="">
            <div class="form-block style">
                <div class="field">
                    <label>Name <em>*</em></label>
                    <?= $form_edit->input_html('input', 'ticket_name', (isset($_POST['ticket_name'])?$_POST['ticket_name']:$name_prefill)); ?>
                </div>
                <div class="field">
                    <label>Email <em>*</em></label>
                    <?= $form_edit->input_html('email', 'ticket_email', (isset($_POST['ticket_email'])?$_POST['ticket_email']:$email_prefill)); ?>
                </div>
                <div class="field">
                    <label>Subject </label>
                    <?= $form_edit->input_html('input', 'ticket_subject', $_POST['ticket_subject']); ?>
                </div>
                <div class="field single">
                    <label>Message <em>*</em></label>
                    <?= $form_edit->input_html('textarea', 'ticket_message', $_POST['ticket_message'], ['placeholder'=>'Please enter your query...']); ?>
                </div>
                <div class="field">
                    <p class="opt opt-grey"><i class="far fa-envelope"></i> Email reply to <?php echo MAIN_company; ?>? <?php echo $form_edit->input_html('checkbox', 'send_email', 1) ?></p>
                </div>
                <div class="field submit">
                    <?= $form_edit->input_html('submit', 'submit', '<span class="fas fa-check"></span> Send'); ?>
                </div>
            </div>
        </form>
    </div>

</div>

<?php

include FE_abs."template/foot.php";

?>
