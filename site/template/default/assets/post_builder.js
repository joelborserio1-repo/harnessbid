//JS
var ajax_rel = zl_main_rel + "includes/page/ajax.php?";
var pb_new_item_count = 0;
var global_object_id = 0;
var pb_section_data = {};
var pb_row_data = {};
var pb_block_data = {};
var pb_item_data = {};
var pb_item_full_data = {};

$(document).ready(function(e) {
    
    setInterval(pb_keep_alive, 300000);
	
    /* -- POST BUILDER -- */
	// -- Show Post Builder, hide default editor
	$(document).on('click', '#pb-show', function() {
		$('#default-content-section').slideUp(300, function() {
			$('#pb-section').slideDown(300);
		});
		$(this).hide();
		$('#pb-hide').show();
		$('input[name="meta[post_builder]"]').val('1');
		
		return false;
	});
	
	// -- Hide Post Builder, show default editor
	$(document).on('click', '#pb-hide', function() {
		$('#pb-section').slideUp(300, function() {
			$('#default-content-section').slideDown(300);
		});
		$(this).hide();
		$('#pb-show').show();
		$('input[name="meta[post_builder]"]').val('0');
		
		return false;
	});
    /* -- POST BUILDER END -- */
	
    /* -- SECTIONS -- */
	// -- Add a new section
    $('.post-builder').on('click', '.pb-section-add', function() {
        $('#modal-structure').data('section', 0);
        $('#modal-structure').data('row', 0);
        reset_modal_structure();
        $('#modal-structure').modal('show');
		return false;
	});
	
	// -- Duplicate a section
	$('.post-builder').on('click', '.pb-section-duplicate', function() {
		var section_id = $(this).closest('.pb-section').data('id');
        var pb_post_data = {};
        var pb_dup_structure = {};
        
        pb_post_data[section_id] = {};
        $('#modal-section-edit .pb-section-edit-container .pb-section-edit-block[data-id="'+section_id+'"] .form-control').each(function() {
			if($(this).attr('type') != 'file') {
				pb_post_data[section_id][$(this).attr('name')] = $(this).val();
			}
		});
        $('.pb-section[data-id="'+section_id+'"] .pb-row-container .pb-row').each(function(rindex) {
            var row_id = $(this).data('id');
            if($('#modal-row-edit .pb-row-edit-container .pb-row-edit-block[data-id="'+row_id+'"] .pb-row-input-remove').val() == '0') {
                pb_dup_structure[rindex] = {};
                pb_dup_structure[rindex]['row_id'] = row_id;
                pb_dup_structure[rindex]['columns'] = {};
                pb_post_data[row_id] = {};
                $('#modal-row-edit .pb-row-edit-container .pb-row-edit-block[data-id="'+row_id+'"] .form-control').each(function() {
                    if($(this).attr('type') != 'file') {
                        pb_post_data[row_id][$(this).attr('name')] = $(this).val();
                    }
                });
                $('.pb-row[data-id="'+row_id+'"] .pb-container .pb-column').each(function(cindex) {
                    if($(this).find('.pb-column-input-remove').val() == '0') {
                        var column_id = $(this).data('id');
                        pb_dup_structure[rindex]['columns'][cindex] = {};
                        pb_dup_structure[rindex]['columns'][cindex]['column_id'] = column_id;
                        pb_dup_structure[rindex]['columns'][cindex]['blocks'] = {};
                        pb_post_data[column_id] = {};
                        $(this).find('.pb-column-edit-block .form-control').each(function() {
                            if($(this).attr('type') != 'file') {
                                pb_post_data[column_id][$(this).attr('name')] = $(this).val();
                            }
                        });
                        $('.pb-row[data-id="'+row_id+'"] .pb-container .pb-column[data-id="'+column_id+'"] .pb-block').each(function(bindex) {
                            var block_id = $(this).data('id');
                            if($('#modal-content-edit .pb-block-edit-container .pb-block-edit-block[data-id="'+block_id+'"] .pb-block-input-remove').val() == '0') {
                                pb_dup_structure[rindex]['columns'][cindex]['blocks'][bindex] = {};
                                pb_dup_structure[rindex]['columns'][cindex]['blocks'][bindex]['block_id'] = block_id;
                                pb_dup_structure[rindex]['columns'][cindex]['blocks'][bindex]['items'] = {};
                                pb_post_data[block_id] = {};
                                $('#modal-content-edit .pb-block-edit-container .pb-block-edit-block[data-id="'+block_id+'"] .pb-block-edit-main .form-control').each(function() {
                                    if($(this).attr('type') != 'file') {
                                        pb_post_data[block_id][$(this).attr('name')] = $(this).val();
                                    }
                                });
                                if($('#modal-content-edit .pb-block-edit-container .pb-block-edit-block[data-id="'+block_id+'"] .pb-block-edit-main .pb-item-table-container').length > 0) {
                                    $('#modal-content-edit .pb-block-edit-container .pb-block-edit-block[data-id="'+block_id+'"] .pb-block-edit-main .pb-item-table-container tbody tr').each(function(iindex) {
                                        var item_id = $(this).data('id');
                                        if($('#modal-content-edit .pb-block-edit-container .pb-block-edit-block[data-id="'+block_id+'"] .pb-item-edit-container .pb-item-block[data-id="'+item_id+'"] .pb-item-input-remove').val() == '0') {
                                            pb_dup_structure[rindex]['columns'][cindex]['blocks'][bindex]['items'][iindex] = {};
                                            pb_dup_structure[rindex]['columns'][cindex]['blocks'][bindex]['items'][iindex]['item_id'] = item_id;
                                            pb_post_data[item_id] = {};
                                            $('#modal-content-edit .pb-block-edit-container .pb-block-edit-block[data-id="'+block_id+'"] .pb-item-edit-container .pb-item-block[data-id="'+item_id+'"] .form-control').each(function() {
                                                if($(this).attr('type') != 'file') {
                                                    pb_post_data[item_id][$(this).attr('name')] = $(this).val();
                                                }
                                            });
                                        }
                                    });
                                }               
                            }
                        });
                    }
                }); 
            }
        });
        
        var post_arr = {};
        post_arr['section_id'] = section_id;
        post_arr['post_data'] = JSON.stringify(pb_post_data);
        post_arr['child_structure'] = JSON.stringify(pb_dup_structure);
        $.ajax({
            url: ajax_rel+"Ajax=post_builder&Do=duplicate_section",
            type: 'POST',
            data: post_arr,
            success: function(data) {
                var return_arr = JSON.parse(data);
                if(return_arr.html != null) {
                    $('.pb-section[data-id="'+section_id+'"]').after(return_arr.html);
                    $('.post-builder #modal-section-edit .pb-section-edit-container').append(return_arr.section_edit_html);
                    if(return_arr.row_edit_html != null) {
                        $('.post-builder #modal-row-edit .pb-row-edit-container').append(return_arr.row_edit_html);
                        if(return_arr.block_edit_html != null) {
                            $('.post-builder #modal-content-edit .pb-block-edit-container').append(return_arr.block_edit_html);
                        }
                    }
					pb_section_init_sortable();
					pb_row_init_sortable();
                	pb_block_init_sortable();
                }
            }
        });
        
		return false;
	});
	
	// -- Remove a section
	$('.post-builder').on('click', '.pb-section-remove', function() {
		var co = confirm("Are you sure you want to remove this section?");
		if(co) {
			var section = $(this).closest('.pb-section');
            var section_id = section.data('id');
            $('.pb-section-edit-block[data-id="'+section_id+'"] input[name="pb_post['+section_id+'][remove]"]').val('1');
            section.fadeOut(function() {
                section.appendTo('.post-builder #pb-trash');
            });
		}
		return false;
	});
    
    // -- Structure modal tab change
	$('.post-builder').on('click', '.pb-modal-section-tab', function() {
		var tab = $(this).data('tab');
		$('#modal-structure .pb-modal-section-tab-block').each(function() {
            $(this).addClass('hide');
        });
        $('#modal-structure .pb-modal-section-tabs').children().removeClass('active');
        $('#modal-structure .pb-modal-section-tab-block[data-tab=\"'+tab+'\"]').removeClass('hide');
        $(this).closest('li').addClass('active');
        $(this).blur();
		return false;
	});
    
    // -- Open popup for editng a sections config
	$('.post-builder').on('click', '.pb-section-configuration', function() {
		var section_id = $(this).closest('.pb-section').data('id');
		$('#modal-section-edit').data('section', section_id);
        $('#modal-section-edit .pb-section-edit-container .pb-section-edit-block[data-id="'+section_id+'"] .form-control').each(function() {
			if($(this).attr('type') != 'file') {
				pb_section_data[$(this).attr('name')] = $(this).val();
			}
		});
        $('#modal-section-edit .pb-section-edit-container .pb-section-edit-block').hide();
        $('#modal-section-edit .pb-section-edit-container .pb-section-edit-block[data-id="'+section_id+'"]').show();
        $('#modal-section-edit').modal('show');
        global_object_id = section_id;
		
		return false;
	});
    
    // -- Remove a section image
	$('.post-builder').on('click', '.pb-section-image-delete', function() {
        var section_id = $('#modal-section-edit').data('section');
        $('#input-image-file-'+section_id).val('');
        $('.pb-section-edit-block[data-id="'+section_id+'"] .section-image-settings').addClass('hide');
        $('.pb-section-edit-block[data-id="'+section_id+'"] .image-container .image').addClass('hide');
        $('.pb-section-edit-block[data-id="'+section_id+'"] #image-upl-success').addClass('hide');
		return false;
	});
    
    // -- On section modal open
    $("#modal-section-edit").on('shown.bs.modal', function() {
        var section_id = $("#modal-section-edit").data('section');
        $('#modal-section-edit .pb-section-edit-container .pb-section-edit-block[data-id="'+section_id+'"]').attr('data-save','0');
        $('#modal-section-edit .pb-section-edit-container .pb-section-edit-block[data-id="'+section_id+'"] .pb-section-title').focusInput();
    });
    
    // -- On section modal close
    $("#modal-section-edit").on('hidden.bs.modal', function() {
        var section_id = $("#modal-section-edit").data('section');
        if($('#modal-section-edit .pb-section-edit-container .pb-section-edit-block[data-id="'+section_id+'"]').attr('data-save') == '0') {
            if(pb_section_data['pb_post['+section_id+'][meta][image_file]'] != '' && $('#input-image-file-'+section_id).val() != '' && pb_section_data['pb_post['+section_id+'][meta][image_file]'] != $('#input-image-file-'+section_id).val()) {
                var img_src = $('.pb-section-edit-block[data-id="'+section_id+'"] .image-container .image img').attr('src');
                $('.pb-section-edit-block[data-id="'+section_id+'"] .image-container .image img').attr('src',img_src.replace($('#input-image-file-'+section_id).val(), pb_section_data['pb_post['+section_id+'][meta][image_file]']));
            }
            $('#modal-section-edit .pb-section-edit-container .pb-section-edit-block[data-id="'+section_id+'"] .form-control').each(function() {
                if($(this).attr('type') != 'file') {
                    $(this).val(pb_section_data[$(this).attr('name')]);
                }
            });
            if(($('#input-image-file-'+section_id).val() != '' && $('.pb-section-edit-block[data-id="'+section_id+'"] .section-image-settings').hasClass('hide')) || 
               ($('#input-image-file-'+section_id).val() == '' && !$('.pb-section-edit-block[data-id="'+section_id+'"] .section-image-settings').hasClass('hide'))) {
                $('.pb-section-edit-block[data-id="'+section_id+'"] .section-image-settings').toggleClass('hide');
                $('.pb-section-edit-block[data-id="'+section_id+'"] .image-container .image').toggleClass('hide');
                $('.pb-section-edit-block[data-id="'+section_id+'"] #image-upl-success').toggleClass('hide');
            }
        } else {
            $('#modal-section-edit .pb-section-edit-container .pb-section-edit-block[data-id="'+section_id+'"]').attr('data-save','0');
        }
    });
    
    // -- Save section content
	$('.post-builder').on('click', '.section-content-save', function() {
        var section_id = $("#modal-section-edit").data('section');
        $('#modal-section-edit .pb-section-edit-container .pb-section-edit-block[data-id="'+section_id+'"]').attr('data-save','1');
        var section_title = $('#modal-section-edit .pb-section-edit-container .pb-section-edit-block[data-id="'+section_id+'"] .pb-section-title').val();
        var section_hide = $('#modal-section-edit .pb-section-edit-container .pb-section-edit-block[data-id="'+section_id+'"] .pb-section-hide').val();
        if(section_title != pb_section_data.title) {
           $('.pb-section[data-id="'+section_id+'"] .pb-container-section-title').html(section_title);
        }
        if(section_hide == '1' && !$('.pb-section[data-id="'+section_id+'"]').hasClass('inactive')) {
            $('.pb-section[data-id="'+section_id+'"]').addClass('inactive');
        } else if(section_hide == '0' && $('.pb-section[data-id="'+section_id+'"]').hasClass('inactive')) {
            $('.pb-section[data-id="'+section_id+'"]').removeClass('inactive');
        }
	});
    
    // -- Toggle section contents visibility
    $('.post-builder').on('click', '.pb-section .pb-container-header .pb-container-toggle', function() {
        var pb_section_header = $(this).closest('.pb-container-header');
        var pb_row_container = pb_section_header.next('.pb-row-container');
        if(pb_row_container.is(':visible')) {
            pb_row_container.slideUp(function() {
                pb_section_header.addClass('minimised');
            });
        } else {
            pb_row_container.slideDown();
            pb_section_header.removeClass('minimised');
        }
        return false;
    });
    /* -- SECTIONS END -- */
    
    /* -- ROWS -- */
    // -- Add a new row
    $('.post-builder').on('click', '.pb-row-add', function() {
        $('#modal-structure').data('section', $(this).closest('.pb-section').data('id'));
        $('#modal-structure').data('row', 0);
        reset_modal_structure();
        $('#modal-structure').modal('show');
		return false;
	});
    
    // -- Remove a row
	$('.post-builder').on('click', '.pb-row-remove', function() {
		var co = confirm("Are you sure you want to remove this row?");
		if(co) {
			var row = $(this).closest('.pb-row');
            var row_id = row.data('id');
            $('.pb-row-edit-block[data-id="'+row_id+'"] input[name="pb_post['+row_id+'][remove]"]').val('1');
            row.fadeOut(function() {
                row.appendTo('.post-builder #pb-trash');
            });
		}
		return false;
	});
    
    // -- Duplicate a row
	$('.post-builder').on('click', '.pb-row-duplicate', function() {
		var row_id = $(this).closest('.pb-row').data('id');
        var pb_post_data = {};
        var pb_dup_structure = {};
        
        pb_post_data[row_id] = {};
        $('#modal-row-edit .pb-row-edit-container .pb-row-edit-block[data-id="'+row_id+'"] .form-control').each(function() {
			if($(this).attr('type') != 'file') {
				pb_post_data[row_id][$(this).attr('name')] = $(this).val();
			}
		});
        $('.pb-row[data-id="'+row_id+'"] .pb-container .pb-column').each(function(cindex) {
            if($(this).find('.pb-column-input-remove').val() == '0') {
                var column_id = $(this).data('id');
                pb_dup_structure[cindex] = {};
                pb_dup_structure[cindex]['column_id'] = column_id;
                pb_dup_structure[cindex]['blocks'] = {};
                pb_post_data[column_id] = {};
                $(this).find('.pb-column-edit-block .form-control').each(function() {
                    if($(this).attr('type') != 'file') {
                        pb_post_data[column_id][$(this).attr('name')] = $(this).val();
                    }
                });
                $('.pb-row[data-id="'+row_id+'"] .pb-container .pb-column[data-id="'+column_id+'"] .pb-block').each(function(bindex) {
                    var block_id = $(this).data('id');
                    if($('#modal-content-edit .pb-block-edit-container .pb-block-edit-block[data-id="'+block_id+'"] .pb-block-input-remove').val() == '0') {
                        pb_dup_structure[cindex]['blocks'][bindex] = {};
                        pb_dup_structure[cindex]['blocks'][bindex]['block_id'] = block_id;
                        pb_dup_structure[cindex]['blocks'][bindex]['items'] = {};
                        pb_post_data[block_id] = {};
                        $('#modal-content-edit .pb-block-edit-container .pb-block-edit-block[data-id="'+block_id+'"] .pb-block-edit-main .form-control').each(function() {
                            if($(this).attr('type') != 'file') {
                                pb_post_data[block_id][$(this).attr('name')] = $(this).val();
                            }
                        });
                        if($('#modal-content-edit .pb-block-edit-container .pb-block-edit-block[data-id="'+block_id+'"] .pb-block-edit-main .pb-item-table-container').length > 0) {
                            $('#modal-content-edit .pb-block-edit-container .pb-block-edit-block[data-id="'+block_id+'"] .pb-block-edit-main .pb-item-table-container tbody tr').each(function(iindex) {
                                var item_id = $(this).data('id');
                                if($('#modal-content-edit .pb-block-edit-container .pb-block-edit-block[data-id="'+block_id+'"] .pb-item-edit-container .pb-item-block[data-id="'+item_id+'"] .pb-item-input-remove').val() == '0') {
                                    pb_dup_structure[cindex]['blocks'][bindex]['items'][iindex] = {};
                                    pb_dup_structure[cindex]['blocks'][bindex]['items'][iindex]['item_id'] = item_id;
                                    pb_post_data[item_id] = {};
                                    $('#modal-content-edit .pb-block-edit-container .pb-block-edit-block[data-id="'+block_id+'"] .pb-item-edit-container .pb-item-block[data-id="'+item_id+'"] .form-control').each(function() {
                                        if($(this).attr('type') != 'file') {
                                            pb_post_data[item_id][$(this).attr('name')] = $(this).val();
                                        }
                                    });
                                }
                            });
                        }               
                    }
                });
            }
        });
        
        var post_arr = {};
        post_arr['row_id'] = row_id;
        post_arr['post_data'] = JSON.stringify(pb_post_data);
        post_arr['child_structure'] = JSON.stringify(pb_dup_structure);
        $.ajax({
            url: ajax_rel+"Ajax=post_builder&Do=duplicate_row",
            type: 'POST',
            data: post_arr,
            success: function(data) {
                var return_arr = JSON.parse(data);
                if(return_arr.html != null) {
                    $('.pb-row[data-id="'+row_id+'"]').after(return_arr.html);
                    $('.post-builder #modal-row-edit .pb-row-edit-container').append(return_arr.row_edit_html);
                    if(return_arr.block_edit_html != null) {
                        $('.post-builder #modal-content-edit .pb-block-edit-container').append(return_arr.block_edit_html);
                    }
					pb_row_init_sortable();
                	pb_block_init_sortable();
                }
            }
        });
        
		return false;
	});
    
    // -- Open popup for editing a rows config
	$('.post-builder').on('click', '.pb-row-configuration', function() {
		var row_id = $(this).closest('.pb-row').data('id');
		$('#modal-row-edit').data('row', row_id);
        $('#modal-row-edit .pb-row-edit-container .pb-row-edit-block[data-id="'+row_id+'"] .form-control').each(function() {
			if($(this).attr('type') != 'file') {
				pb_row_data[$(this).attr('name')] = $(this).val();
			}
		});
        $('#modal-row-edit .pb-row-edit-container .pb-row-edit-block').hide();
        $('#modal-row-edit .pb-row-edit-container .pb-row-edit-block[data-id="'+row_id+'"]').show();
        $('#modal-row-edit').modal('show');
        global_object_id = row_id;
		
		return false;
	});
    
    // -- On row modal open
    $("#modal-row-edit").on('shown.bs.modal', function() {
        var row_id = $("#modal-row-edit").data('row');
        $('#modal-row-edit .pb-row-edit-container .pb-row-edit-block[data-id="'+row_id+'"]').attr('data-save','0');
        $('#modal-row-edit .pb-row-edit-container .pb-row-edit-block[data-id="'+row_id+'"] .pb-row-title').focusInput();
    });
    
    // -- On row modal close
    $("#modal-row-edit").on('hidden.bs.modal', function() {
        var row_id = $("#modal-row-edit").data('row');
        if($('#modal-row-edit .pb-row-edit-container .pb-row-edit-block[data-id="'+row_id+'"]').attr('data-save') == '0') {
            $('#modal-row-edit .pb-row-edit-container .pb-row-edit-block[data-id="'+row_id+'"] .form-control').each(function() {
                if($(this).attr('type') != 'file') {
                    $(this).val(pb_row_data[$(this).attr('name')]);
                }
            });
        } else {
            $('#modal-row-edit .pb-row-edit-container .pb-row-edit-block[data-id="'+row_id+'"]').attr('data-save','0');
        }
    });
    
    // -- Save row content
	$('.post-builder').on('click', '.row-content-save', function() {
		var row_id = $("#modal-row-edit").data('row');
        $('#modal-row-edit .pb-row-edit-container .pb-row-edit-block[data-id="'+row_id+'"]').attr('data-save','1');
        var row_title = $('#modal-row-edit .pb-row-edit-container .pb-row-edit-block[data-id="'+row_id+'"] .pb-row-title').val();
        var row_hide = $('#modal-row-edit .pb-row-edit-container .pb-row-edit-block[data-id="'+row_id+'"] .pb-row-hide').val();
        if(row_title != pb_row_data.title) {
           $('.pb-row[data-id="'+row_id+'"] .pb-container-row-title').html(row_title);
        }
        if(row_hide == '1' && !$('.pb-row[data-id="'+row_id+'"]').hasClass('inactive')) {
            $('.pb-row[data-id="'+row_id+'"]').addClass('inactive');
        } else if(row_hide == '0' && $('.pb-row[data-id="'+row_id+'"]').hasClass('inactive')) {
            $('.pb-row[data-id="'+row_id+'"]').removeClass('inactive');
        }
	});
    
    // -- Open popup for setting a rows structure
	$('.post-builder').on('click', '.pb-row-structure', function() {
		var row_id = $(this).closest('.pb-row').data('id');
        var section_id = $(this).closest('.pb-section').data('id');
        var curr_layout = $(this).data('layout');
        var curr_tab = $(this).data('tab');
		$('#modal-structure').data('row', row_id);
        $('#modal-structure').data('section', row_id);
        reset_modal_structure();
		$('#modal-structure').modal('show');
        $('#modal-structure .pb-modal-section-tab-block .layout-section[data-layout=\"'+curr_layout+'\"]').addClass('selected');
        $('#modal-structure .pb-modal-section-tabs .pb-modal-section-tab[data-tab=\"'+curr_tab+'\"]').trigger('click');
		return false;
	});
    
    // -- Select a rows structure
    $('.post-builder').on('click', '.layout-section', function() {
		var layout = String($(this).data('layout'));
		var section_id = $('#modal-structure').data('section');
        var row_id = $('#modal-structure').data('row');
        var parent_id = $('input[name="post_builder_id"]').val();
        var tab_id = $(this).closest('.pb-modal-section-tab-block').data('tab');
        
        if(section_id <= 0 || row_id <= 0) {
            $.get(ajax_rel+"Ajax=post_builder&Do=new_row_layout&section_id="+section_id+"&row_id="+row_id+"&layout="+layout+"&parent_id="+parent_id, function(data) {
                var return_arr = JSON.parse(data);
                if(section_id <= 0) {
                    $('.post-builder .pb-section-container').append('<div class="row pb-section" data-id="'+return_arr.section_id+'">'+return_arr.html+'</div>');
                    $('.post-builder #modal-section-edit .pb-section-edit-container').append(return_arr.section_edit_html);
                    $('.post-builder #modal-row-edit .pb-row-edit-container').append(return_arr.row_edit_html);
					pb_section_init_sortable();
                } else {
                    $('.pb-section[data-id="'+section_id+'"] .pb-row-container .pb-row-add').before('<div class="pb-row" data-id="'+return_arr.row_id+'">'+return_arr.html+'</div>');
                    $('.post-builder #modal-row-edit .pb-row-edit-container').append(return_arr.row_edit_html);
                }
				pb_row_init_sortable();
                pb_block_init_sortable();
            });
        } else {
            var post_arr = {};
            post_arr['columns'] = {};
            $('.pb-row[data-id="'+row_id+'"] .pb-container .pb-column').each(function() {
                var pb_column_id = $(this).data('id');
                post_arr['columns'][pb_column_id] = {};
                $('.pb-row[data-id="'+row_id+'"] .pb-container .pb-column[data-id="'+pb_column_id+'"] .pb-block').each(function(index) {
                    post_arr['columns'][pb_column_id][index] = $(this).data('id');
                });
            });
            post_arr['row_id'] = row_id;
            post_arr['layout'] = layout;
            post_arr['columns'] = JSON.stringify(post_arr['columns']);
            $.ajax({
                url: ajax_rel+"Ajax=post_builder&Do=update_row_layout",
                type: 'POST',
                data: post_arr,
                traditional: true,
                success: function(data) { 
                    var return_arr = JSON.parse(data);
                    $('.pb-row[data-id="'+row_id+'"] .pb-container').addClass('old');
                    $('.pb-row[data-id="'+row_id+'"] .pb-container.old').hide();
                    $('.pb-row[data-id="'+row_id+'"] .pb-container.old').after(return_arr.html);
                    $('.pb-row[data-id="'+row_id+'"] .pb-container.old .pb-block').each(function() {
                        var pb_block_id = $(this).data('id');
                        if($('.pb-row[data-id="'+row_id+'"] .pb-container.new .pb-block[data-id="'+pb_block_id+'"]').length) {
                           $('.pb-row[data-id="'+row_id+'"] .pb-container.new .pb-block[data-id="'+pb_block_id+'"]').replaceWith($(this));
                        }
                    });
                    $('.pb-row[data-id="'+row_id+'"] .pb-container.old .pb-column').each(function() {
                        var pb_column_id = $(this).data('id');
                        if($('.pb-row[data-id="'+row_id+'"] .pb-container.new .pb-column[data-id="'+pb_column_id+'"]').length) {
                            $(this).remove();
                        } else {
                            $(this).find('input[name="pb_post['+pb_column_id+'][remove]"]').val('1');
                        }
                    });
                    $('.pb-row[data-id="'+row_id+'"] .pb-container.old').toggleClass('pb-container');
                    $('.pb-row[data-id="'+row_id+'"] .pb-container.new').removeClass('new');
                    $('.pb-row[data-id="'+row_id+'"] .pb-container-header .pb-row-structure').data('layout',layout);
                    $('.pb-row[data-id="'+row_id+'"] .pb-container-header .pb-row-structure').data('tab',tab_id);
                    $('.post-builder #modal-row-edit .pb-row-edit-container .pb-row-edit-block[data-id="'+row_id+'"] .pb-row-layout').val(layout);
                    pb_block_init_sortable();
                }
            });  
        }
        $('#modal-structure').modal('hide');
	});
    
    // -- Toggle row contents visibility
    $('.post-builder').on('click', '.pb-row .pb-container-header .pb-container-toggle', function() {
        var pb_row_header = $(this).closest('.pb-container-header');
        var pb_col_container = pb_row_header.next('.pb-column-container');
        if(pb_col_container.is(':visible')) {
            pb_col_container.slideUp(function() {
                pb_row_header.addClass('minimised');
            });
        } else {
            pb_col_container.slideDown();
            pb_row_header.removeClass('minimised');
        }
        return false;
    });
    /* -- ROWS END -- */
	
    /* -- BLOCKS -- */
	// -- Open popup for setting a blocks content type
	$('.post-builder').on('click', '.pb-block-insert', function() {
		var column_id = $(this).closest('.pb-column').data('id');
		$('#modal-content-type').data('column', column_id);
		$('#modal-content-type').modal('show');
		return false;
	});
	
	// -- Select a blocks content type, then do edit popup
	$('.post-builder').on('click', '.content-type-section', function() {
		var type = $(this).data('type');
		var column_id = $('#modal-content-type').data('column');
		$.get(ajax_rel+"Ajax=post_builder&Do=add_block&column_id="+column_id+"&type="+type, function(data) {
			var return_arr = JSON.parse(data);
			$('.post-builder .pb-column[data-id="'+column_id+'"] .pb-block-insert').before(return_arr.html);
            $('.post-builder #modal-content-edit .pb-block-edit-container').append(return_arr.block_edit_html);
            $('#modal-content-type').modal('hide');
			$('.pb-block[data-id="'+return_arr.block_id+'"] .pb-block-content .block-content-edit').trigger('click');
            pb_block_sort();
		});
	});
    
    // -- Open popup for editng a blocks content
	$('.post-builder').on('click', '.block-content-edit', function() {
		var block_id = $(this).closest('.pb-block').data('id');
		var type = $(this).closest('.pb-block-content').data('type');
		$('#modal-content-edit').data('block', block_id);
        pb_block_data = {};
		$('#modal-content-edit .pb-block-edit-container .pb-block-edit-block[data-id="'+block_id+'"] .pb-block-edit-main .form-control').each(function() {
			if($(this).attr('type') != 'file') {
				pb_block_data[$(this).attr('name')] = $(this).val();
			}
		});
        
        //-- If block has the content edit open function then run it
        var block_function = "pb_"+$('#modal-content-edit .pb-block-edit-container .pb-block-edit-block[data-id="'+block_id+'"]').attr('data-type')+"_content_edit_open";
        if(typeof window[block_function] === 'function') {
            window[block_function](block_id);
        }
        $('#modal-content-edit .pb-block-edit-container .pb-block-edit-block').hide();
        $('#modal-content-edit .pb-block-edit-container .pb-block-edit-block[data-id="'+block_id+'"]').show();
        $('#modal-content-edit').modal('show');
        global_object_id = block_id;
		
		return false;
	});
	
	// -- Duplicate a block
	$('.post-builder').on('click', '.block-content-duplicate', function() {
		var block_id = $(this).closest('.pb-block').data('id');
        var pb_post_data = {};
        var pb_dup_structure = {};
        
        pb_post_data[block_id] = {};
        $('#modal-content-edit .pb-block-edit-container .pb-block-edit-block[data-id="'+block_id+'"] .pb-block-edit-main .form-control').each(function() {
			if($(this).attr('type') != 'file') {
				pb_post_data[block_id][$(this).attr('name')] = $(this).val();
			}
		});
        if($('#modal-content-edit .pb-block-edit-container .pb-block-edit-block[data-id="'+block_id+'"] .pb-block-edit-main .pb-item-table-container').length > 0) {
            $('#modal-content-edit .pb-block-edit-container .pb-block-edit-block[data-id="'+block_id+'"] .pb-block-edit-main .pb-item-table-container tbody tr').each(function(iindex) {
                var item_id = $(this).data('id');
                if($('#modal-content-edit .pb-block-edit-container .pb-block-edit-block[data-id="'+block_id+'"] .pb-item-edit-container .pb-item-block[data-id="'+item_id+'"] .pb-item-input-remove').val() == '0') {
                    pb_dup_structure[iindex] = {};
                    pb_dup_structure[iindex]['item_id'] = item_id;
                    pb_post_data[item_id] = {};
                    $('#modal-content-edit .pb-block-edit-container .pb-block-edit-block[data-id="'+block_id+'"] .pb-item-edit-container .pb-item-block[data-id="'+item_id+'"] .form-control').each(function() {
                        if($(this).attr('type') != 'file') {
                            pb_post_data[item_id][$(this).attr('name')] = $(this).val();
                        }
                    });
                }
            });
        }
        
        var post_arr = {};
        post_arr['block_id'] = block_id;
        post_arr['post_data'] = JSON.stringify(pb_post_data);
        post_arr['child_structure'] = JSON.stringify(pb_dup_structure);
        $.ajax({
            url: ajax_rel+"Ajax=post_builder&Do=duplicate_block",
            type: 'POST',
            data: post_arr,
            traditional: true,
            success: function(data) { 
                var return_arr = JSON.parse(data);
                if(return_arr.html != null) {
                    $('.pb-block[data-id="'+block_id+'"]').after(return_arr.html);
                    $('.post-builder #modal-content-edit .pb-block-edit-container').append(return_arr.block_edit_html);
                    pb_block_init_sortable();
                }
            }
        });  
        
		return false;
	});
    
    // -- Remove a block
	$('.post-builder').on('click', '.block-content-remove', function() {
        var co = confirm("Are you sure you want to remove this content block?");
		if(co) {
			var block = $(this).closest('.pb-block');
            var block_id = block.data('id');
            $('.pb-block-edit-block[data-id="'+block_id+'"] input[name="pb_post['+block_id+'][remove]"]').val('1');
            block.fadeOut(function() {
                block.appendTo('.post-builder #pb-trash');
            });
		}
		
		return false;
	});
    
    // -- On content modal open
    $("#modal-content-edit").on('shown.bs.modal', function() {
        var block_id = $("#modal-content-edit").data('block');
        $('#modal-content-edit .pb-block-edit-container .pb-block-edit-block[data-id="'+block_id+'"]').attr('data-save','0');
        $('#modal-content-edit .pb-block-edit-container .pb-block-edit-block[data-id="'+block_id+'"] .pb-block-title').focusInput();
        if(!$('body').hasClass('modal-open')) {
            $('body').addClass('modal-open');
        }
    });
    
    // -- On content modal close
    $("#modal-content-edit").on('hidden.bs.modal', function() {
        var block_id = $("#modal-content-edit").data('block');
        if($('#modal-content-edit .pb-block-edit-container .pb-block-edit-block[data-id="'+block_id+'"]').attr('data-save') == '0') {
            $('#modal-content-edit .pb-block-edit-container .pb-block-edit-block[data-id="'+block_id+'"] .pb-block-edit-main .form-control').each(function() {
                if($(this).attr('type') != 'file') {
                    $(this).val(pb_block_data[$(this).attr('name')]);
                }
            });
            //-- If block has the content edit close function then run it
            var block_function = "pb_"+$('#modal-content-edit .pb-block-edit-container .pb-block-edit-block[data-id="'+block_id+'"]').attr('data-type')+"_content_edit_close";
            if(typeof window[block_function] === 'function') {
                window[block_function](block_id);
            }
        } else {
            $('#modal-content-edit .pb-block-edit-container .pb-block-edit-block[data-id="'+block_id+'"]').attr('data-save','0');
        }
    });
	
	// -- Save block content
	$('.post-builder').on('click', '.pb-block-content-save', function() {
		var block_id = $("#modal-content-edit").data('block');
        $('#modal-content-edit .pb-block-edit-container .pb-block-edit-block[data-id="'+block_id+'"]').attr('data-save','1');
        var block_title = $('#modal-content-edit .pb-block-edit-container .pb-block-edit-block[data-id="'+block_id+'"] .pb-block-title').val();
        var block_hide = $('#modal-content-edit .pb-block-edit-container .pb-block-edit-block[data-id="'+block_id+'"] .pb-block-hide').val();
        if(block_title != pb_block_data.title) {
           $('.pb-block[data-id="'+block_id+'"] .label-post-title').html(block_title);
        }
        if(block_hide == '1' && !$('.pb-block[data-id="'+block_id+'"]').hasClass('inactive')) {
            $('.pb-block[data-id="'+block_id+'"]').addClass('inactive');
        } else if(block_hide == '0' && $('.pb-block[data-id="'+block_id+'"]').hasClass('inactive')) {
            $('.pb-block[data-id="'+block_id+'"]').removeClass('inactive');
        }
        
        //-- If block has the content edit save function then run it
        var block_function = "pb_"+$('#modal-content-edit .pb-block-edit-container .pb-block-edit-block[data-id="'+block_id+'"]').attr('data-type')+"_content_edit_save";
        if(typeof window[block_function] === 'function') {
            window[block_function](block_id);
        }
	});
    /* -- BLOCKS END -- */
    
    // -- Keypress triggers
    $('#modal-content-edit, #modal-row-edit, #modal-section-edit').keypress(function(e) {
        if(e.keyCode == 13 || e.keyCode == 10) {
            if(!$(document.activeElement).hasClass('redactor-in') && !$(document.activeElement).hasClass('redactor-source-open')) {
                $(this).find(".modal-footer .btn-save-modal").trigger('click');
                return false;
            }
        } else if(e.keyCode == 27) {
            $('.post-builder .modal').modal('hide');
            return false;
        }
    });
	
    pb_section_init_sortable();
	pb_row_init_sortable();
    pb_block_init_sortable();
	
});

function pb_section_sort() {
    $('.post-builder .pb-section-container').children(".pb-section").each(function(index) {
        var section_id = $(this).data('id');
        $('.post-builder #modal-section-edit .pb-section-edit-container .pb-section-edit-block[data-id=\"'+section_id+'\"] .pb-section-sort').val(index);
    });
}

function pb_row_sort() {
    $('.post-builder .pb-section-container').children(".pb-section").each(function(index) {
        var section_id = $(this).data('id');
        $(this).find(".pb-row").each(function(index) {
            var row_id = $(this).data('id');
            $('.post-builder #modal-row-edit .pb-row-edit-container .pb-row-edit-block[data-id=\"'+row_id+'\"] .pb-row-sort').val(index);
            $('.post-builder #modal-row-edit .pb-row-edit-container .pb-row-edit-block[data-id=\"'+row_id+'\"] .pb-row-input-parent-id').val(section_id);
        });    
    });
}

function pb_block_sort() {
    $('.post-builder .pb-section .pb-row').find(".pb-column").each(function(index) {
        var column_id = $(this).data('id');
        $(this).find(".pb-block").each(function(index) {
            var block_id = $(this).data('id');
            $('.post-builder #modal-content-edit .pb-block-edit-container .pb-block-edit-block[data-id=\"'+block_id+'\"] .pb-block-sort').val(index);
            $('.post-builder #modal-content-edit .pb-block-edit-container .pb-block-edit-block[data-id=\"'+block_id+'\"] .pb-block-input-parent-id').val(column_id);
        });
    });
}

function pb_section_init_sortable() {
    $(".pb-section-container").sortable({
        cancel: '.pb-section .pb-container-header-left .pb-container-options, .pb-section .pb-container-header-right, .pb-section .pb-container-header-left .pb-container-toggle',
        handle: ".pb-container-header",
		update: function(event, ui) {
            pb_section_sort();
		}	
	});
    pb_section_sort();
}

function pb_row_init_sortable() {
    $(".pb-row-container").sortable({
        cancel: '.pb-row-add, .pb-row .pb-container-header-left .pb-container-options, .pb-row .pb-container-header-right, .pb-row .pb-container-header-left .pb-container-toggle',
        items : '.pb-row',
        handle: ".pb-container-header",
        connectWith: '.pb-row-container',
        dropOnEmpty: true,
		update: function(event, ui) {
            pb_row_sort();
		},
        change: function(event, ui) {
            $('.pb-row-add').removeClass('hide');
            var prev = ui.placeholder.prev();
            if(prev.hasClass('pb-row-add')) {
                prev.addClass('hide');
            }
        },
        stop: function(event, ui) {
            $('.pb-row-add').removeClass('hide');
            var prev = ui.item.prev();
            if(prev.hasClass('pb-row-add')) {
                prev.closest('.pb-row-container').append(prev);
            }
		},
	});
    pb_row_sort();
}

function pb_block_init_sortable() {
    $(".pb-column").sortable({
        cancel: '.pb-block-insert, .pb-block .content-options-right, .pb-block .content-options-left',
		items: '.pb-block',
        connectWith: '.pb-column',
        dropOnEmpty: true,
		update: function(event, ui) {
            pb_block_sort();
		},
        change: function(event, ui) {
            $('.pb-block-insert').removeClass('hide');
            var prev = ui.placeholder.prev();
            if(prev.hasClass('pb-block-insert')) {
                prev.addClass('hide');
            }
        },
        stop: function(event, ui) {
            $('.pb-block-insert').removeClass('hide');
            var prev = ui.item.prev();
            if(prev.hasClass('pb-block-insert')) {
                prev.closest('.pb-column').append(prev);
            }
		},
	});
    pb_block_sort();
}

function reset_modal_structure() {
    $('#modal-structure .pb-modal-section-tab-block .layout-section').removeClass('selected');
    $('#modal-structure .pb-modal-section-tabs .pb-modal-section-tab[data-tab=\"0\"]').trigger('click');
}

function section_image_upload(id, image) {
    if(image !== undefined) {
        $('.pb-section-edit-block[data-id="'+id+'"] #input-image-file-'+id).val(image);
        $('.pb-section-edit-block[data-id="'+id+'"] .section-image-settings').removeClass('hide');
        $('.pb-section-edit-block[data-id="'+id+'"] .image-container .image').removeClass('hide');
        $('.pb-section-edit-block[data-id="'+id+'"] #image-upl-success').removeClass('hide');
        $('.pb-section-edit-block[data-id="'+id+'"] .image-upl-wrapper .uploadifive-queue .close').trigger('click');
        $('.pb-section-edit-block[data-id="'+id+'"] .image-upl-wrapper #upl_image_file-'+id).uploadifive('clearQueue');
        var img_path = $('.pb-section-edit-block[data-id="'+id+'"] .image-container .image').data('path');
        $('.pb-section-edit-block[data-id="'+id+'"] .image-container .image img').attr('src',img_path+image+"?"+Math.floor((Math.random() * 100) + 1));
    }
}

function pb_keep_alive() {
    var post_arr = {};
    $.ajax({
        url: ajax_rel+"Ajax=post_builder&Do=keep_login",
        type: 'POST',
        data: post_arr,
        success: function(data) { 
            if(data !== '1') {
               document.location.href = site_rel;
            }
        },
        error: function(data) { console.log('error: '+data); }
    });
}
