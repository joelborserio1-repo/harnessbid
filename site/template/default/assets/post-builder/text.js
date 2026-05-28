
$(document).ready(function(e) {
    
});

function pb_text_content_edit_open(block_id) {
    $('#modal-content-edit .pb-block-edit-container .pb-block-edit-block[data-id="'+block_id+'"] .redactor-box .redactor-in').each(function() {
        pb_block_data[$(this).next('textarea').attr('name')] = $(this).html();
    });
}

function pb_text_content_edit_close(block_id) {
    $('#modal-content-edit .pb-block-edit-container .pb-block-edit-block[data-id="'+block_id+'"] .redactor-box .redactor-in').each(function() {
        $(this).html(pb_block_data[$(this).next('textarea').attr('name')]);
    });
}
