var pb_item_button_new_count = 0;

$(document).ready(function() {

    $('.post-builder').on('click','div[data-type="button"] .pb-item-row-add',function() {
        var block_id = $('#modal-content-edit').data('block');
        pb_item_button_edit_item(0, block_id);
        return false;
    });
    
    $('.post-builder').on('click','.button-options .pb-item-remove-row',function() {
        var trow = $(this).closest('tr');
        var pb_item_id = trow.data('id');
        var block_id = $('#modal-content-edit').data('block');
        
        $('#modal-content-edit .pb-block-edit-container .pb-block-edit-block[data-id="'+block_id+'"] .pb-item-edit-container[data-type="button"] .pb-item-block[data-id="'+pb_item_id+'"] input[name="pb_post['+pb_item_id+'][remove]"]').val('1');
        trow.fadeOut(function() {
            trow.addClass('inactive');
            if($('#modal-content-edit .pb-block-edit-container .pb-block-edit-block[data-id="'+block_id+'"] .button-options tbody tr:not(.inactive)').length <= 0) {
                $('#modal-content-edit .pb-block-edit-container .pb-block-edit-block[data-id="'+block_id+'"] .button-options').addClass('hide');
                $('#modal-content-edit .pb-block-edit-container .pb-block-edit-block[data-id="'+block_id+'"] .pb-item-row-add').removeClass('btn-xs');
            }    
        });

        return false;
    });
    
    $('.post-builder').on('click','.button-options .pb-item-edit-row',function() {
        var block_id = $('#modal-content-edit').data('block');
        var id = $(this).data('id');
        pb_item_button_edit_item(id, block_id);
        return false;
    });
    
    $('.post-builder').on('click','#modal-content-edit .btn-close-modal',function() {
        var block_id = $('#modal-content-edit').data('block');
        if($('#modal-content-edit .pb-block-edit-container .pb-block-edit-block[data-id="'+block_id+'"] .pb-item-edit-container[data-type="button"]').length > 0) {
            var pb_item_id;
            $('#modal-content-edit .pb-block-edit-container .pb-block-edit-block[data-id="'+block_id+'"] .pb-item-edit-container[data-type="button"] .pb-item-block').each(function() {
                if($(this).is(':visible')) {
                    pb_item_id = $(this).data('id');
                }
            });
            if(pb_item_id != null) {
                var pb_item_block = $('#modal-content-edit .pb-block-edit-container .pb-block-edit-block[data-id="'+block_id+'"] .pb-item-edit-container[data-type="button"] .pb-item-block[data-id="'+pb_item_id+'"]');
                if(pb_item_block.data('new') == '1') {
                    pb_item_block.remove();
                } else {
                    pb_item_block.hide();
                    $('#modal-content-edit .pb-block-edit-container .pb-block-edit-block[data-id="'+block_id+'"] .pb-item-edit-container[data-type="button"] .pb-item-block[data-id="'+pb_item_id+'"] .form-control').each(function() {
                        if($(this).attr('type') != 'file') {
                            $(this).val(pb_item_data[$(this).attr('name')]);
                        }
                    });
                }
                $('#modal-content-edit .pb-block-edit-container .pb-block-edit-block[data-id="'+block_id+'"] .pb-block-edit-main').show();
                $('#modal-content-edit .modal-footer button').attr('data-dismiss', 'modal');
                $('#modal-content-edit .modal-footer button.btn-success').removeClass('pb-item-content-save');
                $('#modal-content-edit .modal-footer button.btn-success').addClass('pb-block-content-save');    
            }
        }
    });
    
    $('.post-builder').on('click','.pb-item-content-save',function() {
        var block_id = $('#modal-content-edit').data('block');
        if($('#modal-content-edit .pb-block-edit-container .pb-block-edit-block[data-id="'+block_id+'"] .pb-item-edit-container[data-type="button"]').length > 0) {
            var pb_item_id;
            $('#modal-content-edit .pb-block-edit-container .pb-block-edit-block[data-id="'+block_id+'"] .pb-item-edit-container[data-type="button"] .pb-item-block').each(function() {
                if($(this).is(':visible')) {
                    pb_item_id = $(this).data('id');
                }
            });
            var pb_item_block = $('#modal-content-edit .pb-block-edit-container .pb-block-edit-block[data-id="'+block_id+'"] .pb-item-edit-container[data-type="button"] .pb-item-block[data-id="'+pb_item_id+'"]');
            pb_item_block.attr('data-new','0');
            var pb_item_title = pb_item_block.find('.pb-button-input-title').val();
            var pb_item_hidden = pb_item_block.find('.pb-button-input-hide').val();
            if(pb_item_block.find('.pb-button-input-type').val() == 'custom') {
                var pb_item_link = pb_item_block.find('.pb-button-input-custom').val();
                if(pb_item_title == '') {
                    pb_item_block.find('.pb-button-input-title').val('New Button');
                    pb_item_title = 'New Button';
                }
            } else {
                var pb_item_object_id = pb_item_block.find('.pb-button-input-object-id').val();
                var pb_item_link = pb_item_block.find('.pb-button-input-object-id option[value="'+pb_item_object_id+'"]').html();
                if(pb_item_title == '') {
                    pb_item_title = pb_item_link;
                }
            }
            if($('#modal-content-edit .pb-block-edit-container .pb-block-edit-block[data-id="'+block_id+'"] .button-options tbody').length <= 0) {
                $('#modal-content-edit .pb-block-edit-container .pb-block-edit-block[data-id="'+block_id+'"] .button-options').append('<tbody></tbody>');
                pb_item_button_init_sortable();
            }
            if($('#modal-content-edit .pb-block-edit-container .pb-block-edit-block[data-id="'+block_id+'"] .button-options tbody tr[data-id="'+pb_item_id+'"]').length > 0) {
                $('#modal-content-edit .pb-block-edit-container .pb-block-edit-block[data-id="'+block_id+'"] .button-options tbody tr[data-id="'+pb_item_id+'"]').find('.pb-item-table-title').html(pb_item_title);
                $('#modal-content-edit .pb-block-edit-container .pb-block-edit-block[data-id="'+block_id+'"] .button-options tbody tr[data-id="'+pb_item_id+'"]').find('.pb-item-table-link').html(pb_item_link);
            } else {
                $('#modal-content-edit .pb-block-edit-container .pb-block-edit-block[data-id="'+block_id+'"] .button-options tbody').append('<tr data-id="'+pb_item_id+'" class="ui-sortable-handle"><td><span class="pb-item-table-title">'+pb_item_title+'</span></td><td><span class="pb-item-table-link">'+pb_item_link+'</span></td><td class="right w100"><a href="#" class="btn btn-primary btn-xs pb-item-edit-row" title="Edit Button" data-id="'+pb_item_id+'"><i class="fas fa-edit"></i> Edit</a> <a href="#" class="btn btn-danger btn-xs pb-item-remove-row" title="Remove Button"><i class="fas fa-times"></i></a></td></tr>');
                pb_item_button_sort();
            }
            $('#modal-content-edit .pb-block-edit-container .pb-block-edit-block[data-id="'+block_id+'"] .button-options tbody tr[data-id="'+pb_item_id+'"]').removeClass('inactive');
            if(pb_item_hidden == '1') {
                $('#modal-content-edit .pb-block-edit-container .pb-block-edit-block[data-id="'+block_id+'"] .button-options tbody tr[data-id="'+pb_item_id+'"]').addClass('inactive');
            }

            $('#modal-content-edit .pb-block-edit-container .pb-block-edit-block[data-id="'+block_id+'"] .pb-item-edit-container[data-type="button"] .pb-item-block').hide();
            $('#modal-content-edit .modal-footer button').attr('data-dismiss', 'modal');
            $('#modal-content-edit .pb-block-edit-container .pb-block-edit-block[data-id="'+block_id+'"] .pb-block-edit-main .pb-item-table-container[data-type="button"]').show();
            $('#modal-content-edit .pb-block-edit-container .pb-block-edit-block[data-id="'+block_id+'"] .pb-block-edit-main').show();
            $('#modal-content-edit .pb-block-edit-container .pb-block-edit-block[data-id="'+block_id+'"] .button-options').removeClass('hide');
            $('#modal-content-edit .pb-block-edit-container .pb-block-edit-block[data-id="'+block_id+'"] .pb-item-row-add').addClass('btn-xs');
            $('#modal-content-edit .modal-footer button.btn-success').removeClass('pb-item-content-save');
            $('#modal-content-edit .modal-footer button.btn-success').addClass('pb-block-content-save');
        }
    });
    
    $('.post-builder').on('change','.pb-button-input-type',function() {
        var extra_url = '';
        var ctrl = $(this);
        var pb_item_block = $(this).closest('.pb-item-block');
        var pb_item_object_id = pb_item_block.find('.pb-button-input-object-id');
        if($(this).val() == 'custom') {
            pb_item_object_id.hide(300);
            pb_item_block.find('.pb-button-input-custom').show(300);
            pb_item_block.find('.pb-button-input-title').attr('placeholder','Required - For example: Home, About, Contact Us...');
        } else {
            pb_item_object_id.show(300);
            pb_item_block.find('.pb-button-input-custom').hide(300);
            pb_item_block.find('.pb-button-input-title').attr('placeholder','Optional - For example: Home, About, Contact Us...');

            var object = $(this).val();
            $.get(ajax_rel+"Ajax=menu_item_object&Object=" + object + extra_url,function(data) {
                pb_item_object_id.html(data);
                if(pb_item_object_id.data('object-id') > 0) {
                    var ctrl_obj_id = pb_item_object_id.data('object-id');
                    pb_item_object_id.find("option[value='" + ctrl_obj_id + "']").prop('selected', true);
                }
            });
        }
        return false;
    });
    
});

function pb_button_content_edit_open(block_id) {
    $('#modal-content-edit .pb-block-edit-container .pb-block-edit-block[data-id="'+block_id+'"] .pb-item-edit-container[data-type="button"] .pb-item-block').each(function() {
        var pb_item_id = $(this).data('id');
        $(this).find('.form-control').each(function() {
            if($(this).attr('type') != 'file') {
                if(pb_item_full_data[pb_item_id] == undefined) {
                   pb_item_full_data[pb_item_id] = {};
                }
                pb_item_full_data[pb_item_id][$(this).attr('name')] = $(this).val();
            }
        });
    });
}

function pb_button_content_edit_close(block_id) {
    $('#modal-content-edit .pb-block-edit-container .pb-block-edit-block[data-id="'+block_id+'"] .pb-item-edit-container[data-type="button"] .pb-item-block').each(function() {
        var pb_item_id = $(this).data('id');
        if(pb_item_full_data[pb_item_id] != undefined) {
            $(this).find('.form-control').each(function() {
                if($(this).attr('type') != 'file') {
                    $(this).val(pb_item_full_data[pb_item_id][$(this).attr('name')]);
                }    
            });
            var pb_item_title = $('#modal-content-edit .pb-block-edit-container .pb-block-edit-block[data-id="'+block_id+'"] .pb-item-edit-container[data-type="button"] .pb-item-block[data-id="'+pb_item_id+'"]').find('.pb-button-input-title').val();
            if($('#modal-content-edit .pb-block-edit-container .pb-block-edit-block[data-id="'+block_id+'"] .pb-item-edit-container[data-type="button"] .pb-item-block[data-id="'+pb_item_id+'"]').find('.pb-button-input-type').val() == 'custom') {
                var pb_item_link = $('#modal-content-edit .pb-block-edit-container .pb-block-edit-block[data-id="'+block_id+'"] .pb-item-edit-container[data-type="button"] .pb-item-block[data-id="'+pb_item_id+'"]').find('.pb-button-input-custom').val();
            } else {
                var pb_item_object_id = $('#modal-content-edit .pb-block-edit-container .pb-block-edit-block[data-id="'+block_id+'"] .pb-item-edit-container[data-type="button"] .pb-item-block[data-id="'+pb_item_id+'"]').find('.pb-button-input-object-id').val();
                var pb_item_link = $('#modal-content-edit .pb-block-edit-container .pb-block-edit-block[data-id="'+block_id+'"] .pb-item-edit-container[data-type="button"] .pb-item-block[data-id="'+pb_item_id+'"]').find('.pb-button-input-object-id option[value="'+pb_item_object_id+'"]').html();
                if(pb_item_title == '') {
                    pb_item_title = pb_item_link;
                }
            }
            if(pb_item_title != $('#modal-content-edit .pb-block-edit-container .pb-block-edit-block[data-id="'+block_id+'"] .pb-block-edit-main .pb-item-table-container .button-options tr[data-id="'+pb_item_id+'"] .pb-item-table-title').html()) {
                $('#modal-content-edit .pb-block-edit-container .pb-block-edit-block[data-id="'+block_id+'"] .pb-block-edit-main .pb-item-table-container .button-options tr[data-id="'+pb_item_id+'"] .pb-item-table-title').html(pb_item_title);
            }
            if(pb_item_link != $('#modal-content-edit .pb-block-edit-container .pb-block-edit-block[data-id="'+block_id+'"] .pb-block-edit-main .pb-item-table-container .button-options tr[data-id="'+pb_item_id+'"] .pb-item-table-link').html()) {
                $('#modal-content-edit .pb-block-edit-container .pb-block-edit-block[data-id="'+block_id+'"] .pb-block-edit-main .pb-item-table-container .button-options tr[data-id="'+pb_item_id+'"] .pb-item-table-link').html(pb_item_link);
            }
            $('#modal-content-edit .pb-block-edit-container .pb-block-edit-block[data-id="'+block_id+'"] .pb-block-edit-main .pb-item-table-container .button-options tr[data-id="'+pb_item_id+'"]').removeClass('inactive');
            if($('#modal-content-edit .pb-block-edit-container .pb-block-edit-block[data-id="'+block_id+'"] .pb-item-edit-container[data-type="button"] .pb-item-block[data-id="'+pb_item_id+'"] .pb-item-hide').val() == '1') {
                $('#modal-content-edit .pb-block-edit-container .pb-block-edit-block[data-id="'+block_id+'"] .pb-block-edit-main .pb-item-table-container .button-options tr[data-id="'+pb_item_id+'"]').addClass('inactive');
            }
            if($('#modal-content-edit .pb-block-edit-container .pb-block-edit-block[data-id="'+block_id+'"] .pb-block-edit-main .pb-item-table-container .button-options tr[data-id="'+pb_item_id+'"]').css('display') == 'none' && $('#modal-content-edit .pb-block-edit-container .pb-block-edit-block[data-id="'+block_id+'"] .pb-item-edit-container[data-type="button"] .pb-item-block[data-id="'+pb_item_id+'"] .pb-item-input-remove').val() != '1') {
               $('#modal-content-edit .pb-block-edit-container .pb-block-edit-block[data-id="'+block_id+'"] .pb-block-edit-main .pb-item-table-container .button-options tr[data-id="'+pb_item_id+'"]').show();
            }
        } else {
            $('#modal-content-edit .pb-block-edit-container .pb-block-edit-block[data-id="'+block_id+'"] .pb-block-edit-main .pb-item-table-container .button-options tr[data-id="'+pb_item_id+'"]').remove();
            $('#modal-content-edit .pb-block-edit-container .pb-block-edit-block[data-id="'+block_id+'"] .pb-item-edit-container[data-type="button"] .pb-item-block[data-id="'+pb_item_id+'"]').remove();
        }
        if($('#modal-content-edit .pb-block-edit-container .pb-block-edit-block[data-id="'+block_id+'"] .button-options tbody tr:not(.inactive)').length <= 0) {
            $('#modal-content-edit .pb-block-edit-container .pb-block-edit-block[data-id="'+block_id+'"] .button-options').addClass('hide');
            $('#modal-content-edit .pb-block-edit-container .pb-block-edit-block[data-id="'+block_id+'"] .pb-item-row-add').removeClass('btn-xs');
        } else {
            $('#modal-content-edit .pb-block-edit-container .pb-block-edit-block[data-id="'+block_id+'"] .button-options').removeClass('hide');
            $('#modal-content-edit .pb-block-edit-container .pb-block-edit-block[data-id="'+block_id+'"] .pb-item-row-add').addClass('btn-xs');
        }
    });
    pb_item_full_data = {};
}

function pb_item_button_edit_item(id=0, block_id) {
    if(id != 0) {
        $('#modal-content-edit .pb-block-edit-container .pb-block-edit-block[data-id="'+block_id+'"] .pb-item-edit-container[data-type="button"] .pb-item-block[data-id="'+id+'"] .form-control').each(function() {
			if($(this).attr('type') != 'file') {
				pb_item_data[$(this).attr('name')] = $(this).val();
			}
		});
        $('#modal-content-edit .pb-block-edit-container .pb-block-edit-block[data-id="'+block_id+'"] .pb-block-edit-main').hide();
        $('#modal-content-edit .pb-block-edit-container .pb-block-edit-block[data-id="'+block_id+'"] .pb-item-edit-container[data-type="button"] .pb-item-block[data-id="'+id+'"]').show();
        $('#modal-content-edit .pb-block-edit-container .pb-block-edit-block[data-id="'+block_id+'"] .pb-item-edit-container[data-type="button"] .pb-item-block[data-id="'+id+'"] .pb-button-input-title').focusInput();
    } else {
        pb_item_button_new_count++;
        $.get(ajax_rel+"Ajax=post_builder&Do=add_item&block_id="+block_id+"&type=button", function(data) {
			var return_arr = JSON.parse(data);
            id = return_arr.item_id;
            $('#modal-content-edit .pb-block-edit-container .pb-block-edit-block[data-id="'+block_id+'"] .pb-item-edit-container[data-type="button"]').append(return_arr.item_edit_html);
            $('#modal-content-edit .pb-block-edit-container .pb-block-edit-block[data-id="'+block_id+'"] .pb-block-edit-main').hide();
            $('#modal-content-edit .pb-block-edit-container .pb-block-edit-block[data-id="'+block_id+'"] .pb-item-edit-container[data-type="button"] .pb-item-block[data-id="'+id+'"]').show();
            $('#modal-content-edit .pb-block-edit-container .pb-block-edit-block[data-id="'+block_id+'"] .pb-item-edit-container[data-type="button"] .pb-item-block[data-id="'+id+'"] .pb-button-input-title').focusInput();
        });
    }
    $('#modal-content-edit .modal-footer button').attr('data-dismiss', '');
    $('#modal-content-edit .modal-footer button.btn-success').removeClass('pb-block-content-save');
    $('#modal-content-edit .modal-footer button.btn-success').addClass('pb-item-content-save');
}

function pb_item_button_sort() {
    $('#modal-content-edit .pb-block-edit-container .pb-block-edit-block .pb-block-edit-main .pb-item-table-container[data-type="button"] .button-options tbody').children("tr").each(function(index) {
        $('#modal-content-edit .pb-block-edit-container .pb-block-edit-block .pb-item-edit-container[data-type="button"] .pb-item-block[data-id="'+$(this).data('id')+'"] .pb-item-sort').val(index);
    });
}

function pb_item_button_init_sortable() {
    $('#modal-content-edit .pb-block-edit-container .pb-block-edit-block .pb-block-edit-main .pb-item-table-container[data-type="button"] tbody').sortable({
        update: function(event, ui) {
            pb_item_button_sort();
        }	
    });
}
