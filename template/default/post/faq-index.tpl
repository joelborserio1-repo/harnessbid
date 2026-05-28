<div class="pb-section pb-section-row-1 section-variant-1 section-pad-1 page-title">
  <div class="frame frame-master">
    <div class="pb-container container-fluid">
      <div class="pb-row row pb-row-column-2 align-items-center">
        <div class="pb-column col-sm-8">
          <div class="pb-block pb-block-type-text pb-block-id-129">
            <div class="pb-block-content">
              <h1><?php echo $post->title; ?></h1>
            </div>
          </div>
        </div>
        <div class="pb-column col-sm-4">
          <div class="pb-block pb-block-type-text pb-block-id-130 label">
            <div class="pb-block-content">
              <p><i class="ti ti-check" aria-hidden="true"></i> Home of <em>zero </em><strong>Commissions</strong></p>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
<!--<h2><?php echo ucfirst(implode(" ",$filter_title)); ?></h2>-->

<div class="pb-section section-pad-5">
  <div class="frame">
    <div class="post-wrap post-type-faq box-container">
        <?php foreach($index_loop as $item) { ?>
        <div class="box faq" itemscope itemtype="http://schema.org/Question">
                <h3 itemprop="name"><i class="ti ti-help" aria-hidden="true"></i> <?php echo stripslashes($item['name']); ?></h3>
                <div class="faq-inner" itemprop="text">
                	<?php echo stripslashes($item['content']); ?>
                </div>
        </div>
        <?php } ?>
    </div>
  </div>
</div>

<div class="text-center">
	<?php echo $pagination; ?>
</div>

<?php echo $post->content; ?>
