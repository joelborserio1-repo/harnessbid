
<?php if(count($zulu->template->js_file) > 0) {
    foreach($zulu->template->js_file as $file) {
        $config = [];
        if(is_array($file)) {
            $config = $file;
            $file = $file['src'];
        } ?>
        <script type="text/javascript" src="<?php echo $file; ?>"<?php echo (isset($config['param'])?" ".implode(' ',$config['param']):null); ?>></script>
    <?php } ?>
<!--end page js includes-->
<?php } ?>
<?php if(count($zulu->template->js_code) > 0 || count($zulu->template->jquery_code) > 0) { ?>
<script type="text/javascript">
    <?php foreach($zulu->template->js_code as $js_code) { ?>
    <?php echo $js_code; ?>
    <?php } ?>

    $(document).ready(function(e) {
        <?php foreach($zulu->template->jquery_code as $js_code) { ?>
        <?php echo $js_code; ?>
        <?php } ?>
    });
</script>
<!--end script foot-->
<?php } ?>
<?php if($class_setting->data['ws_module_adobe_typekit'] != NULL) { ?>
<script src="https://use.typekit.net/<?php echo $class_setting->data['ws_module_adobe_typekit']; ?>.js"></script>
<script>try{Typekit.load({ async: true });}catch(e){}</script>
<!--end typekit-->
<?php } ?>
