//DO LOAD
var site_rel = zl_main_rel;
var ajax_rel = site_rel + "includes/page/ajax.php?";
var sale_discount = 0;
var pay_method_cache = null;
var lines_have_disc = false;
var sale_have_disc = false;
var global_total = 0;
var global_paid = 0;
var global_balance = 0;
var paid_array = [];
var paid_array_i = 0;

//-- Add line
$(document).on("click",".inv-add",function() {
	var id = $(this).data("id");
	var mode = $(this).data("mode");
	if(mode == 'sell'){
		sell_add_item(id);
	}
	return false;
});

//-- Change layout template
$(".layout-load").mouseup(function() {
	var tpl = $(this).data('template');
	sell_layout(tpl);
	return false;
});

//-- Method button
$(".bt-method").mouseup(function() {
	$(".bt-method").removeClass("selected");
	if(pay_method_cache!=$(this).data('val')) {
		$("#field-method").val($(this).data('val'));
		pay_method_cache = $(this).data('val');
		$(this).addClass("selected");
		
		if($(this).data("link")!=null) {
			var urlpop = $(this).data("link") + "&Amt=" + global_total;
			$.fancybox.open({
				padding : 0,
				href: urlpop,
				type: 'iframe'
			});
		}
		if(!$(this).hasClass('btn-pay-module')&&$(this).data('val')!='Cash') {
			paid_set(global_total);
			paid_item_array_clear();
			paid_item_array('Card',global_total);
		}
	} else {
		$("#field-method").val('');
		pay_method_cache = null;
	}
	return false;
});

//-- Delete item off section
/*
$(document).on("click",".item-delete",function() {
	var item_id = $(this).data('id');
	$.get(site_rel + "admin/index.php?Page=sell&Action=JSAction&Do=LayoutItemDelete&id=" + item_id,function(data) {
		sell_layout(0,1);
	});
	e.preventDefault();
	return false;
});*/

//-- Prompt Set Paid Amount
$(document).on("click",".bt-paid",function() {
	var prompt_val = prompt("Please enter the value paid:",global_paid);
	if(prompt_val) {
		paid_set(prompt_val);
	}
	return false;
});

//-- Delete line
$(document).on("click",".inv-action-delete",function() {
	$(this).closest('.inventory-item').remove();
	line_total();
});

//-- Discount
$(document).on("click",".inv-action-discount",function() {
	if(lines_have_disc) {
		alert("This sale has discounts applied to individual lines - you cannot place an overall discount in this case. Please remove the line discounts.");
	} else {
		var val = prompt("Enter the discount percentage (max 100%)",sale_discount);
		$("#inv-label-disc").html(val);
		$("#discount").val(val);
		sale_discount = val;
		if(val>0) {
			sale_have_disc = true;
		} else {
			sale_have_disc = false;
		}
		line_total();
	}
});

//-- Discount
$(document).on("click",".inv-action-disc",function() {
	if(sale_have_disc) {
		alert("This sale has a master discount applied - you cannot create individual line discounts in this case. Please remove the master discount.");
	} else {
		var cur_disc = ($(this).children('span').html())*1;
		var this_line_id = $(this).data('id');
		var val = prompt("Enter the discount percentage (max 100%)",sale_discount);
		$(this).children('span').html(val);
		$(".line-disc-" + this_line_id).val(val);
		line_total();
	}
});

//-- Add Variant
$(document).on("click",".bt-add-variant",function() {
	var parent_id = $(this).data('parent-id');
	var inp_qty = $('.qty-input').val();

	console.log("Parent ID: " + parent_id);
	
	var attr_arr = {};
	var attr_opt = {};
	var post_info = {};
	$("#modal-"+parent_id+" .select-attribute").each(function(index) {
		var f_val = $(this).val();
		var f_name = $(this).data("slug");
		if($(this).is(":radio") || $(this).is(":checkbox")) {
			if($(this).is(":checked")) {
				if($(this).is(":checkbox")) {
					if(attr_opt[f_name] == null) {
						attr_opt[f_name] = [];
					}
					attr_opt[f_name].push(f_val);
				} else {
					attr_arr[f_name] = f_val;
				}
			}
		} else {
			attr_arr[f_name] = f_val;
		}
	});
	post_info["attribute"] = attr_arr;
	post_info["attribute_option"] = attr_opt;
	$.ajax({
		url: site_rel + "admin/index.php?Page=sell&Action=VariantID&ProductID=" + parent_id,
		type: 'POST',
		data: post_info,
		success: function(add_id) { console.log('Response: ' + add_id); sell_add_item(add_id,inp_qty,true); }
	});
	
});

//-- Paid Array Append
function paid_arr_append(data) {
	//-- global format for paid array - 0 - AMT, 1 - REFERENCE, 2 - PAID T/F, 3 - METHOD_DATA, 4 - TXN INFO, 5 - MODULE NAME
	paid_array[paid_array_i] = [data.amount,data.reference,(data.paid?true:false),data.raw,data.info,data.module];
	$('.input-paid-array').val(JSON.stringify(paid_array));
	//alert(JSON.stringify(paid_array));
	paid_array_i++;
}

//-- Paid Info Clear
function paid_item_array_clear() {
	paid_array_i = 0;
	paid_array = [];
	$('.input-paid-array').val('');
}

//-- Paid Info
function paid_item_array(item,amt) {
	var pay_info = {
		'amount': amt,
		'paid': true,
		'info': item,
		'reference': item + ' Initiated',
		'raw': '',
		'module': '',
	};
	paid_arr_append(pay_info);
}

//-- Paid vals
function paid_val(paid) {
	var pval = (paid*1);
	$("#lbl-paid").html(pval.toFixed(2));
	$("#paid_amount").val(pval.toFixed(2));
}

//-- Public Set Paid
function paid_set(val,method='Card') {
	var chkval = val*1;
	if(chkval>global_total&&method!='Cash') {
		global_paid = global_total;
	} else {
		global_paid = chkval;
	}
	global_balance = global_total-global_paid;
	paid_val(global_paid);
}

//-- Public Set Paid
function paid_add(val) {
	var pay_amt = val*1;
	global_paid += pay_amt;
	if(global_total<global_paid) {
		global_paid = global_total;
	}
	global_balance = global_total-global_paid;
	paid_val(global_paid);
}

//-- Public Get Line Info
function line_get(val) {
	var object_data = jQuery.parseJSON(val);
	
	$.get(site_rel + "admin/index.php?Page=sell&Action=LineDataHTML&Type=manual&data=" + val,function(html) {
		$(".inventory-list").append(html);
		line_total();
	});
	return false;
}

//Total at Base
function line_total() {
	var total_invoice = 0;
	var disc = 0;
	var label_sub = 0;
	lines_have_disc = false;

	//$('span.inv-label-subtotal').each(function(i, obj) {
	$(".inventory-item").each(function(i,obj) {
		var this_price = $(this).find(".inv-label-subtotal").html();
		var this_quan = $(this).find(".quan").html();
		var this_disc = $(this).find(".line-disc").val();
		var this_subtotal = Number(this_price.replace(/[^0-9\.]+/g,"")) * Number(this_quan.replace(/[^0-9\.]+/g,""));
		
		total_invoice += this_subtotal-(this_subtotal * (Number(this_disc.replace(/[^0-9\.]+/g,""))/100));
		
		if((this_disc*1)>0) {
			lines_have_disc = true;
		}
	});
	
	if(tax_excl) {			
		label_sub = total_invoice;
		if(sale_discount>0) {
			disc = total_invoice*(sale_discount/100);
			total_invoice = total_invoice-disc;
		}
		
		var gst = total_invoice*(tax_rate/100);
		var total = gst+total_invoice;
		
		$("#sale-summary-subtotal").html(label_sub.toFixed(2));
		$("#sale-summary-discount").html(disc.toFixed(2));
		$("#sale-summary-gst").html(gst.toFixed(2));
		$("#sale-summary-total").html(total.toFixed(2));
		global_total = total.toFixed(2);
	} else {
		label_sub = total_invoice;
		if(sale_discount>0) {
			disc = total_invoice*(sale_discount/100);
			total_invoice = total_invoice-disc;
		}
		
		var subtotal = total_invoice / (1+(tax_rate/100));
		var gst = total_invoice-subtotal;
		
		$("#sale-summary-subtotal").html(label_sub.toFixed(2));
		$("#sale-summary-discount").html(disc.toFixed(2));
		$("#sale-summary-gst").html(gst.toFixed(2));
		$("#sale-summary-total").html(total_invoice.toFixed(2));
		global_total = total_invoice.toFixed(2);
	}
	paid_set(0);
	global_balance = global_total;
	return false;
}

//-- Update Line
$(document).on('change', ".sf-input-product", function() {
	var title = $(this).val();
	var thisc = $(this);
	$.get(site_rel + "admin/index.php?Page=sell&Action=AddItem&id=" + $('#page_id').val() + "&title=" + title,function(data) {
		//$('#prod_buttons').val(data);
	});
	return false;
});

//-- Update Line with Price Break
$(document).on('click', "#modal-price-break .modal-footer .price-break-apply", function() {
    var product = $(this).attr('data-product');
    var price = $(this).attr('data-price');
    var price_break = $(this).attr('data-price-break');
    $('#item-'+product+' .price .inv-label-subtotal').html(price);
    $('.line-price-break-'+product).val(price_break);
    line_total();
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

//-- Layout Refresh
function sell_layout(id,edit) {
	$.get(site_rel + "admin/index.php?Page=sell&Action=JSAction&Do=LayoutHTML&id=" + id + "&EditMode=" + edit,function(data) {
		$("#sell-master").html(data);
	});
	return false;
}

//-- Add Item
function sell_add_item(id,qty=1, variant=false) {
	$.get(site_rel + "admin/index.php?Page=sell&Action=LineData&Var=Product&id=" + id,function(data) {
		var check = jQuery.parseJSON(data);
		var duplicate = false;

		//console.log('Quantity: ' + qty);
		//console.log('Product: ' + id);
		
		//Duplicate check
		$(".line-quantity-" + id).each(function(index,val) {
			if($(this).val()>0) {
				duplicate = true;
				var this_qty = ($(this).val()*1);
				var new_quan = (this_qty+(qty*1));
                qty = new_quan;
				$(this).val(new_quan);
				$("#item-" + id + " .quan").html(new_quan.toFixed(2));
			}
		});
		
		if(duplicate) {
			line_total();
            price_break_popup(id,qty,variant);
		} else {
			if(check['attribute']>0) { //--no attr
				//trigger modal with extra details
				$('#modal-'+id).modal('show');
			} else { //--attr
				$.get(site_rel + "admin/index.php?Page=sell&Action=LineDataHTML&qty=" + qty + "&data=" + data,function(html) {
					$(".inventory-list").append(html);
					line_total();
                    price_break_popup(id,qty,variant);
				});
			}
		}
        
	});
    
    return;
}

//-- Add Item
function sell_add_product(id, variant=false) {
	$.get(site_rel + "admin/index.php?Page=sell&Action=LineData&Var=Product&id=" + id,function(data) {
		var check = data.split("{}");
		var duplicate = false;

		//Duplicate check
		$(".line-quantity-" + id).each(function(index,val) {
			if($(this).val()>0) {
				duplicate = true;
				var this_qty = ($(this).val()*1);
				var new_quan = this_qty+1;
                qty = new_quan;
				$(this).val(new_quan);
				$("#item-" + id + " .quan").html(new_quan.toFixed(2));
			}
		});
		
		if(duplicate) {
			line_total();
            price_break_popup(id,qty,variant);
		} else {
			if(check[3]>0) { //--no attr
				//trigger modal with extra details
			} else { //--attr
				$.get(site_rel + "admin/index.php?Page=sell&Action=LineDataHTML&data=" + data,function(html) {
					$(".inventory-list").append(html);
					line_total();
                    price_break_popup(id,qty,variant);
				});
			}
		}
        
	});
}

function price_break_popup(product_id, quantity=1, show_hard=false) {
    show_hard = (show_hard?1:0);
    $.get(ajax_rel+'Ajax=price_break&Do=table_html&id='+product_id+'&quantity='+quantity+'&show_hard='+show_hard, function(data) {
        console.log(data);
        var return_arr = JSON.parse(data);
        if(return_arr['success']) {
            $('#modal-price-break .modal-body #table-container').html(return_arr['html']);
            $('#modal-price-break .modal-footer .price-break-apply').attr('data-product',product_id);
            $('#modal-price-break .modal-footer .price-break-apply').attr('data-price',return_arr['price']);
            $('#modal-price-break .modal-footer .price-break-apply').attr('data-price-break',quantity);
            $('#modal-price-break').modal('show');
        }
    });  
}
