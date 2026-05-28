//DO LOAD
var site_rel = zl_main_rel;
var ajax_rel = site_rel + "includes/page/ajax.php?";

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
			
			$.get(site_rel + "admin/index.php?Page=sale&Action=LineData&barcode=" + $(this).val(),function(data) {
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
	$(".bt-mail-receipt").click(function() {
		$('html, body').animate({
			scrollTop: 0
		}, 2000);
		$("#box-comment").slideToggle(1000);
		return false;
	});
	$('#btn_surcharge').on('click', function() {
		open_surcharge_dialog();
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
	$.get(site_rel + "admin/index.php?Page=sale&Action=LineData&sku=" + sku,function(data) {
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

//-- Price Break check
$(document).on("change",".line-item-quan",function() {
    var sku = $(this).parent().parent().find('input[name="sku"].sf-value').val();
    var quan = $(this).val();
	price_break_popup(sku, quan, true);
});
//-- Update Line with Price Break
$(document).on('click', "#modal-price-break .modal-footer .price-break-apply", function() {
    var sku = $(this).attr('data-sku');
    var price = $(this).attr('data-price');
    var price_break = $(this).attr('data-price-break');
    var row = $('input[name="sku"].sf-value[value="'+sku+'"]').parent().parent();
    var price_input = row.find('input.line-item-unit');
    price_input.val(price);
    price_input.trigger('keyup');
});

//-- Keypresses
$(document).keypress(function(e) {
    if(e.keyCode == 13 || e.keyCode == 10) {
        if($("#modal-price-break").hasClass('in')) {
            $("#modal-price-break .modal-footer .price-break-apply").trigger('click');
            return false;
        }
    } else if(e.keyCode == 27) {
        if($("#modal-price-break").hasClass('in')) {
            $('#modal-price-break').modal('hide');
            return false;
        }
    }
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
	if($("#sale-summary-shipping").length) {
		var shipping = $("#sale-summary-shipping").html().replace(/[^0-9\.]+/g,"");
		total_invoice += Number(shipping);
	}
	
	if(tax_excl) {
		var gst = total_invoice*(tax_rate/100);
		var total = gst+total_invoice;
		
		$("#sale-summary-subtotal").html(total_invoice.toFixed(2));
		$("#sale-summary-subtotal-hidden").val(total_invoice.toFixed(2));
		$("#sale-summary-gst").html(gst.toFixed(2));
		$("#sale-summary-total").html(total.toFixed(2));
		$("#sale-summary-total-hidden").val(total.toFixed(2));
	} else {
		var subtotal = total_invoice / (1+(tax_rate/100));
		var gst = total_invoice-subtotal;
		
		$("#sale-summary-subtotal").html(subtotal.toFixed(2));
		$("#sale-summary-subtotal-hidden").val(subtotal.toFixed(2));
		$("#sale-summary-gst").html(gst.toFixed(2));
		$("#sale-summary-total").html(total_invoice.toFixed(2));
		$("#sale-summary-total-hidden").val(total_invoice.toFixed(2));
		
	}
	
	return false;
}
				
function open_surcharge_dialog() {
	var surcharge_percent = prompt('Please enter surcharge percentage:', '');
	if (surcharge_percent == null || surcharge_percent == '') {
		//Canceled
	} else {
		surcharge_percent = parseFloat(surcharge_percent);
		if(isNaN(surcharge_percent)){
			alert('Please enter a number to add as a surcharge.');
		}else{
			var surcharge_val = surcharge_calc(surcharge_percent);
			InvoiceAddLineRowWithValues('', 'Surcharge', surcharge_val, '1.00', '');
			$('.line-item-unit').trigger('keyup');
		}
		
	}
}

function surcharge_calc(surcharge_percent){
	var current_total = $('#sale-summary-subtotal-hidden').val();
	console.log(current_total);
	return ((current_total*surcharge_percent)/100);
}

//Add Invoice Line Row
function InvoiceAddLineRowWithValues(sku, description, price, qty, disc) {
	var this_row = $("#row_count").val();
	this_row = (this_row*1)+1;
	var row_id = this_row;
	
	$("#line-items").append("<tr> <td class=\"sku\"><input type=\"text\" name=\"line[" + row_id + "][sku]\" value=\"" + sku  + "\"  class=\"form-control sf-input typeahead sf-input-sku input-sku\" data-populate=\"sku\" data-sf=\"sf_product_sku\" data-id=\"" + row_id + "\" autocomplete=\"off\" /><div class=\"guessbox guessbox-sku\" style=\"display:none;\"><ul></ul></div></td> <td class=\"des\"><input type=\"text\" name=\"line[" + row_id + "][description]\" value=\"" + description  + "\"  class=\"form-control input-description\" /></td> <td class=\"unit\"> <div class=\"input-group\"> <span class=\"input-group-addon\">$</span> <input type=\"text\" name=\"line[" + row_id + "][price]\" data-row=\"" + row_id + "\"  value=\"" + price  + "\" class=\"form-control input-price line-item-unit\" id=\"line-" + row_id + "-unit\" /></div> </td> <td class=\"quan\"><input type=\"text\" data-row=\"\" id=\"line-" + row_id + "-quan\" data-row=\"" + row_id + "\" name=\"line[" + row_id + "][qty]\" value=\"" + qty  + "\"  class=\"form-control line-item-quan\" /></td> <td class=\"disc\"><div class=\"input-group\"><input type=\"text\" name=\"line[" + row_id + "][disc]\" data-row=\"" + row_id + "\"  value=\"" + disc  + "\" id=\"line-" + row_id + "-disc\" class=\"form-control line-item-disc\" /><span class=\"input-group-addon\">%</span></div></td> <td class=\"linettl\"><p class=\"form-control-static\">$<span class=\"line-item-total\" id=\"line-" + row_id + "-total\">0.00</span></p></td><td class=\"action\"><a href=\"#\" class=\"line-remove opt opt-danger\"><span class=\"fas fa-times\"></span></a></td></tr> ");
	$("input[name=\"line[" + row_id + "]['sku']\"]").focus();
	$("#row_count").val(this_row);
	return false;
};

//Add Invoice Line Row
function InvoiceAddLineRow() {
	var this_row = $("#row_count").val();
	this_row = (this_row*1)+1;
	var row_id = this_row;
	$("#line-items").append("<tr> <td class=\"sku\"><input type=\"text\" name=\"line[" + row_id + "][sku]\" value=\"\"  class=\"form-control sf-input typeahead sf-input-sku input-sku\" data-populate=\"sku\" data-sf=\"sf_product_sku\" data-id=\"" + row_id + "\" autocomplete=\"off\" /><div class=\"guessbox guessbox-sku\" style=\"display:none;\"><ul></ul></div></td> <td class=\"des\"><input type=\"text\" name=\"line[" + row_id + "][description]\" value=\"\"  class=\"form-control input-description\" /></td> <td class=\"unit\"> <div class=\"input-group\"> <span class=\"input-group-addon\">$</span> <input type=\"text\" name=\"line[" + row_id + "][price]\" data-row=\"" + row_id + "\"  value=\"\" class=\"form-control input-price line-item-unit\" id=\"line-" + row_id + "-unit\" /></div> </td> <td class=\"quan\"><input type=\"text\" data-row=\"\" id=\"line-" + row_id + "-quan\" data-row=\"" + row_id + "\" name=\"line[" + row_id + "][qty]\" value=\"\"  class=\"form-control line-item-quan\" /></td> <td class=\"disc\"><div class=\"input-group\"><input type=\"text\" name=\"line[" + row_id + "][disc]\" data-row=\"" + row_id + "\"  value=\"\" id=\"line-" + row_id + "-disc\" class=\"form-control line-item-disc\" /><span class=\"input-group-addon\">%</span></div></td> <td class=\"linettl\"><p class=\"form-control-static\">$<span class=\"line-item-total\" id=\"line-" + row_id + "-total\">0.00</span></p></td><td class=\"action\"><a href=\"#\" class=\"line-remove opt opt-danger\"><span class=\"fas fa-times\"></span></a></td></tr> ");
	$("input[name=\"line[" + row_id + "]['sku']\"]").focus();
	$("#row_count").val(this_row);
	return false;
};

function price_break_popup(sku, quantity=1, show_hard=false) {
    show_hard = (show_hard?1:0);
    $.get(ajax_rel+'Ajax=price_break&Do=table_html&sku='+sku+'&quantity='+quantity+'&show_hard='+show_hard, function(data) {
        var return_arr = JSON.parse(data);
        if(return_arr['success']) {
            $('#modal-price-break .modal-body #table-container').html(return_arr['html']);
            $('#modal-price-break .modal-footer .price-break-apply').attr('data-sku',sku);
            $('#modal-price-break .modal-footer .price-break-apply').attr('data-price',return_arr['price']);
            $('#modal-price-break .modal-footer .price-break-apply').attr('data-price-break',quantity);
            $('#modal-price-break').modal('show');
            $('#modal-price-break .modal-footer .price-break-apply').focus();
        }
        if(return_arr['price_regular'].length) {
           $('input[name="sku"].sf-value[value="'+sku+'"]').parent().parent().find('.line-item-unit').val(return_arr['price_regular']);
        }
    });
}