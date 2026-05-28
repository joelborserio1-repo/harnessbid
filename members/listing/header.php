<!--<div class="section section-blue">
    <div class="frame">
        <div class="container-fluid p-0">
            <div class="row no-gutters">
                <div class="col-auto mr-auto">
                    <h2 class="h1">List your horse...</h2>
                </div>
                <div class="col-auto">
                    <p>Home of <span>Zero</span> <b>Commissions</b></p>
                </div>
            </div>
        </div>
    </div>
</div>-->

<div class="pb-section pb-section-row-1 section-variant-1 section-pad-1 page-title">
  <div class="frame frame-master">
    <div class="pb-container container-fluid">
      <div class="pb-row row pb-row-column-2 align-items-center">
        <div class="pb-column col-sm-8">
          <div class="pb-block pb-block-type-text pb-block-id-129">
            <div class="pb-block-content">
                <?php if($is_edit) { ?>
                <h1>Edit Listing...</h1>
                <?php } elseif($is_relist) { ?>
                <h1>Relist...</h1>
                <?php } else { ?>
                <h1>List Your Horse...</h1>
                <?php } ?>

            </div>
          </div>
        </div>
        <div class="pb-column col-sm-4">
          <div class="pb-block pb-block-type-text pb-block-id-130 label">
            <div class="pb-block-content">
              <p><span class="fas fa-check"></span> Home of <em>zero </em><strong>Commissions</strong></p>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<div class="listing-process-section">
  <div class="frame">
      <div class="listing-progress-bar container-fluid p-0">
          <div class="row no-gutters text-center">
              <?php if($new_listing) { ?>
              <div class="col">
                  <div class="progress-segment <?= ($step>1?'complete':($step==1?'active':null)); ?>">
                      <div class="progress-bar">
                        <div class="fas fa-gavel"></div>
                      </div>
                      <h5>Listing Type</h5>
                      <h6>Step 1</h6>
                  </div>
              </div>
              <?php } ?>
              <div class="col">
                  <div class="progress-segment <?= ($step>2?'complete':($step==2?'active':null)); ?>">
                      <div class="progress-bar">
                        <div class="fas fa-horse"></div>
                      </div>
                      <h5>Details</h5>
                      <h6>Step <?= ($new_listing?'2':'1'); ?></h6>
                  </div>
              </div>
              <div class="col">
                  <div class="progress-segment <?= ($step>3?'complete':($step==3?'active':null)); ?>">
                      <div class="progress-bar">
                        <div class="fas fa-images"></div>
                      </div>
                      <h5>Photos & Video</h5>
                      <h6>Step <?= ($new_listing?'3':'2'); ?></h6>
                  </div>
              </div>
              <div class="col">
                  <div class="progress-segment <?= ($step>4?'complete':($step==4?'active':null)); ?>">
                      <div class="progress-bar">
                        <div class="fas fa-file-signature"></div>
                      </div>
                      <h5>Finalise</h5>
                      <h6>Step <?= ($new_listing?'4':'3'); ?></h6>
                  </div>
              </div>
          </div>
      </div>
  </div>

<div class="frame">

    <div class="container-fluid p-0">
        <div class="row justify-content-center">
            <div class="col-lg-6 col-md-9 col-12">

                <?php echo $zulu->notification(); ?>

                <form method="post" action="" enctype="multipart/form-data" id="payment-form">
