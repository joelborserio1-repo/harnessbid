//JS
var site_rel = zl_main_rel;

$(document).ready(function(e) {
	$(document).on('click',".confirm",function() {
		var co = confirm("Are you sure? This action is irreversable.");
		if(co) {
			return true;
		} else {
			return false;
		}
	});
	$(document).on('click',".confirm-prompt",function() {
		var msg = $(this).data("msg");
		if(msg==null) {
			msg = "Are you sure? This action is irreversable.";
		}
		var co = confirm(msg);
		if(co) {
			return true;
		} else {
			return false;
		}
	});
	$('.scroll-to').click(function() {
		var container = $(this).data("scroll");
		$('html, body').animate({
			scrollTop: $(container).offset().top
		}, 2000);
		return false;
	});
	$(".panel-checkbox-action-box").hide();
	$("input.action").click(function() {
		panel_cbox_toggle();
	});
	$(".bt-sidebar-hide").click(function() {
		if($(".sidebar").is(":hidden")) {
			$(".sidebar").show();
			$("#page-wrapper").removeClass('nosidebar');
			$.get(site_rel + "admin/index.php?Page=setting&Action=SetSidebar&Toggle=0");
			$(this).html("<i class=\"fal fa-eye-slash\"></i> Hide Sidebar");
		} else {
			$(".sidebar").hide();
			$("#page-wrapper").addClass('nosidebar');
			$.get(site_rel + "admin/index.php?Page=setting&Action=SetSidebar&Toggle=1");
			$(this).html("<i class=\"fal fa-eye\"></i> Show Sidebar");
		}
		return false;
	});
	$("a.popup").fancybox({
		maxWidth	: 800,
		maxHeight	: 600,
		fitToView	: false,
		width		: '70%',
		height		: '70%',
		autoSize	: false,
		closeClick	: false,
		openEffect	: 'none',
		closeEffect	: 'none'
	});
	$("a.popup-help").fancybox({
		maxWidth	: 800,
		maxHeight	: 800,
		fitToView	: false,
		width		: '70%',
		height		: '90%',
		autoSize	: false,
		closeClick	: false,
		openEffect	: 'none',
		closeEffect	: 'none',
		padding		: 0
	});
    
    $(document).on('click',".quick-select",function() {
        select_text($(this).attr('id'));
    });
    
});

/*Panel Toggle Box*/
function panel_cbox_toggle() {
	var count = 0;
	$("input.action").each(function() {
		if($(this).is(":checked")) {
			count++;	
		}
	});
	if(count>0) {
		$(".panel-checkbox-action-box").slideDown(500);	
	} else {
		$(".panel-checkbox-action-box").slideUp(500);
	}
}

/*Refresh File View*/
function file_preview(ele) {
	$("#postsubmit").trigger('click');
}

/* Quick select text */
function select_text(containerid) {
    if (document.selection) {
        var range = document.body.createTextRange();
        range.moveToElementText(document.getElementById(containerid));
        range.select();
    } else if (window.getSelection) {
        var range = document.createRange();
        range.selectNode(document.getElementById(containerid));
        window.getSelection().addRange(range);
    }
}

/* Set the focus at the end of an inputs value */
function focusInput(jquery_obj) {
    var input_val = jquery_obj.val();
    jquery_obj.val('').focus().val(input_val);
}
$.fn.focusInput = function() { 
    var input_val = this.val();
    this.val('').focus().val(input_val);
    return this;
}
