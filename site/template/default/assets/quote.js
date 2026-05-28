//JS
$(document).ready(function() {
	$("#signature").signature();
	$(".bt-confirm").hide();
});
$(document).on('click','.bt-comment',function() {
	$('html, body').animate({
		scrollTop: 0
	}, 2000);
	$("#box-comment").slideToggle(1000);
	return false;
});
$(document).on('click','.bt-sign',function() {
	$('html, body').animate({
		scrollTop: $("#signature").offset().top
	}, 2000);
	$(".signature-box").addClass('highlight');
	$('.bt-sign').html("<span class='fas fa-check'></span> Confirm &amp; Accept");

	$(this).hide();
	$(".bt-confirm").show();
	return false;
});
$(document).on('click','.bt-clear',function() {
	$("#signature").signature('clear');
	return false;
});
$(document).on('click','.bt-confirm',function() {
	if($('#signature').signature('isEmpty')) {
		alert("Please ensure you 'sign' the quote by touching or drawing your mouse in the signature box.");
	} else {
		$("#signature_textform").val($('#signature').signature('toSVG'));
		$("#form-signature").submit();
	}
	return false;
});
$(document).on('click','.scroll-to',function() {
	var section_sc = $(this).data('scroll');
	$('html, body').animate({
		scrollTop: $(section_sc).offset().top
	}, 1000);
	return false;
});