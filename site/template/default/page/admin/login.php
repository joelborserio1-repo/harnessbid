<?php if(PAGE_action==NULL) { ?>
<form role="form" method="post" action="">
    <fieldset>
        <?php if(!TOGGLE_PIN) { ?>
        <div class="form-group">
            <?php echo $form_login->input_html("input","username",$_POST['username'],array('autofocus'=>true,'placeholder'=>'Username')); ?>
        </div>
        <div class="form-group">
            <?php echo $form_login->input_html("password","password",$_POST['password'],array('placeholder'=>'Password')); ?>
        </div>
        <div class="form-group field-pin">
            <?php echo $form_login->input_html("password","pin",$_POST['pin'],array('autocomplete'=>false,'placeholder'=>'Quick access PIN...','length'=>4)); ?>
        </div>
        <div class="form-group field-pin">
            <?php echo $form_login->input_html("password","pin_c",$_POST['pin_c'],array('autocomplete'=>false,'placeholder'=>'Confirm your PIN...','length'=>4)); ?>
        </div>
        <div class="form-group">
                <div class="checkbox">
            <label>
                <?php echo $form_login->input_html("checkbox","remember",1,array('id'=>'remember','checked'=>($_POST['remember']>0?true:false))); ?> Remember Me
            </label>
                </div>
        </div>
        <div class="form-group">
            <?php echo $form_login->input_html("submit","login","Login",array('class'=>array('btn-block','btn-success'))); ?>
            <?php echo $form_login->input_html("hidden","action","login"); ?>
        </div>
        <div class="form-group">
            <?php echo $form_login->input_html("submit","forgot","Forgot your password?",array('class'=>array('btn-block','btn-danger','confirm'))); ?>
        </div>
        <?php } else { ?>
        <div class="form-group">
            <?php echo $form_login->input_html("password","pin",$_POST['pin'],array('autocomplete'=>false,'autofocus'=>true,'placeholder'=>'Enter your pin code...')); ?>
        </div>
        <div class="form-group">
            <?php echo $form_login->input_html("submit","login","Login",array('class'=>array('btn-block','btn-success'))); ?>
            <?php echo $form_login->input_html("hidden","action","login"); ?>
            <?php echo $form_login->input_html("hidden","method","pin"); ?>
        </div>
        <div class="form-group">
            <a href="<?php echo $zulu->link_page('login',array('query'=>array('Action'=>'user_forget'))); ?>" class="btn btn-danger btn-block">Forget Me / Use Other Account</a>
        </div>
        <?php } ?>
    </fieldset>
</form>
<?php } ?>