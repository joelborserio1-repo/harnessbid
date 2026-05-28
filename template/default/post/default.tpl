<?php if($post->_meta->post_builder == 0 && !$zulu->template->body->hide_title) { ?>
<h1><?php echo $post->title; ?></h1>
<?php } ?>

<?php echo $post->content; ?>