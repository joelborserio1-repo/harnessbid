<?php echo $breadcrumb; ?>

<h1><?php echo $post->title; ?></h1>
<?php echo $post->content; ?>

<?php echo $class_post->post_content($post_data,['display'=>'gallery']); ?>