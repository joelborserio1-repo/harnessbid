
$(document).ready(function(e) {
    
});

function pb_image_content_edit_close(block_id) {
    if($('#input-image-file-'+block_id).val() != '') {
        var img_path = $('.pb-block-edit-block[data-id="'+block_id+'"] .image-container .image').data('path');
        $('.pb-block-edit-block[data-id="'+block_id+'"] .image-container .image img').attr('src',img_path+$('#input-image-file-'+block_id).val()+"?"+Math.floor((Math.random() * 100) + 1));
    } else {
        $('.pb-block-edit-block[data-id="'+block_id+'"] .image-container .image').addClass('hide');
    }
}

function pb_image_image_upload(id, image) {
    if(image !== undefined) {
        $('.pb-block-edit-block[data-id="'+id+'"] #input-image-file-'+id).val(image);
        $('.pb-block-edit-block[data-id="'+id+'"] .image-container .image').removeClass('hide');
        $('.pb-block-edit-block[data-id="'+id+'"] .image-upl-wrapper .uploadifive-queue .close').trigger('click');
        $('.pb-block-edit-block[data-id="'+id+'"] .image-upl-wrapper #upl_image_file-'+id).uploadifive('clearQueue');
        var img_path = $('.pb-block-edit-block[data-id="'+id+'"] .image-container .image').data('path');
        $('.pb-block-edit-block[data-id="'+id+'"] .image-container .image img').attr('src',img_path+image+"?"+Math.floor((Math.random() * 100) + 1));
    }
}
