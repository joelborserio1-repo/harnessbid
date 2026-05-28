var pb_pshow_manual_item_data = {};

$(document).ready(function() {
	
	//-- MANUAL: ADD Product
    $('.post-builder').on('change','.pshow-sf-product_quick',function() {
		var product_id = $(this).val();
		var block_id = $('#modal-content-edit').data('block');
        
        pb_product_showcase_manual_item_html(product_id, block_id);
        $('#modal-content-edit .pb-block-edit-container .pb-block-edit-block[data-id="'+block_id+'"] .sf-input-product_quick').val('');
        $('#modal-content-edit .pb-block-edit-container .pb-block-edit-block[data-id="'+block_id+'"] .pshow-sf-product_quick').val('');
        $('#modal-content-edit .pb-block-edit-container .pb-block-edit-block[data-id="'+block_id+'"] .sf-input-product_quick').focus();
	});
	
	//-- MANUAL: DEL Product
    $('.post-builder').on('click','.pshow-bt-prod-del',function() {
		var block_id = $('#modal-content-edit').data('block');
        
        $(this).closest('.pshow-manual-item').remove();
        if($('#modal-content-edit .pb-block-edit-container .pb-block-edit-block[data-id="'+block_id+'"] .pshow-html-product-wrapper .pshow-manual-item').length <= 0) {
            $('#modal-content-edit .pb-block-edit-container .pb-block-edit-block[data-id="'+block_id+'"] .pshow-html-product-wrapper .pb-pshow-input-manual-product-blank').prop("disabled",false);
        }
        
        return false;
	});
	
});

function pb_product_showcase_content_edit_open(block_id) {
    $('#modal-content-edit .pb-block-edit-container .pb-block-edit-block[data-id="'+block_id+'"] .pshow-html-product-wrapper .pshow-manual-item').each(function() {
        var product_id = $(this).data('product');
        var product_title = $(this).find('.pshow-manual-item-title').html();
        pb_pshow_manual_item_data[product_id] = product_title;
    });
}

function pb_product_showcase_content_edit_close(block_id) {
    $('#modal-content-edit .pb-block-edit-container .pb-block-edit-block[data-id="'+block_id+'"] .pshow-html-product-wrapper .pshow-manual-item').remove();
    var pshow_item_count = 0;
    $.each(pb_pshow_manual_item_data, function(key, value) {
        pb_product_showcase_manual_item_html(key, block_id);
        pshow_item_count++;
    });
    pb_pshow_manual_item_data = {};
    if(pshow_item_count <= 0) {
        $('#modal-content-edit .pb-block-edit-container .pb-block-edit-block[data-id="'+block_id+'"] .pshow-html-product-wrapper .pb-pshow-input-manual-product-blank').prop("disabled",false);
    }
}

function pb_product_showcase_manual_item_html(product_id, block_id) {
    var post_arr = {};
    post_arr['block_id'] = block_id;
    post_arr['product_id'] = product_id;
    $.ajax({
        url: ajax_rel+"Ajax=post_builder&Do=pshow_manual_add",
        type: 'POST',
        data: post_arr,
        success: function(data) {
            var return_arr = JSON.parse(data);
            if(return_arr.html != null) {
                $('#modal-content-edit .pb-block-edit-container .pb-block-edit-block[data-id="'+block_id+'"] .pshow-html-product-wrapper').append(return_arr.html);
                $('#modal-content-edit .pb-block-edit-container .pb-block-edit-block[data-id="'+block_id+'"] .pshow-html-product-wrapper .pb-pshow-input-manual-product-blank').prop("disabled",true);
            }
        }
    });
}
