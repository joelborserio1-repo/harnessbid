<?php
//(C)2007-2014 RAZOR WEB DESIGN LIMITED
//ZULU SHOPPING SYSTEM v2.1.1
//BUILT ON PHP & MySQL

define('PAGE_file','support');
define('AUTHORISE',1);
include dirname(__FILE__)."/../includes/loader.php";
include FE_abs."template/head.php";

?>

<div class="frame">

    <?php echo member_breadcrumb(); ?>

    <div class="row">
        <div class="col-lg-8">
            <h1>Your Support Tickets</h1>
        </div>
        <div class="col-lg-4 text-right">
            <a href="<?php echo $zulu->front_link(LINK_account_support_new); ?>" class="button">
                <span class="fas fa-plus-circle"></span> New Ticket
            </a>
        </div>
    </div>

    <?php echo $zulu->notification(); ?>

    <div class="row support-ticket-container">
        <div class="col-md-4">
            <div class="panel panel-primary" style="">
                <div class="panel-heading">
                    My Support Tickets
                </div>
                <div class="panel-body" style="height:600px; overflow-y: scroll;">
                    <?php echo $tickets_list_html; ?>
                </div>
            </div>
        </div>

        <div class="col-md-8">
            <div class="panel panel-default" style="">
                <div class="panel-heading">
                   <div class="row">
                        <div class="col-lg-6">
                            Messages
                        </div>
                        <div class="col-lg-6 text-right">
                            <label>Auto scroll <?php echo $form_edit->input_html("checkbox","chat_auto_scroll", '1',['custom'=>['id'=>'chat_auto_scroll','checked'=>true]]); ?></label>
                        </div>
                   </div>

                </div>
                <div class="panel-body" style="height:453px; overflow-x: hidden; overflow-y: scroll;" id="message-window">
                    <?php echo $message_html; ?>
                </div>
                <div class="panel-footer">
                    <form role="form" action="" method="post">
                        <div class="row">
                            <div class="col-lg-10">
                                <div class="form-group">
                                    <?php if ($current_ticket_status == "Closed"){ ?>
                                    <?php echo $form_edit->input_html("textarea","reply_message","You cant reply to a closed ticket",['class'=>['form-control'],'rows'=>'3', 'custom'=>['disabled' => 'true']]); ?>
                                    <?php }else{ ?>
                                    <?php echo $form_edit->input_html("textarea","reply_message","",['class'=>['form-control'],'rows'=>'3', 'custom'=>['id'=>'reply_message']]); ?>
                                    <?php } ?>
                                </div>
                            </div>
                            <div class="col-lg-2">
                                <div class="form-group">
                                <?php if ($current_ticket_status == "Closed"): ?>
                                    <p class="opt opt-grey"><i class="fas fa-envelope"></i> Email <?php echo $form_edit->input_html('checkbox','send_email',1,['custom'=>['disabled' =>'true']]) ?></p>
                                    <p><?php echo $form_edit->input_html("submit","submit","Send",['custom'=>['disabled' =>'true']]); ?></p>
                                <?php else: ?>
                                    <p class="opt opt-grey"><i class="fas fa-envelope"></i> Email <?php echo $form_edit->input_html('checkbox','send_email',1) ?></p>
                                    <p><?php echo $form_edit->input_html("submit","submit","Send",['id'=>'send_message_btn']); ?></p>
                                    <?php echo $form_edit->input_html("hidden","current_ticket_id", $support_id,['custom'=>['id'=>'current_ticket_id']]); ?>

                                <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php

include FE_abs."template/foot.php";

?>
