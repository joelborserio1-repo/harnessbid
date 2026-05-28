var pb_item_accordion_new_count = 0;

$(document).ready(function() {

    $('.post-builder').on('click','div[data-type="accordion"] .pb-item-row-add',function() {
        var block_id = $('#modal-content-edit').data('block');
        pb_item_accordion_edit_item(0, block_id);
        return false;
    });
    $('.post-builder').on('click','.accordion-options .pb-item-remove-row',function() {
        var trow = $(this).closest('tr');
        var pb_item_id = trow.data('id');
        var block_id = $('#modal-content-edit').data('block');
        
        $('#modal-content-edit .pb-block-edit-container .pb-block-edit-block[data-id="'+block_id+'"] .pb-item-edit-container[data-type="accordion"] .pb-item-block[data-id="'+pb_item_id+'"] input[name="pb_post['+pb_item_id+'][remove]"]').val('1');
        trow.fadeOut(function() {
            trow.addClass('inactive');
            if($('#modal-content-edit .pb-block-edit-container .pb-block-edit-block[data-id="'+block_id+'"] .accordion-options tbody tr:not(.inactive)').length <= 0) {
                $('#modal-content-edit .pb-block-edit-container .pb-block-edit-block[data-id="'+block_id+'"] .accordion-options').addClass('hide');
                $('#modal-content-edit .pb-block-edit-container .pb-block-edit-block[data-id="'+block_id+'"] .pb-item-row-add').removeClass('btn-xs');
            }    
        });
        
        return false;
    });
    $('.post-builder').on('click','.accordion-options .pb-item-edit-row',function() {
        var block_id = $('#modal-content-edit').data('block');
        var id = $(this).data('id');
        pb_item_accordion_edit_item(id, block_id);
        return false;
    });
    $('.post-builder').on('click','#modal-content-edit .btn-close-modal',function() {
		var block_id = $('#modal-content-edit').data('block');
        if($('#modal-content-edit .pb-block-edit-container .pb-block-edit-block[data-id="'+block_id+'"] .pb-item-edit-container[data-type="accordion"]').length > 0) {
            var pb_item_id;
            $('#modal-content-edit .pb-block-edit-container .pb-block-edit-block[data-id="'+block_id+'"] .pb-item-edit-container[data-type="accordion"] .pb-item-block').each(function() {
                if($(this).is(':visible')) {
                    pb_item_id = $(this).data('id');
                }
            });
            if(pb_item_id != null) {
                var pb_item_block = $('#modal-content-edit .pb-block-edit-container .pb-block-edit-block[data-id="'+block_id+'"] .pb-item-edit-container[data-type="accordion"] .pb-item-block[data-id="'+pb_item_id+'"]');
                if(pb_item_block.data('new') == '1') {
                    pb_item_block.remove();
                } else {
                    pb_item_block.hide();
                    $('#modal-content-edit .pb-block-edit-container .pb-block-edit-block[data-id="'+block_id+'"] .pb-item-edit-container[data-type="accordion"] .pb-item-block[data-id="'+pb_item_id+'"] .form-control').each(function() {
                        if($(this).attr('type') != 'file') {
                            $(this).val(pb_item_data[$(this).attr('name')]);
                        }
                    });
                    $('#modal-content-edit .pb-block-edit-container .pb-block-edit-block[data-id="'+block_id+'"] .pb-item-edit-container[data-type="accordion"] .pb-item-block[data-id="'+pb_item_id+'"] .redactor-box .redactor-in').each(function() {
                        $(this).html(pb_item_data[$(this).next('textarea').attr('name')]);
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
        if($('#modal-content-edit .pb-block-edit-container .pb-block-edit-block[data-id="'+block_id+'"] .pb-item-edit-container[data-type="accordion"]').length > 0) {
            var pb_item_id;
            $('#modal-content-edit .pb-block-edit-container .pb-block-edit-block[data-id="'+block_id+'"] .pb-item-edit-container[data-type="accordion"] .pb-item-block').each(function() {
                if($(this).is(':visible')) {
                    pb_item_id = $(this).data('id');
                }
            });
            var pb_item_block = $('#modal-content-edit .pb-block-edit-container .pb-block-edit-block[data-id="'+block_id+'"] .pb-item-edit-container[data-type="accordion"] .pb-item-block[data-id="'+pb_item_id+'"]');
            pb_item_block.attr('data-new','0');
            var pb_item_title = pb_item_block.find('.pb-item-title').val();
            var pb_item_hidden = pb_item_block.find('.pb-item-hide').val();
            if(pb_item_title == '') {
                pb_item_block.find('.pb-item-title').val('New Item');
                pb_item_title = 'New Item';
            }
            if($('#modal-content-edit .pb-block-edit-container .pb-block-edit-block[data-id="'+block_id+'"] .accordion-options tbody').length <= 0) {
                $('#modal-content-edit .pb-block-edit-container .pb-block-edit-block[data-id="'+block_id+'"] .accordion-options').append('<tbody></tbody>');
                pb_item_accordion_init_sortable();
            }
            if($('#modal-content-edit .pb-block-edit-container .pb-block-edit-block[data-id="'+block_id+'"] .accordion-options tbody tr[data-id="'+pb_item_id+'"]').length > 0) {
                $('#modal-content-edit .pb-block-edit-container .pb-block-edit-block[data-id="'+block_id+'"] .accordion-options tbody tr[data-id="'+pb_item_id+'"]').find('.pb-item-table-title').html(pb_item_title);
            } else {
                $('#modal-content-edit .pb-block-edit-container .pb-block-edit-block[data-id="'+block_id+'"] .accordion-options tbody').append('<tr data-id="'+pb_item_id+'" class="ui-sortable-handle"><td><span class="pb-item-table-title">'+pb_item_title+'</span></td><td class="right w100"><a href="#" class="btn btn-primary btn-xs pb-item-edit-row" title="Edit Item" data-id="'+pb_item_id+'"><i class="fas fa-edit"></i> Edit</a> <a href="#" class="btn btn-danger btn-xs pb-item-remove-row" title="Remove Item"><i class="fas fa-times"></i></a></td></tr>');
                pb_item_accordion_sort();
            }
            $('#modal-content-edit .pb-block-edit-container .pb-block-edit-block[data-id="'+block_id+'"] .accordion-options tbody tr[data-id="'+pb_item_id+'"]').removeClass('inactive');
            if(pb_item_hidden == '1') {
                $('#modal-content-edit .pb-block-edit-container .pb-block-edit-block[data-id="'+block_id+'"] .accordion-options tbody tr[data-id="'+pb_item_id+'"]').addClass('inactive');
            }

            $('#modal-content-edit .pb-block-edit-container .pb-block-edit-block[data-id="'+block_id+'"] .pb-item-edit-container[data-type="accordion"] .pb-item-block').hide();
            $('#modal-content-edit .modal-footer button').attr('data-dismiss', 'modal');
            $('#modal-content-edit .pb-block-edit-container .pb-block-edit-block[data-id="'+block_id+'"] .pb-block-edit-main .pb-item-table-container[data-type="accordion"]').show();
            $('#modal-content-edit .pb-block-edit-container .pb-block-edit-block[data-id="'+block_id+'"] .pb-block-edit-main').show();
            $('#modal-content-edit .pb-block-edit-container .pb-block-edit-block[data-id="'+block_id+'"] .accordion-options').removeClass('hide');
            $('#modal-content-edit .pb-block-edit-container .pb-block-edit-block[data-id="'+block_id+'"] .pb-item-row-add').addClass('btn-xs');
            $('#modal-content-edit .modal-footer button.btn-success').removeClass('pb-item-content-save');
            $('#modal-content-edit .modal-footer button.btn-success').addClass('pb-block-content-save');
        }
    });
});

function pb_accordion_content_edit_open(block_id) {
    $('#modal-content-edit .pb-block-edit-container .pb-block-edit-block[data-id="'+block_id+'"] .pb-item-edit-container[data-type="accordion"] .pb-item-block').each(function() {
        var pb_item_id = $(this).data('id');
        $(this).find('.form-control').each(function() {
            if($(this).attr('type') != 'file') {
                if(pb_item_full_data[pb_item_id] == undefined) {
                   pb_item_full_data[pb_item_id] = {};
                }
                pb_item_full_data[pb_item_id][$(this).attr('name')] = $(this).val();
            }
        });
        $('#modal-content-edit .pb-block-edit-container .pb-block-edit-block[data-id="'+block_id+'"] .pb-item-edit-container[data-type="accordion"] .pb-item-block[data-id="'+pb_item_id+'"] .redactor-box .redactor-in').each(function() {
            pb_item_full_data[pb_item_id][$(this).next('textarea').attr('name')] = $(this).html();
        });
    });
}

function pb_accordion_content_edit_close(block_id) {
    $('#modal-content-edit .pb-block-edit-container .pb-block-edit-block[data-id="'+block_id+'"] .pb-item-edit-container[data-type="accordion"] .pb-item-block').each(function() {
        var pb_item_id = $(this).data('id');
        if(pb_item_full_data[pb_item_id] != undefined) {
            $(this).find('.form-control').each(function() {
                if($(this).attr('type') != 'file') {
                    $(this).val(pb_item_full_data[pb_item_id][$(this).attr('name')]);
                }    
            });
            $('#modal-content-edit .pb-block-edit-container .pb-block-edit-block[data-id="'+block_id+'"] .pb-item-edit-container[data-type="accordion"] .pb-item-block[data-id="'+pb_item_id+'"] .redactor-box .redactor-in').each(function() {
                $(this).html(pb_item_full_data[pb_item_id][$(this).next('textarea').attr('name')]);
            });
            if($('#modal-content-edit .pb-block-edit-container .pb-block-edit-block[data-id="'+block_id+'"] .pb-item-edit-container[data-type="accordion"] .pb-item-block[data-id="'+pb_item_id+'"] .pb-item-title').val() != $('#modal-content-edit .pb-block-edit-container .pb-block-edit-block[data-id="'+block_id+'"] .pb-block-edit-main .pb-item-table-container .accordion-options tr[data-id="'+pb_item_id+'"] .pb-item-table-title').html()) {
                $('#modal-content-edit .pb-block-edit-container .pb-block-edit-block[data-id="'+block_id+'"] .pb-block-edit-main .pb-item-table-container .accordion-options tr[data-id="'+pb_item_id+'"] .pb-item-table-title').html($('#modal-content-edit .pb-block-edit-container .pb-block-edit-block[data-id="'+block_id+'"] .pb-item-edit-container[data-type="accordion"] .pb-item-block[data-id="'+pb_item_id+'"] .pb-item-title').val());
            }
            $('#modal-content-edit .pb-block-edit-container .pb-block-edit-block[data-id="'+block_id+'"] .pb-block-edit-main .pb-item-table-container .accordion-options tr[data-id="'+pb_item_id+'"]').removeClass('inactive');
            if($('#modal-content-edit .pb-block-edit-container .pb-block-edit-block[data-id="'+block_id+'"] .pb-item-edit-container[data-type="accordion"] .pb-item-block[data-id="'+pb_item_id+'"] .pb-item-hide').val() == '1') {
                $('#modal-content-edit .pb-block-edit-container .pb-block-edit-block[data-id="'+block_id+'"] .pb-block-edit-main .pb-item-table-container .accordion-options tr[data-id="'+pb_item_id+'"]').addClass('inactive');
            }
            if($('#modal-content-edit .pb-block-edit-container .pb-block-edit-block[data-id="'+block_id+'"] .pb-block-edit-main .pb-item-table-container .accordion-options tr[data-id="'+pb_item_id+'"]').css('display') == 'none' && $('#modal-content-edit .pb-block-edit-container .pb-block-edit-block[data-id="'+block_id+'"] .pb-item-edit-container[data-type="accordion"] .pb-item-block[data-id="'+pb_item_id+'"] .pb-item-input-remove').val() != '1') {
               $('#modal-content-edit .pb-block-edit-container .pb-block-edit-block[data-id="'+block_id+'"] .pb-block-edit-main .pb-item-table-container .accordion-options tr[data-id="'+pb_item_id+'"]').show();
            }
        } else {
            $('#modal-content-edit .pb-block-edit-container .pb-block-edit-block[data-id="'+block_id+'"] .pb-block-edit-main .pb-item-table-container .accordion-options tr[data-id="'+pb_item_id+'"]').remove();
            $('#modal-content-edit .pb-block-edit-container .pb-block-edit-block[data-id="'+block_id+'"] .pb-item-edit-container[data-type="accordion"] .pb-item-block[data-id="'+pb_item_id+'"]').remove();
        }
        if($('#modal-content-edit .pb-block-edit-container .pb-block-edit-block[data-id="'+block_id+'"] .accordion-options tbody tr:not(.inactive)').length <= 0) {
            $('#modal-content-edit .pb-block-edit-container .pb-block-edit-block[data-id="'+block_id+'"] .accordion-options').addClass('hide');
            $('#modal-content-edit .pb-block-edit-container .pb-block-edit-block[data-id="'+block_id+'"] .pb-item-row-add').removeClass('btn-xs');
        } else {
            $('#modal-content-edit .pb-block-edit-container .pb-block-edit-block[data-id="'+block_id+'"] .accordion-options').removeClass('hide');
            $('#modal-content-edit .pb-block-edit-container .pb-block-edit-block[data-id="'+block_id+'"] .pb-item-row-add').addClass('btn-xs');
        }
    });
    pb_item_full_data = {};
}

function pb_item_accordion_edit_item(id=0, block_id) {
    if(id != 0) {
        $('#modal-content-edit .pb-block-edit-container .pb-block-edit-block[data-id="'+block_id+'"] .pb-item-edit-container[data-type="accordion"] .pb-item-block[data-id="'+id+'"] .form-control').each(function() {
			if($(this).attr('type') != 'file') {
				pb_item_data[$(this).attr('name')] = $(this).val();
			}
		});
        $('#modal-content-edit .pb-block-edit-container .pb-block-edit-block[data-id="'+block_id+'"] .pb-item-edit-container[data-type="accordion"] .pb-item-block[data-id="'+id+'"] .redactor-box .redactor-in').each(function() {
            pb_item_data[$(this).next('textarea').attr('name')] = $(this).html();
        });  
        $('#modal-content-edit .pb-block-edit-container .pb-block-edit-block[data-id="'+block_id+'"] .pb-block-edit-main').hide();
        $('#modal-content-edit .pb-block-edit-container .pb-block-edit-block[data-id="'+block_id+'"] .pb-item-edit-container[data-type="accordion"] .pb-item-block[data-id="'+id+'"]').show();
        $('#modal-content-edit .pb-block-edit-container .pb-block-edit-block[data-id="'+block_id+'"] .pb-item-edit-container[data-type="accordion"] .pb-item-block[data-id="'+id+'"] .pb-item-title').focusInput();
    } else {
        pb_item_accordion_new_count++;
        $.get(ajax_rel+"Ajax=post_builder&Do=add_item&block_id="+block_id+"&type=accordion", function(data) {
			var return_arr = JSON.parse(data);
            id = return_arr.item_id;
            $('#modal-content-edit .pb-block-edit-container .pb-block-edit-block[data-id="'+block_id+'"] .pb-item-edit-container[data-type="accordion"]').append(return_arr.item_edit_html);
            $('#modal-content-edit .pb-block-edit-container .pb-block-edit-block[data-id="'+block_id+'"] .pb-block-edit-main').hide();
            $('#modal-content-edit .pb-block-edit-container .pb-block-edit-block[data-id="'+block_id+'"] .pb-item-edit-container[data-type="accordion"] .pb-item-block[data-id="'+id+'"]').show();
            $('#modal-content-edit .pb-block-edit-container .pb-block-edit-block[data-id="'+block_id+'"] .pb-item-edit-container[data-type="accordion"] .pb-item-block[data-id="'+id+'"] .pb-item-title').focusInput();
        });
        
        /*var pb_item_new_row = $('#modal-content-edit .pb-block-edit-container .pb-block-edit-block[data-id="'+block_id+'"] .pb-item-new-container .pb-item-block').clone();
        id = 'new-' + pb_item_accordion_new_count;
        pb_item_new_row.find('.js-code-container').replaceWith("<script type=\"text/javascript\">"+pb_item_new_row.find('.js-code-container').html()+"</script>");
        var pb_item_this_html = pb_item_new_row.html();
        var textarea_id = 'pb-item-content-'+id;
        pb_item_this_html = pb_item_this_html.replace(/blank-htmlarea/g, textarea_id);
        pb_item_new_row.attr('data-id', id);
        pb_item_new_row.html(pb_item_this_html.replace(/\[0\]/g, "["+pb_item_accordion_new_count+"]"));
        $('#modal-content-edit .pb-block-edit-container .pb-block-edit-block[data-id="'+block_id+'"] .pb-item-edit-container[data-type="accordion"]').append(pb_item_new_row);
        $('#modal-content-edit .pb-block-edit-container .pb-block-edit-block[data-id="'+block_id+'"] .pb-item-edit-container[data-type="accordion"] .pb-item-block[data-id="'+id+'"]').show();*/
    }
    $('#modal-content-edit .modal-footer button').attr('data-dismiss', '');
    $('#modal-content-edit .modal-footer button.btn-success').removeClass('pb-block-content-save');
    $('#modal-content-edit .modal-footer button.btn-success').addClass('pb-item-content-save');
}

function pb_item_accordion_sort() {
    $('#modal-content-edit .pb-block-edit-container .pb-block-edit-block .pb-block-edit-main .pb-item-table-container[data-type="accordion"] .accordion-options tbody').children("tr").each(function(index) {
        $('#modal-content-edit .pb-block-edit-container .pb-block-edit-block .pb-item-edit-container[data-type="accordion"] .pb-item-block[data-id="'+$(this).data('id')+'"] .pb-item-sort').val(index);
    });
}

function pb_item_accordion_init_sortable() {
    $('#modal-content-edit .pb-block-edit-container .pb-block-edit-block .pb-block-edit-main .pb-item-table-container[data-type="accordion"] tbody').sortable({
        update: function(event, ui) {
            pb_item_accordion_sort();
        }	
    });
}
