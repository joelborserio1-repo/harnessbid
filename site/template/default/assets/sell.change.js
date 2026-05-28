var paid = 0;
var total = 0;
var change = 0;
var balance = 0;

$(document).ready(function(e) {
	
	total = total_base;
	total = round(total,1);
	
	load_val();
	
    $(".cur-add").click(function() {
		var factor = $(this).data("factor");
		//total = total-factor;
		paid = paid+factor;
		load_val();
		return false;
	});
});

$(".bt-pay-mark").click(function() {
	parent.paid_item_array('Cash',paid);
	parent.paid_set(paid,'Cash');
	parent.jQuery.fancybox.close();
	return false;
});
$(".bt-pay-full").click(function() {
	parent.paid_item_array('Cash',total);
	parent.paid_set(total,'Cash');
	parent.jQuery.fancybox.close();
	return false;
});
function round(value, precision) {
    var multiplier = Math.pow(10, precision || 0);
    return Math.round(value * multiplier) / multiplier;
}
function load_val() {
	balance = total-paid;
	if(balance<=0) {
		change = (total-paid)*-1;
		balance = 0;
	}
	if(change>0) {
		$("#box-change").addClass("panel-yellow");	
		$("#box-change").removeClass("panel-default");
		$('.bt-pay-mark').addClass('disabled');
	} else {
		$("#box-change").removeClass("panel-yellow");
		$("#box-change").addClass("panel-default");
		$('.bt-pay-mark').removeClass('disabled');
	}
	
	$("#lbl-paid").html(paid.toFixed(2));
	$("#lbl-due").html(balance.toFixed(2));
	$("#lbl-change").html(change.toFixed(2));	
	return;
}