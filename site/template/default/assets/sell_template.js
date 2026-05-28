//DO LOAD
var gst_excl = true;

$(document).ready(function() {
	
	//Update Invoice Row - COST
	/*
	$(".line-item-unit,.line-item-quan,.line-item-disc").keyup(function(e) {
		RowTotalValue($(this));
		return false;
	});*/

	
	//Selector: ADD INVOICE LINE - Adds new line to invoice
	$("#bt-add-invoice-row").click(function() {
		InvoiceAddLineRow();
		return false;
	});
	
	refresh_total();
	$("input[name='date_due'],input[name='date']").datepicker({dateFormat: "dd/mm/yy"});
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
	$.get(site_rel + "admin/index.php?Page=sale&Action=LineData&sku=" + sku,function(data) {
		if(data!=null&&data!="") {
			var split_data = data.split("#%");
			thisc.parent().nextAll('.des').find('.input-description').val(split_data[0]);
			thisc.parent().nextAll('.unit').find('.input-price').val(split_data[1]);
			thisc.parent().nextAll('.quan').find('.line-item-quan').focus();
			RowTotalValue(thisc.parent().nextAll('.quan').find('.line-item-quan'));
		}
	});
	return false;
});


//Remove Line
$(document).on('click', ".line-remove", function() {
	$(this).closest('tr').remove();
	$.get(site_rel + "admin/index.php?Page=sale&Action=LineDelete&id=" + $(this).data('line'),function(data) {
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
$(document).on("keyup",".line-item-unit,.line-item-quan,.line-item-disc,.line-item-extra",function() {
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
	/*
	var type = jq_object.data('row-type');
	if(type == 'quan') {
		quantity = jq_object.val();
		disc = $("#line-" + row_id + "-disc").val();
		cost = $("#line-" + row_id + "-unit").val();
	}
	if(type == 'disc') {
		quantity = $("#line-" + row_id + "-quan").val();
		cost = $("#line-" + row_id + "-unit").val();
		disc = jq_object.val();
	}
	if(type == 'unit') {
		cost = jq_object.val();
		disc = $("#line-" + row_id + "-disc").val();
		quantity = $("#line-" + row_id + "-quan").val();
	}*/
	
	cost = $("#line-" + row_id + "-unit").val();
	disc = $("#line-" + row_id + "-disc").val();
	quantity = $("#line-" + row_id + "-quan").val();
	number = Number(cost.replace(/[^0-9\.]+/g,""));
	disc = Number(disc.replace(/[^0-9\.]+/g,""));
	
	if(quantity<0) {
		$("#line-" + row_id + "-quan").val('1');	
	}
	if(disc>100||disc<0) {
		$("#line-" + row_id + "-disc").val('0');	
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
	if(gst_excl) {
		var gst = total_invoice*0.15;
		var total = gst+total_invoice;
		
		$("#sale-summary-subtotal").html(total_invoice.toFixed(2));
		$("#sale-summary-gst").html(gst.toFixed(2));
		$("#sale-summary-total").html(total.toFixed(2));
	} else {
		var subtotal = total_invoice/1.15;
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
	$("#line-items").append("<tr> <td class=\"sku\"><input type=\"text\" name=\"line[" + row_id + "][sku]\" value=\"\"  class=\"form-control sf-input typeahead sf-input-sku input-sku\" data-populate=\"sku\" data-sf=\"sf_product_sku\" autocomplete=\"off\" /><div class=\"guessbox guessbox-sku\" style=\"display:none;\"><ul></ul></div></td> <td class=\"des\"><input type=\"text\" name=\"line[" + row_id + "][description]\" value=\"\"  class=\"form-control input-description\" /></td> <td class=\"unit\"> <div class=\"form-group input-group\"> <span class=\"input-group-addon\">$</span> <input type=\"text\" name=\"line[" + row_id + "][price]\" data-row=\"" + row_id + "\"  value=\"\" class=\"form-control input-price line-item-unit\" id=\"line-" + row_id + "-unit\" /></div> </td> <td class=\"quan\"><input type=\"text\" data-row=\"\" id=\"line-" + row_id + "-quan\" data-row=\"" + row_id + "\" name=\"line[" + row_id + "][qty]\" value=\"\"  class=\"form-control line-item-quan\" /></td> <td class=\"disc\"><input type=\"text\" name=\"line[" + row_id + "][disc]\" data-row=\"" + row_id + "\"  value=\"\" id=\"line-" + row_id + "-disc\" class=\"form-control line-item-disc\" /></td> <td class=\"linettl\"><p class=\"form-control-static\">$<span class=\"line-item-total\" id=\"line-" + row_id + "-total\">0.00</span></p></td><td class=\"action\"><a href=\"#\" class=\"line-remove opt opt-danger\"><span class=\"fas fa-times\"></span></a></td></tr> ");
	$("input[name=\"line[" + row_id + "]['sku']\"]").focus();
	$("#row_count").val(this_row);
	return false;
};