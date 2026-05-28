
$(document).ready(function(e) {
    
    $('.slider-type').change(function() {
        let val = $(this).val(),
            row = $(this).closest('.row');
        if(val == 'slider') {
            row.find('div[data-config="post"]').addClass('hide');
            row.find('div[data-config="slider"]').removeClass('hide');
        } else {
            row.find('div[data-config="slider"]').addClass('hide');
            row.find('div[data-config="post"]').removeClass('hide');
        }
    });
    
});

function pb_slider_content_edit_open(block_id) {
    
}

function pb_slider_content_edit_close(block_id) {
    
}
