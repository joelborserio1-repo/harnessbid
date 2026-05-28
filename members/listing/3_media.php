<?php

define('PAGE_file','new_listing_media');
define('AUTHORISE',1);
include dirname(__FILE__)."/../../includes/loader.php";
include FE_abs."template/head.php";
include "header.php";

?>

<div class="form-block single UppyImages">
    <div class="field">
        <h1 class="h2"><?= ($new_listing?'3':'2'); ?>. Photos &amp; Media</h1>
    </div>

    <div class="field w100 field-heading">
        <div class="row no-gutters justify-content-between">
            <div class="col-auto">
                <h4>Photos</h4>
            </div>
            <!--<div class="col-auto">
                <a href="#" class="help-link"><?= $zulu->icon('question-circle'); ?> <mark>Help Me</mark></a>
            </div>-->
        </div>
    </div>
    <div class="field w100">
        <div class="row no-gutters">
            <div class="col-12 col-sm-auto UppyButton"></div>
            <div class="col-12 col-sm-auto ml-sm-4">
                <p><span class="grey">Up to <?= $photos_allowed; ?> photos</p></span>
            </div>
        </div>
        <div class="UppyImageProgressBar"></div>
    </div>
    <div class="field w100">
        <hr />
    </div>
    <div class="field w100">
        <div class="photo-row row">
            <?php for($i=0; $i<$photos_allowed; $i++) { ?>
            <div class="photo-col col-md-4 col-sm-6<?= ($_SESSION['LISTING']['images'][$i]?' active':null); ?>">
                <div class="box photo-box">
                    <img src="<?= ($_SESSION['LISTING']['images'][$i]?MAIN_rel.$upload_dir_images.$_SESSION['LISTING']['images'][$i]:null); ?>" alt="photo <?= $i+1; ?>" />
                    <div class="photo-controls">
                        <a href="#" class="photo-remove"><?= $zulu->icon('times'); ?></a>
                    </div>
                </div>
            </div>
            <?php } ?>
        </div>
    </div>

    <?php if($_SESSION['LISTING']['add_video']) { ?>
    <div class="field w100 field-heading">
        <div class="row no-gutters justify-content-between">
            <div class="col-auto">
                <h4>Videos</h4>
            </div>
            <!--<div class="col-auto">
                <a href="#" class="help-link"><?= $zulu->icon('question-circle'); ?> <mark>Help Me</mark></a>
            </div>-->
        </div>
    </div>
    <div class="field w100">
        <div class="field-inset-wrapper">
            <label>Paste Youtube or Vimeo link / URL</label>
            <?= $form_edit->input_html('input', 'meta[video_1]', $_POST['meta']['video_1'], ['placeholder'=>'Paste Youtube or Vimeo link / URL']); ?>
        </div>
    </div>
    <div class="field w100">
        <div class="field-inset-wrapper">
            <label>Paste Youtube or Vimeo link / URL</label>
            <?= $form_edit->input_html('input', 'meta[video_2]', $_POST['meta']['video_2'], ['placeholder'=>'Paste Youtube or Vimeo link / URL']); ?>
        </div>
    </div>
    <div class="caption">
        <i class="far fa-info-circle"></i> When pasting, ensure you have the full URL including 'https://' at the start.
    </div>
    <?php } ?>

    <div class="field w100 field-custom pedigree-provider-highlight">
        <p class="h3">Pedigree information is automated with <?= MAIN_name; ?>...</p>
        <p class="h4">We will use <span class="pedigree-provider-logo"><?= PEDIGREE_provider; ?></span> to deliver buyers your horses pedigree.</p>
        <p class="action">
            Want to manually add your pedigree? <a href="#" class="pedigree-toggle">Click here</a>
        </p>
    </div>

    <div class="field w100 field-heading pedigree-toggle-target">
        <h4>Upload Custom Pedigree</h4>
    </div>
    <div class="field w100 pedigree-toggle-target">
        <div class="field-inset-wrapper">
            <label>Paste a pedigree website link / URL</label>
            <?= $form_edit->input_html('input', 'meta[pedigree_link]', $_POST['meta']['pedigree_link'], ['placeholder'=>'Paste a pedigree website link / URL']); ?>
        </div>
    </div>
    <div class="field w100 pedigree-toggle-target">
        <label>Or upload a file <i class="far fa-upload"></i> with the pedigree information...</label><br />
        <div class="UppyPedigree"></div>
        <div class="UppyPedigreeProgressBar"></div>
        <div class="row no-gutters<?= (!isset($_SESSION['LISTING']['meta']['pedigree_file'])||!$_SESSION['LISTING']['meta']['pedigree_file']?' hide':null); ?>" id="pedigree-file-info">
            <div class="col-12 col-sm-auto">
                <p>Uploaded: <span id="pedigree-file-name"><?= $_SESSION['LISTING']['meta']['pedigree_file']; ?></span></p>
            </div>
            <div class="col-12 col-sm-auto ml-sm-4">
                <p><a href="#" id="pedigree-remove" class="red"><?= $zulu->icon('times'); ?> Remove File</a></p>
            </div>
        </div>
    </div>

    <div class="field w100 pedigree-toggle-target">
        <p class="blue">
            <b>Find your pedigree:</b>
            <?php foreach($locations as $key=>$location) { ?>
            <a href="<?= $location->pedigree_link; ?>" target="_blank"><?= $location->name; ?></a><?= ($key<count($locations)-1?', ':null); ?>
            <?php } ?>
        </p>
    </div>

</div>

<div class="form-block style">
    <div class="field submit">
        <?= $form_edit->input_html('submit', 'submit', 'Continue to Listing Summary '.$zulu->icon('arrow-right'), ['class'=>['bt-block', 'btn-next']]); ?>
    </div>
</div>

<div class="form-block style back">
    <div class="field submit">
        <a href="<?= $zulu->front_link($url_back); ?>" class="button btn-back"><?= $zulu->icon('arrow-left'); ?> Back to Details</a>
    </div>
</div>

<?php

include "footer.php";
include FE_abs."template/foot.php";

?>
