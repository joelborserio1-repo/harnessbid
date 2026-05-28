//DO LOAD
var site_rel = zl_main_rel;

$(document).ready(function() {
	var input_count = [];
	var row_count = $("#row_count").val();
	for(var i=1; i<=row_count; i++) {
		input_count[i] = 0;
	}
	
	$('.sf-input-sku').keyup(function() {
		var curr_line = $(this).data('id');
		var curr_diff = $(this).val().length - input_count[curr_line];
		
		if(curr_diff > 1 || curr_diff < 1) {
			
			$.get(site_rel + "admin/index.php?Page=porder&Action=LineData&barcode=" + $(this).val(),function(data) {
				split_data = jQuery.parseJSON(data);
				if(split_data.skip != true && data != $('input[name="line['+curr_line+'][sku]"]').val()) {// 
					$('input[name="line['+curr_line+'][sku]"]').val(data);
					$('input[name="line['+curr_line+'][sku]"]').trigger('blur');
				}
			});
		}
		input_count[curr_line] = $(this).val().length;
	});
	//Update Invoice Row - COST
	/*
	$(".line-item-unit,.line-item-quan,.line-item-disc").keyup(function(e) {
		RowTotalValue($(this));
		return false;
	});*/

	$('input').blur(function() {
		if(!$(this).nextAll('.guessbox').is(":hover")) {
			$('.guessbox').hide();
		}
	});
	
	//Selector: ADD INVOICE LINE - Adds new line to invoice
	$("#bt-add-invoice-row").click(function() {
		InvoiceAddLineRow();
		return false;
	});
	
	refresh_total();
	$("input[name='date_due'],input[name='date']").datepicker({dateFormat: "dd/mm/yy"});
	
	//Mail Reciept
	$(".bt-mail-reciept").click(function() {
		$('html, body').animate({
			scrollTop: 0
		}, 2000);
		$("#box-comment").slideToggle(1000);
		return false;
	});
});

//Refresh Costs
function refresh_total() {
	$("input.line-item-unit").each(function() {
		$(this).trigger("keyup");
	});
}

//Update Line
$(document).on('change', ".sf-input-sku", function() {
	var sku = $(this).val();
	var thisc = $(this);
	$.get(site_rel + "admin/index.php?Page=porder&Action=LineData&sku=" + sku,function(data) {
		split_data = jQuery.parseJSON(data);
		if(split_data.name!=null && split_data.skip!=true) {
			if(split_data.sku!=null) {
				$(this).val(split_data.sku);	
			}
			thisc.parent().nextAll('.des').find('.input-description').val(split_data.name);
			thisc.parent().nextAll('.unit').find('.input-price').val(split_data.price);
			thisc.parent().nextAll('.quan').find('.line-item-quan').focus();
			RowTotalValue(thisc.parent().nextAll('.quan').find('.line-item-quan'));
		}
	});
	return false;
});


//Remove Line
$(document).on('click', ".line-remove", function() {
	$(this).closest('tr').remove();
	$.get(site_rel + "admin/index.php?Page=porder&Action=LineDelete&id=" + $(this).data('line'),function(data) {
	});
	refresh_total();
	return false;
});

//Detect Empty Field
$(document).on("focus",".line-item-unit",function() {
	if($(this).val()=='') {
		$(this).val('0');
	}
	$(this).select();
	return false;
});
$(document).on("focus",".line-item-quan",function() {
	if($(this).val()=='') {
		$(this).val('1');	
	}
	$(this).select();
	return false;
});

//Detect Line Item Change
$(document).on("keyup",".line-item-unit,.line-item-quan,.line-item-extra",function() {
	RowTotalValue($(this));
	return false;
});

//Set Row Total
function RowTotalValue(jq_object) {
	var field_class = jq_object.attr('class');
	var cost = 0;
	var disc = 0;
	var extra = 0;
	var quantity = 0;
	var number = 0;
	var row_id = jq_object.data('row');
	
	cost = $("#line-" + row_id + "-unit").val();
	quantity = $("#line-" + row_id + "-quan").val();
	number = Number(cost.replace(/[^0-9\.]+/g,""));
	var disc = 0;
	
	if(quantity<0) {
		$("#line-" + row_id + "-quan").val('1');	
	}
	
	//Subtotal w/disc
	var total = (number*quantity);
	if(disc>0&&disc<=100) {
		var total = total-(number*quantity)*(disc/100);
	}
	
	$("#line-" + row_id + "-total").html(total.toFixed(2));
	
	//Total at Base
	var total_invoice = 0;
	$('span.line-item-total').each(function(i, obj) {
		var this_row = $(this).html();
		total_invoice += Number(this_row.replace(/[^0-9\.]+/g,""));
	});
	if($("#sale-summary-shipping").length) {
		var shipping = $("#sale-summary-shipping").html().replace(/[^0-9\.]+/g,"");
		total_invoice += Number(shipping);
	}
	
	if(tax_excl) {
		var gst = total_invoice*(tax_rate/100);
		var total = gst+total_invoice;
		
		$("#sale-summary-subtotal").html(total_invoice.toFixed(2));
		$("#sale-summary-gst").html(gst.toFixed(2));
		$("#sale-summary-total").html(total.toFixed(2));
	} else {
		var subtotal = total_invoice / (1+(tax_rate/100));
		var gst = total_invoice-subtotal;
		
		$("#sale-summary-subtotal").html(subtotal.toFixed(2));
		$("#sale-summary-gst").html(gst.toFixed(2));
		$("#sale-summary-total").html(total_invoice.toFixed(2));
	}
	
	return false;
}

//Add Invoice Line Row
function InvoiceAddLineRow() {
	var this_row = $("#row_count").val();
	this_row = (this_row*1)+1;
	var row_id = this_row;
	$("#line-items").append("<tr> <td class=\"sku\"><input type=\"text\" name=\"line[" + row_id + "][sku]\" value=\"\"  class=\"form-control sf-input typeahead sf-input-sku input-sku\" data-populate=\"sku\" data-sf=\"sf_product_sku\" data-id=\"" + row_id + "\" autocomplete=\"off\" /><div class=\"guessbox guessbox-sku\" style=\"display:none;\"><ul></ul></div></td> <td class=\"des\"><input type=\"text\" name=\"line[" + row_id + "][description]\" value=\"\"  class=\"form-control input-description\" /></td> <td class=\"unit\"> <div class=\"input-group\"> <span class=\"input-group-addon\">$</span> <input type=\"text\" name=\"line[" + row_id + "][price]\" data-row=\"" + row_id + "\"  value=\"\" class=\"form-control input-price line-item-unit\" id=\"line-" + row_id + "-unit\" /></div> </td> <td class=\"quan\"><input type=\"text\" data-row=\"\" id=\"line-" + row_id + "-quan\" data-row=\"" + row_id + "\" name=\"line[" + row_id + "][qty]\" value=\"\"  class=\"form-control line-item-quan\" /></td><td class=\"linettl\"><p class=\"form-control-static\">$<span class=\"line-item-total\" id=\"line-" + row_id + "-total\">0.00</span></p></td><td class=\"action\"><a href=\"#\" class=\"line-remove opt opt-danger\"><span class=\"fas fa-times\"></span></a></td></tr> ");
	$("input[name=\"line[" + row_id + "]['sku']\"]").focus();
	$("#row_count").val(this_row);
	return false;
};