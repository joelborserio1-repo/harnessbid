<?php

//-- Member Breadcrumbs
function member_breadcrumb() {
	global $zulu;
	$item[] = "<div class=\"breadcrumb\"><a href=\"".FE_rel."members/\">My Account</a></div>";
	foreach($zulu->template->breadcrumb as $l) {
		$item[] = "<div class=\"breadcrumb\"><a href=\"{$l['link']}\">{$l['label']}</a></div>";
	}
	return "<div class=\"breadcrumbs breadcrumbs-members\">".implode("<div class=\"breadcrumb-divider\"><span class='far fa-chevron-right'></span></div>",$item)."</div>";
}

//-- Generic Breadcrumbs
function generic_breadcrumb() {
	global $zulu;

	foreach($zulu->template->breadcrumb as $l) {
		$item[] = "<div class=\"breadcrumb\"><a href=\"{$l['link']}\">{$l['label']}</a></div>";
	}
    return "<div class=\"breadcrumbs\">".implode("<div class=\"breadcrumb-divider\"><span class='far fa-chevron-right'></span></div>",$item)."</div>";
}

//-- Reset Checkout
function checkout_reset($config=[]) {
	global $class_sale;
	unset($_SESSION['CHECKOUT'],$_SESSION['SALE_TOKEN'],$_SESSION['Pay_Now']);
	$class_sale->cart_dump($config);
}

//-- Check if the cart is empty
function cart_empty_check() {
	global $zulu,$class_sale;
	$cart_data = $class_sale->cart_data(array('client_id'=>$_SESSION['user']['id'],'session'=>session_id()));
	if(count($cart_data) <= 0) {
		checkout_reset();
		$zulu->notification_set("You've removed everything from you basket!",1);
		header("Location: ".$zulu->front_link(FE_rel."checkout/basket/"));
		exit;
	}
}

function cart_update() {
	global $zulu,$class_sale,$class_product;

	$cart_data = $class_sale->cart_data(array('client_id'=>$_SESSION['user']['id'],'session'=>session_id()));
    $quantity_arr = [];
	foreach($cart_data as $cart_row) {
		$quantity = $cart_row['quantity'];
		$prod_row = $class_product->product_data(['id'=>$cart_row['product_id']]);
        if($prod_row['parent_id'] > 0) {
            $break_disable = $class_product->product_meta($prod_row['id'],'price_break_disable')['value'];
            $break_disable_parent = $class_product->product_meta($prod_row['id'],'price_break_disable_parent')['value'];
            $break_data = $class_product->price_break_data(['product_id'=>$prod_row['id']]);
            if(($break_disable || count($break_data) <= 0) && !$break_disable_parent) {
                if($quantity_arr[$prod_row['parent_id']] > 0) {
                    $quantity = $quantity_arr[$prod_row['parent_id']];
                } else {
                    foreach($cart_data as $cart_row2) {
                        if($cart_row2['id'] == $cart_row['id']) continue;
                        $prod_row2 = $class_product->product_data(['id'=>$cart_row2['product_id']]);
                        if($prod_row2['parent_id'] == $prod_row['parent_id']) {
                            $break_disable2 = $class_product->product_meta($prod_row2['id'],'price_break_disable')['value'];
                            $break_disable_parent2 = $class_product->product_meta($prod_row2['id'],'price_break_disable_parent')['value'];
                            $break_data2 = $class_product->price_break_data(['product_id'=>$prod_row2['id']]);
                            if(($break_disable2 || count($break_data2) <= 0) && !$break_disable_parent2) {
                                $quantity += $cart_row2['quantity'];
                                $quantity_arr[$prod_row['parent_id']] = $quantity;
                            }
                        }
                    }
                }
            }
        }

        $price_data = $class_product->price($cart_row['product_id'], ['quantity'=>$quantity]);
        if($price_data['price'] != $cart_row['price']) {
            $class_sale->cart_edit($cart_row['id'],['price'=>$price_data['price']]);
            if($cart_row['sale_line_id'] > 0) {
                $line_row = $class_sale->sale_line_data(['id'=>$cart_row['sale_line_id']]);
                $class_sale->sale_line_edit($line_row['id'],['quantity'=>$cart_row['quantity'],'price'=>$price_data['price'],'discount'=>$line_row['discount'],'extra'=>$line_row['extra']]);
            }
        }
	}

    return;
}

function checkout_summary($config=[]) {
	global $class_sale,$class_setting,$class_product,$zulu,$class_book,$class_client,$class_renew, $class_module, $COUPON_enabled, $form_edit;

	if($config['checkout']) {
		$is_checkout = true;
		$sale_data = $class_sale->sale_data(['token'=>$_SESSION['CHECKOUT']['TOKEN']]);
		$sale_meta = $zulu->meta_array($class_sale->sale_meta($sale_data['id']));
	}

	$cart_data = $class_sale->cart_data(array('client_id'=>$_SESSION['user']['id'],'session'=>session_id(),'sort'=>'title ASC'));
	$cart_count = count($cart_data);
	$is_tangible = $simple_view = false;
    $delivery_options = $class_module->delivery_options();

    if((isset($config['simple']) && $config['simple']) || (isset($config['popup']) && $config['popup'])) {
        $simple_view = true;
    }

	if($_SESSION['CHECKOUT']['COUPON']!=NULL) {
		$coupon_row = $class_sale->coupon_data(['code'=>$_SESSION['CHECKOUT']['COUPON']]);
		$has_coupon = true;
		$allowed = $class_sale->coupon_product_count_allowed($coupon_row['id'],$coupon_row['discount_object_id'],$_SESSION['user']['id'],true);
		$counter = 1;
	}

	//-- Action: input_html
	if($_POST['action']=='input_html') {
		foreach($_POST['input_label'] as $cart_id=>$values) {
			foreach($values as $qty_id=>$qty_val) {
				if(trim($qty_val)==NULL) {
					$zulu->notification_set("All labels require a value.",2);
					$die = true;
					break;
				}
				$input_html[$cart_id][$qty_id] = $qty_val;
			}
		}

		if(!$die) {
			foreach($input_html as $key=>$val) {
				$cart_data = $class_sale->cart_data(['id'=>$key]);
				$custom = unserialize($cart_data['custom']);
				$custom['input_label'] = $val;
			}

			$class_sale->cart_edit($key,['custom'=>serialize($custom)]);
			$zulu->notification_set("Labels were saved.");
			header("Location: ".$zulu->front_link(true)."#row-".$cart_id);
			exit;
		}
	}

	//-- Load Cart
	foreach($cart_data as $cart_row) {

		$cid = $cart_row['id'];
		$pid = $cart_row['product_id'];
		$product = $class_product->product_data(['id'=>$pid]);
		$title = stripslashes($cart_row['title']);

		//Item Values
		$item_quantity = $cart_row['quantity'];
		$item_price = $cart_row['price'];
		$item_tax = $cart_row['tax'];
		$item_subtotal = $item_price*$item_quantity;

		$custom = unserialize($cart_row['custom']);
		foreach($custom['attribute_option'] as $attr_slug=>$attr_opts) {
			foreach($attr_opts as $attr_opt) {
				$item_subtotal += $attr_opt['price'];
			}
		}

		//Totals
		$cart_total += $item_subtotal;

		//Sub
		if($product['sku']!=NULL) {
			$sub[] = $product['sku'];
		}

		//Coupon
		if($has_coupon) {
			$coupon_pid = $pid;

			$p_check_arr = [$coupon_pid];
			if($class_product->has_children($coupon_pid)) {
				foreach($class_product->vars->children_array as $child) {
					$p_check_arr[] = $child['id'];
				}
			} elseif($product['type_variant'] == '2') {
				$p_check_arr[] = $product['parent_id'];
			}

			if(($counter <= $allowed || $allowed == -1) && in_array($coupon_row['discount_object_id'], $p_check_arr)) {
				$temp_quan = $item_quantity;
				if(($counter + $temp_quan) > $allowed && $allowed != -1) {
					$temp_quan = ($counter + $temp_quan) - $allowed;
				}
				$coupon_price = $class_sale->coupon_price($coupon_row['id'],$coupon_pid,$temp_quan,$item_price,['client_id'=>$_SESSION['user']['id']]);
				$discount += $coupon_price['total'];
				$counter += $temp_quan;
			}
		}

		if(!isset($product_url)) {
			$product_url = $class_product->product_url($pid);
		}
		$images = $class_product->image_data($pid,['object_id'=>$prod_meta['object_id'],'object'=>$prod_meta['object']]);
		$image = $zulu->thumb(($images['main']!=''?$images['main']:FE_crm.FE_path.FE_tpl."images/placeholder-product.png"),"w=300&h=300&zc=1&bg=ffffff");

		$prod_meta = $zulu->meta_array($class_product->product_meta($pid));
		if($product['parent_id']>0&&$product['type_variant']==2) {
			$prod_parent_meta = $zulu->meta_array($class_product->product_meta($product['parent_id']));
		}

		//Digital?
		if(!isset($prod_meta['digital'])||$prod_meta['digital']=='0') {
			$is_tangible = true;
		}

		//Has input field?
		$row_form_extra = [];
		$input_field = ($prod_meta['input_label']!=NULL?$prod_meta['input_label']:$prod_parent_meta['input_label']);
		if($input_field != NULL) {
			$input_field_label = $input_field;
			if(!isset($_POST['input_label'][$cart_row['id']])) {
				foreach($custom['input_label'] as $qty_id=>$val) {
					$_POST['input_label'][$cart_row['id']][$qty_id] = $val;
				}
			}
			if($cart_row['quantity']>1) {
				$head_title = "<h4>Please enter a '".$input_field."'</h4>";
			}
			for($i=1;$i<=$cart_row['quantity'];$i++) {
				if($cart_row['quantity']>1) {
					$input_field_label = "Label #".$i;
				}
				$row_form_extra[] = "<div class=\"field\"><label>{$input_field_label} <em>*</em></label><input type=\"text\" name=\"input_label[".$cart_row['id']."][".$i."]\" value=\"".$_POST['input_label'][$cart_row['id']][$i]."\" placeholder=\"Required...\" /></div>";
			}
			$row_form_extra[] = "<div class=\"field\"><label>&nbsp;</label><input type=\"submit\" value=\"Save\" /></div>";
		}
		if(count($row_form_extra)>0) {
			$extra_html .= "<div class=\"input-labels\">{$head_title}<form method=\"post\" action=\"\"><div class=\"form-block\">".implode("",$row_form_extra)."</div><input type=\"hidden\" name=\"action\" value=\"input_html\" /></form></div>";
		}

		//-- Quantity Lock
		$qty_adjust = ($prod_meta['quantity_group_stop']<=0||!isset($prod_meta['quantity_group_stop'])?true:false);
		if($custom['quantity_lock']>0) {
			$qty_adjust = false;
		}

		//-- HTML Render
		$product_HTML .= "
		<div class=\"cart-row\">
			<div class=\"coltable vmiddle\">
				<div class=\"col col-image\">
					".(!$is_checkout?"<a href=\"".$product_url."\">":null)."<img src=\"{$image}\" alt=\"".$title." image\" />".(!$is_checkout?"</a>":null)."
				</div>
				<div class=\"col col-info\">
					<h4 class=\"title\">".(!$is_checkout?"<a href=\"".$product_url."\">":null).$title.(!$is_checkout?"</a>":null)."</h4>
					<p class=\"sub\">".implode(" ",$sub)."</p>
					".$extra_html."
				</div>
				".(!$simple_view&&!$is_checkout&&$qty_adjust?"
                <div class=\"col col-quantity\">
                    <div class=\"cart-controls\">
                        <div class=\"input-group justify-content-center\">
                            <div class=\"input-group-prepend input-group-btn\">
                                <button type=\"button\" class=\"quantity-min\"><span class=\"far fa-minus\"></span></button>
                            </div>
                            ".$form_edit->input_html('number', 'quantity['.$cart_row['id'].']', $cart_row['quantity'], ['class'=>['qty-adjust','text-center'],'custom'=>['data-id'=>$cart_row['id'],'min'=>'1']])."
                            <div class=\"input-group-append input-group-btn\">
                                <button type=\"button\" class=\"quantity-add\"><span class=\"far fa-plus\"></span></button>
                            </div>
                        </div>
					</div>
                </div>
                ":NULL)."
				<div class=\"col col-price\">
					<p class=\"price\">
						".($item_quantity>1?"
						<span class=\"price-qty\">x".$item_quantity." at ".LOCALE_currency.number_format($item_price,2)." ea</span>
						":null)."
                        ".LOCALE_currency.number_format($item_subtotal,2)."
                    </p>
				</div>
				".(!$simple_view&&!$is_checkout?"
                <div class=\"col col-remove\">
					<a class=\"red\" href=\"".$zulu->front_link(true,['query'=>['Action'=>'Remove',(!$is_checkout?"BasketID":"id")=>$cart_row['id']]])."\">Remove</a>
				</div>
                ":NULL)."
        	</div>
        </div>";
		unset($extra_html,$linked,$product_url,$sub,$p_check_arr);
	}

	$subtotal = $cart_total;
    $cart_total += $sale_meta['ship_price'];

	if($has_coupon > 0) {
		if($coupon_row['discount_object_id'] == 0) {
			if($coupon_row['discount_type'] == 'percent') {
				$discount = $cart_total * ($coupon_row['discount_amount'] / 100);
				$discount_amount = $coupon_row['discount_amount'];
			} elseif($coupon_row['discount_type'] == 'fixed') {
	            if($coupon_row['type'] == 'voucher') {
	                $discount = $coupon_row['discount_remain'];
	            } else {
	                $discount = $coupon_row['discount_amount'];
	            }
				$discount_amount = $discount;
			}
		} else {
            $discount_amount = $discount;
        }
	}

	$checkout_url = $zulu->front_link(FE_rel.'checkout/');
    if($discount > $cart_total) {
        $discount = $cart_total;
    }
	$cart_total -= $discount;
	$cart_summary = $class_sale->payment_summary($cart_total,['sale_id'=>$sale_data['id']]);

    if(isset($class_setting->data['ws_shop_chk_mode'])) {
        $checkout_mode = $class_setting->data['ws_shop_chk_mode'];
    } else {
        $checkout_mode = 'both';
    }
	if($_SESSION['user']['id'] > 0 || $checkout_mode == 'nomem') {
        $checkout_buttons = "<a href=\"".$zulu->front_link(FE_rel.'checkout/')."\" class=\"button bt-block\"><span class=\"fas fa-shopping-cart\"></span> Checkout</a>";
    } elseif($checkout_mode == 'mem') {
        $checkout_buttons = "<a href=\"".FE_rel."members/login.php?return=".urlencode($checkout_url)."\" class=\"button bt-block\"><span class=\"fas fa-sign-in\"></span> Sign In</a>
        <a href=\"".FE_rel."members/register.php?return=".urlencode($checkout_url)."\" class=\"button bt-block\"><span class=\"far fa-pencil\"></span> Create an Account</a>";
    } else {
        $checkout_buttons = "<a href=\"".$checkout_url."\" class=\"button bt-block\"><span class=\"fas fa-shopping-cart\"></span> Guest Checkout</a>
        <a href=\"".FE_rel."members/login.php?return=".urlencode($checkout_url)."\" class=\"button bt-block\"><span class=\"fas fa-sign-in\"></span> Sign In</a>";
    }

	$show_shipping = false;
    if($is_tangible && $is_checkout && $delivery_options['ship'] && (!$sale_meta['delivery_method'] || $sale_meta['delivery_method'] == 'ship')) {
        $show_shipping = true;
    }

    $product_html = "<div class=\"cart-wrap\">
        ".$product_HTML."
    </div>";

	$coupon_html = "<div class=\"form-block single\">
        ".$zulu->notification('',1,['tag'=>'coupon'])."
        <div class=\"field\">
            <div class='field-inset-wrapper".($has_coupon?' has-content':null)."'>
                <label>Voucher or Discount Code</label>
                ".$form_edit->input_html('input','coupon',$coupon->code,['id'=>'coupon','placeholder'=>'Voucher or Discount Code'])."
                ".$form_edit->input_html('submit','coupon_apply','Apply',['id'=>'coupon-apply','class'=>['bt-block'],'custom'=>['disabled'=>'disabled']])."
            </div>
        </div>
    </div>";

	$total_html = "<dl class='dl-horizontal dt-left dd-right cart-pricing'>
        <dt class='subtotal'>Subtotal</dt>
        <dd class='subtotal'>".(!$is_checkout?"<span class='currency'>".LOCALE_currency_code."</span> ":null).LOCALE_currency."<span id='price-subtotal'>".$zulu->dollar($subtotal, true)."</span></dd>

        ".($is_checkout?"
        ".($has_coupon?"
        <dt class=\"discount-code\">Discount Code</dt>
        <dd class=\"discount-code\">
            <b>".$coupon_row['code']."</b>
            <a class=\"remove red\" href=\"#\" id=\"unlink-coupon\" title='Remove discount code'>".$zulu->icon('times')."</a>
        </dd>
        <dt class=\"discount\">Discount</dt>
        <dd class=\"discount\">".LOCALE_currency."<span id='price-discount' data-type='".$coupon_row['discount_type']."' data-amount='".$discount_amount."'>".number_format($discount,2)."</span></dd>":NULL)."

        ".($show_shipping?"
        <dt class=\"shipping\">Shipping</dt>
        <dd class=\"shipping\">".((isset($sale_meta['ship_price'])&&$sale_meta['ship_price']!=='')||$sale_meta['checkout_step']>1?LOCALE_currency."<span id='price-ship'>".$zulu->dollar($sale_meta['ship_price'], true)."</span>":"<span class='grey'>Calculated at next step</span>")."</dd>":NULL)."

        ".(!$class_setting->data['tax_disable']&&!$class_setting->data['tax_method']?"
        <dt class=\"tax\">".$class_setting->data['tax_label']."</dt>
        <dd class=\"tax\">".LOCALE_currency."<span id='price-tax'>".number_format($cart_summary['tax'],2)."</span></dd>":NULL)."

        <dt class=\"total\">Total</dt>
        <dd class=\"total\"><span class='currency'>".LOCALE_currency_code."</span> <b>".LOCALE_currency."<span id='price-total'>".number_format($cart_summary['total'],2)."</span></b></dd>

        ".(!$class_setting->data['tax_disable']&&$class_setting->data['tax_method']?"
        <dt class=\"tax\"></dt>
        <dd class=\"tax\">includes ".$class_setting->data['tax_label']." of ".LOCALE_currency."<span id='price-tax'>".number_format($cart_summary['tax'],2)."</span></dd>":NULL)."
        ":null)."
    </dl>";

	$html = $product_html;
    if($is_checkout && $COUPON_enabled) {
        $html .= $coupon_html;
    }

    $module_html = '';
	if(!$is_checkout && !$simple_view) {
		$module_extra = $class_module->basket_extra(['sale_total'=>$cart_summary['total']]);
		if($module_extra['count'] > 0) {
			$module_html = "<div class='module-pricing'>
				<div class=\"price-extra\">
	                ".$module_extra['html']."
	            </div>
			</div>";
	        $html .= $module_html;
		}
	}

	if(SHOP_active) {
        $button_html = $checkout_buttons;
    } else {
        $button_html = "<span class=\"fas fa-ban\"></span> Our checkout is currently unavailable.";
        if($class_setting->data['ws_shop_chk_dis_msg'] != NULL) {
            $button_html .= $class_setting->data['ws_shop_chk_dis_msg'];
        } else {
            $button_html .= "Please check back soon or contact us for further details.";
        }
    }

	if(!$is_checkout && !$simple_view) {
        $html .= "<div class=\"coltable payment-summary style-background style-border vmiddle\">
            <div class=\"col col-opt\">
                ".$button_html."
            </div>
            <div class=\"col col-sum text-right\">
                ".$total_html."
            </div>
        </div>".$module_html;
    } else {
        $html .= $total_html;
    }

	if(!$is_checkout) {
        $zulu->template->jquery_code[] = "
        let update_timeout;

        $('.quantity-min').click(function() {
            let parent = $(this).closest('.input-group'),
                product = parent.find('input.qty-adjust').data('id'),
                val = parseInt(parent.find('input.qty-adjust').val());
            if(val > 1) {
                parent.find('input.qty-adjust').val(val-1);
                quantity_update_timeout(product);
            }
        });
        $('.quantity-add').click(function() {
            let parent = $(this).closest('.input-group'),
                product = parent.find('input.qty-adjust').data('id'),
                val = parseInt(parent.find('input.qty-adjust').val());
            parent.find('input.qty-adjust').val(val+1);
            quantity_update_timeout(product);
        });

        $(document).on('keypress', '.qty-adjust', function(e) {
            if(e.keyCode == 13) {
                let product = $(this).data('id');
                quantity_update(product);
            }
        });

        $('input.qty-adjust').change(function() {
            let product = $(this).data('id');
            quantity_update(product);
        });

        function quantity_update(id) {
            let quantity = $('input.qty-adjust[data-id=\"'+id+'\"]').val();
            clearTimeout(update_timeout);
            document.location.href = '".$zulu->front_link(true,['query'=>['Action'=>'QtyAdjust']])."&id=' + id + '&qty=' + quantity;
        }
        function quantity_update_timeout(id) {
            clearTimeout(update_timeout);
            update_timeout = setTimeout(function() {
                quantity_update(id);
            }, 1000);
        }
        ";
    } else {
        $zulu->template->jquery_code[] = "
        function refresh_cart_summary() {
            $.get('".$zulu->front_link(LINK_checkout, ['query'=>['Do'=>'RefreshCart']])."', function(data) {
                $('.cart-summary').html(data);
                if($('.shipping-options').length) {
                    $('.shipping-options .list-group-item.selected').find('input[name=\"shipping\"]').trigger('change');
                }
                calc_totals();
            });
        }
        function calc_totals() {
            var subtotal = parseFloat($('#price-subtotal').html()),
                discount = $('#price-discount').length ? parseFloat($('#price-discount').html()) : 0,
                shipping = $('#price-ship').length ? parseFloat($('#price-ship').html()) : 0,
                tax = 0,
                total = 0;
            if(discount > 0) {
                var discount_type = $('#price-discount').data('type'),
                    discount_amount = parseFloat($('#price-discount').data('amount'));
                if(discount_type == 'percent') {
                    discount = subtotal * (discount_amount / 100);
                } else if(discount_type == 'fixed') {
                    if(discount_amount > subtotal) {
                        discount_amount = subtotal;
                    }
                    discount = discount_amount;
                }
            }
            total = subtotal - discount + shipping;
            ".(!$class_setting->data['tax_disable']?"
            ".($class_setting->data['tax_method']?"
            tax = total / (1 + (".$class_setting->data['tax_rate']." / 100));
            tax = total - tax;
            ":"
            tax = total * (".$class_setting->data['tax_rate']." / 100);
            total += tax;
            ")."
            $('#price-tax').html(tax.toFixed(2));
            ":null)."
            $('#price-total').html(total.toFixed(2));
            if(discount > 0) {
                $('#price-discount').html(discount.toFixed(2));
            }
        }
        ";
        if($COUPON_enabled) {
            $zulu->template->jquery_code[] = "
            $(document).on('click', '.cart-summary button#coupon-apply', function() {
                $.get('".$zulu->front_link(LINK_checkout, ['query'=>['Do'=>'Coupon','Code'=>'']])."' + $('input[name=\"coupon\"]').val(), function(data) {
                    try {
                        var return_data = JSON.parse(data);
                        refresh_cart_summary();
                    } catch(e) {
                        document.location.href = '".$zulu->front_link(true)."';
                    }
                });
                return false;
            });
            $(document).on('click', '.cart-summary #unlink-coupon', function() {
                $.get('".$zulu->front_link(LINK_checkout, ['query'=>['Do'=>'UnlinkCoupon']])."', function() {
                    refresh_cart_summary();
                });
                return false;
            });
            $(document).on('change keyup', 'input[name=\"coupon\"]', function() {
                let val = $(this).val();
                if(val != '') {
                    $('#coupon-apply').attr('disabled', false);
                } else {
                    $('#coupon-apply').attr('disabled', true);
                }
            });
            ";
        }
    }

	return [
        'html'          =>  $html,
        'cart_count'    =>  $cart_count,
        'is_tangible'   =>  $is_tangible,
        'total'         =>  $cart_summary['total'],
        'html_button'   =>  $button_html,
        'products'      =>  $product_html,
        'coupon'        =>  $coupon_html,
        'totals'        =>  $total_html,
        'module'        =>  $module_html
    ];
}

function related_product_html($product_id=0, $config=[]) {
    global $class_product, $zulu, $class_website, $class_sale;

    $cats_arr = $product_arr = [];
    $product_HTML = "";

    // Get product ids
    if($config['basket']) {
        $cart_data = $class_sale->cart_data(['client_id'=>$_SESSION['user']['id'],'session'=>session_id()]);
        foreach($cart_data as $cart_row) {
            $prod_row = $class_product->product_data(['id'=>$cart_row['product_id']]);
            if($prod_row['parent_id'] > 0)  $product_arr[] = $prod_row['parent_id'];
            else                            $product_arr[] = $prod_row['id'];
        }
    } elseif($product_id > 0) {
        $product_arr[] = $product_id;
    }

    // Get category ids from product_arr
    foreach($product_arr as $product) {
        $prod_meta = $class_product->product_meta($product);
        $cat_meta = $prod_meta['category'];
        if(isset($cat_meta['value'])) {
            $cats_arr[] = $cat_meta['value'];
        } else {
            foreach($cat_meta as $cat) {
                $cats_arr[] = $cat['value'];
            }
        }
		if(trim($product)!=NULL) {
			$new_product_arr[] = $product;
		}
    }
	$product_arr = $new_product_arr;

    // If we've got categories then get some random products from them
    if(count($cats_arr) > 0) {
		$pd_filter = ['root_id'=>$cats_arr,'type'=>'product','row_limit'=>$class_website->config->shop_result_row_count,'sort'=>'RAND()','hide'=>'0'];
		if(count($product_arr)>0) {
			$pd_filter['where'][] = "product.id NOT IN (".implode(',',$product_arr).")";
		}
        $prod_data = $class_product->product_data($pd_filter);
        foreach($prod_data as $prod_row) {
            $class_product->vars->data = $prod_row;
            $product_HTML .= "<li>".$class_product->product_block()."</li>";
        }
    }

    if($product_HTML != null) {
        $product_HTML = "<ul class=\"product-box row".$class_website->config->shop_result_row_count." ls-master\">".$product_HTML."</ul>";
    }

    return $product_HTML;
}

function checkout_coupon_check($code) {
	global $class_sale, $class_book;
	$cart_data = $class_sale->cart_data(array('client_id'=>$_SESSION['user']['id'],'session'=>session_id(),'sort'=>'title ASC'));
	foreach($cart_data as $cart_row) {
		$custom = unserialize($cart_row['custom']);
		if(count($custom['ticket_temp']) > 0) {
			foreach($custom['ticket_temp'] as $ticket_temp_id) {
				$temp_row = $class_book->event_ticket_temp_data(['id'=>$ticket_temp_id]);
				$ticket_row = $class_book->event_date_ticket_data(['id'=>$temp_row['event_ticket_id']]);
				$prod_ids[$ticket_row['ticket_type_id']]['quan'] += $cart_row['quantity'];
				$prod_ids[$ticket_row['ticket_type_id']]['object'] = 'event_ticket';
			}
		} else {
			$prod_ids[$cart_row['product_id']]['quan'] += $cart_row['quantity'];
			$prod_ids[$cart_row['product_id']]['object'] = 'product';
		}
	}
	$check = $class_sale->coupon_check($code,0,$_SESSION['user']['id'],['object_check'=>$prod_ids]);
	return $check;
}

//-- Country
function countryHTML($def=NULL) {
	global $COUNTRY_default;
	$country_list = array(
		"Afghanistan",
		"Albania",
		"Algeria",
		"Andorra",
		"Angola",
		"Antigua and Barbuda",
		"Argentina",
		"Armenia",
		"Australia",
		"Austria",
		"Azerbaijan",
		"Bahamas",
		"Bahrain",
		"Bangladesh",
		"Barbados",
		"Belarus",
		"Belgium",
		"Belize",
		"Benin",
		"Bhutan",
		"Bolivia",
		"Bosnia and Herzegovina",
		"Botswana",
		"Brazil",
		"Brunei",
		"Bulgaria",
		"Burkina Faso",
		"Burundi",
		"Cambodia",
		"Cameroon",
		"Canada",
		"Cape Verde",
		"Central African Republic",
		"Chad",
		"Chile",
		"China",
		"Colombi",
		"Comoros",
		"Congo (Brazzaville)",
		"Congo",
		"Costa Rica",
		"Cote d'Ivoire",
		"Croatia",
		"Cuba",
		"Cyprus",
		"Czech Republic",
		"Denmark",
		"Djibouti",
		"Dominica",
		"Dominican Republic",
		"East Timor (Timor Timur)",
		"Ecuador",
		"Egypt",
		"El Salvador",
		"Equatorial Guinea",
		"Eritrea",
		"Estonia",
		"Ethiopia",
		"Fiji",
		"Finland",
		"France",
		"Gabon",
		"Gambia, The",
		"Georgia",
		"Germany",
		"Ghana",
		"Greece",
		"Grenada",
		"Guatemala",
		"Guinea",
		"Guinea-Bissau",
		"Guyana",
		"Haiti",
		"Honduras",
		"Hungary",
		"Iceland",
		"India",
		"Indonesia",
		"Iran",
		"Iraq",
		"Ireland",
		"Israel",
		"Italy",
		"Jamaica",
		"Japan",
		"Jordan",
		"Kazakhstan",
		"Kenya",
		"Kiribati",
		"Korea, North",
		"Korea, South",
		"Kuwait",
		"Kyrgyzstan",
		"Laos",
		"Latvia",
		"Lebanon",
		"Lesotho",
		"Liberia",
		"Libya",
		"Liechtenstein",
		"Lithuania",
		"Luxembourg",
		"Macedonia",
		"Madagascar",
		"Malawi",
		"Malaysia",
		"Maldives",
		"Mali",
		"Malta",
		"Marshall Islands",
		"Mauritania",
		"Mauritius",
		"Mexico",
		"Micronesia",
		"Moldova",
		"Monaco",
		"Mongolia",
		"Morocco",
		"Mozambique",
		"Myanmar",
		"Namibia",
		"Nauru",
		"Nepa",
		"Netherlands",
		"New Zealand",
		"Nicaragua",
		"Niger",
		"Nigeria",
		"Norway",
		"Oman",
		"Pakistan",
		"Palau",
		"Panama",
		"Papua New Guinea",
		"Paraguay",
		"Peru",
		"Philippines",
		"Poland",
		"Portugal",
		"Qatar",
		"Romania",
		"Russia",
		"Rwanda",
		"Saint Kitts and Nevis",
		"Saint Lucia",
		"Saint Vincent",
		"Samoa",
		"San Marino",
		"Sao Tome and Principe",
		"Saudi Arabia",
		"Senegal",
		"Serbia and Montenegro",
		"Seychelles",
		"Sierra Leone",
		"Singapore",
		"Slovakia",
		"Slovenia",
		"Solomon Islands",
		"Somalia",
		"South Africa",
		"Spain",
		"Sri Lanka",
		"Sudan",
		"Suriname",
		"Swaziland",
		"Sweden",
		"Switzerland",
		"Syria",
		"Taiwan",
		"Tajikistan",
		"Tanzania",
		"Thailand",
		"Togo",
		"Tonga",
		"Trinidad and Tobago",
		"Tunisia",
		"Turkey",
		"Turkmenistan",
		"Tuvalu",
		"Uganda",
		"Ukraine",
		"United Arab Emirates",
		"United Kingdom",
		"United States",
		"Uruguay",
		"Uzbekistan",
		"Vanuatu",
		"Vatican City",
		"Venezuela",
		"Vietnam",
		"Yemen",
		"Zambia",
		"Zimbabwe"
	);

	//For each country
	foreach($country_list as $val) {
		if(($def==NULL&&$val==$COUNTRY_default)||$val==$def) {
			$sel = "selected=\"selected\"";
		}
		$html .= "<option {$sel}>{$val}</option>";
		unset($sel);
	}
	return $html;
}

//-- Generate Includes Code
function head_include($TPL_include) {
	foreach($TPL_include as $key=>$row) {
		switch($key) {
			case 'js':

				foreach($row as $val) {
					$html[] = "<script type=\"text/javascript\" src=\"{$val}\"></script>";
				}

			break;
			case 'css':

				foreach($row as $val) {
					$html[] = "<link type=\"text/css\" rel=\"stylesheet\" href=\"{$val}\" />";
				}

			break;
		}
	}
	return implode("\n",$html);
}
