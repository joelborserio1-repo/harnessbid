	<!-- Bootstrap Core CSS -->
    <link href="<?php echo MAIN_rel; ?>bower_components/bootstrap/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- MetisMenu CSS -->
    <link href="<?php echo MAIN_rel; ?>bower_components/metisMenu/dist/metisMenu.min.css" rel="stylesheet">

    <!-- Timeline CSS -->
    <link href="<?php echo MAIN_rel; ?>dist/css/timeline.css" rel="stylesheet">

    <!-- Custom CSS -->
    <link href="<?php echo MAIN_rel; ?>dist/css/sb-admin-2.css" rel="stylesheet">

    <!-- Morris Charts CSS -->
    <link href="<?php echo MAIN_rel; ?>bower_components/morrisjs/morris.css" rel="stylesheet">

    <!-- Custom Fonts -->
    <link href="<?php echo MAIN_rel; ?>bower_components/font-awesome/css/all.min.css" rel="stylesheet" type="text/css">
    
    <!-- TPL CSS -->
    <link href="<?php echo TPL_rel; ?>css/style.css" rel="stylesheet">
    <link href="<?php echo TPL_rel; ?>css/style.theme.css" rel="stylesheet">
    
    <!-- HTMLAREA -->
	<link rel="stylesheet" href="<?php echo TPL_rel; ?>assets/redactor/redactor.min.css" />

    <!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
    <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
    <!--[if lt IE 9]>
        <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
        <script src="https://oss.maxcdn.com/libs/respond.js/1.4.2/respond.min.js"></script>
    <![endif]-->
    
    <script>var zl_main_rel = '<?php echo MAIN_rel; ?>';</script>

    <?php if(count($zulu->template->css_file)>0) {
	foreach($zulu->template->css_file as $file) { ?>
	<link type="text/css" rel="stylesheet" href="<?php echo $file; ?>" />
    <?php } } ?>
    
    <?php if(count($zulu->template->css_code)>0) { ?>
    <style type="text/css">
	<?php foreach($zulu->template->css_code as $code_snip) { ?>
		<?php echo $code_snip; ?>
	<?php } ?>
	</style>
	<?php } ?>
    
    <script src="https://use.typekit.net/mxq0zom.js"></script>
    <script>try{Typekit.load({ async: true });}catch(e){}</script>
