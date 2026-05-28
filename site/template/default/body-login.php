
<div class="container">
    <div class="row">
        <div class="col-lg-4 col-lg-offset-4 col-md-6 col-md-offset-3 col-sm-8 col-sm-offset-2 col-xs-12">
            <div class="login-head text-center">
                <div class="logo"><?php echo MAIN_name; ?></div>
            </div>
            <div class="login-body">
                <h1 class="text-center"><?php echo $zulu->nav->title; ?></h1>
                <?php echo $zulu->notification(); ?>
                <?php include(TPL_root."page/".SECTION_path.PAGE_frame.".php"); ?>
            </div>
        </div>
    </div>
</div>

<!-- jQuery -->
<script src="<?php echo MAIN_rel; ?>bower_components/jquery/dist/jquery.min.js"></script>

<!-- Bootstrap Core JavaScript -->
<script src="<?php echo MAIN_rel; ?>bower_components/bootstrap/dist/js/bootstrap.min.js"></script>

<?php if(count($zulu->template->js_file)>0) {
foreach($zulu->template->js_file as $file) { ?>
<script type="text/javascript" src="<?php echo $file; ?>"></script>
<?php } } ?>
<?php if(count($zulu->template->jquery)>0) { ?>
<script type="text/javascript">
    $(document).ready(function(e) {
    <?php echo implode("\n\n",$zulu->template->jquery); ?>
    });
</script>
<?php } ?>
<?php if(count($zulu->template->js_file)>0) { ?>
<script type="text/javascript">
<?php foreach($zulu->template->js_code as $code) { ?>
<?php echo $code; ?>
<?php } ?>
</script>
<?php } ?>