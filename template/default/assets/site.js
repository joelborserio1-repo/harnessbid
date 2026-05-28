$(document).ready(function(e) {

    // toggle dropdown menu on click of a menu link
    $('.navigation .menu li.has-children a.menulink').click(function() {
        var parent = $(this).closest('li.has-children');
        var isOpen = parent.hasClass('open');
        closeMenus();
        if(!isOpen) {
           parent.addClass('open');
        }
        return false;
    });
    // close dropdown menus on click
    $(document).on('click', function(e) {
        closeMenus();
        if($(".mobile-menu-trigger").is(':visible') && $(".navigation").is(':visible') && !$(e.target).closest('.navigation').length) {
            $(".mobile-menu-trigger").trigger('click');
        }
    });
    // toggle mobile menu on click of menu icon
    $(".mobile-menu-trigger").click(function() {
        if(!$(".navigation").is(':visible')) {
            $(".navigation").show("slide", { direction: "right" }, 300);
            $("body").addClass('mobile-menu-active');
        } else {
            $(".navigation").hide("slide", { direction: "right" }, 300);
            $("body").removeClass('mobile-menu-active');
        }
        return false;
    });
    // close mobile menu
    $(".navigation .mobile-menu-close").click(function() {
        $(".mobile-menu-trigger").trigger('click');
        return false;
    });

    $(".mobile-menu-trigger").click(function() {
        $(".mobile-menu-trigger").toggleClass('open');
        return false;
    });

	//-- Fancybox for info
	$(".bt-info-toggle").fancybox({
		maxWidth	: 800,
		maxHeight	: 800,
		fitToView	: false,
		width		: '50%',
		height		: '90%',
		autoSize	: false,
		closeClick	: false,
		openEffect	: 'none',
		closeEffect	: 'none',
		padding		: 20
	});

	//-- Date picker
	$(".date-picker").datepicker({
		dateFormat: "dd/mm/yy"
	});

    $(document).on('click', '.popup-overlay .popup-close, .popup-overlay .popup-hide', function() {
        $(this).closest('.popup-overlay').popup('hide');
		return false;
    });
    $(document).on('click', '.popup-overlay-trigger', function() {
        $('#'+$(this).data('popup')).popup({
            autoopen: true,
			scrolllock: true,
        });
        return false;
    });

    $(document).on('change', '.list-group .list-group-item .lgi-radio input', function() {
        let list_group = $(this).closest('.list-group'),
            list_group_item = $(this).closest('.list-group-item');
        list_group.find('.list-group-item.selected').removeClass('selected');
        list_group_item.addClass('selected');
    });

    //-- Input field inset
    $(document).on('keyup change', '.field-inset-wrapper input, .field-inset-wrapper textarea', function() {
        let parent = $(this).closest('.field-inset-wrapper'),
            val = $(this).val();
        if(val == '') {
            parent.removeClass('has-content');
        } else {
            parent.addClass('has-content');
        }
    });
    init_field_inset();

    //-- Smooth scroll to ID or top
    $(document).on('click', 'a[href^="#"]', function(e) {
        let href = $(this).attr('href');
        if(href.length > 1) {
            let target = $(href);
            if(target.length) {
                e.preventDefault();
                $('html, body').animate({
                    scrollTop: target.offset().top
                }, 200);
            }
        } else if($(this).hasClass('scroll')) {
            e.preventDefault();
            scrollToTop();
        }
    });

    //-- Alert close
    $(document).on('click', '.alert .close', function(e) {
        $(this).closest('.alert').fadeOut(150);
        e.preventDefault();
    });

    //-- Checkout summary toggle
    $(document).on('click', '.page-checkout .cart-header', function(e) {
        let summary = $('.sidebar-content .cart-summary');
        $(this).toggleClass('active');
        if(summary.hasClass('d-none')) {
            summary.removeClass('d-none');
            $(this).find('.toggle-text.toggle-show').addClass('d-none');
            $(this).find('.toggle-text.toggle-hide').removeClass('d-none');
        } else {
            summary.addClass('d-none');
            $(this).find('.toggle-text.toggle-show').removeClass('d-none');
            $(this).find('.toggle-text.toggle-hide').addClass('d-none');
        }
        e.preventDefault();
    });

    $(document).on('click', '.product-box .product-type-product .watchlist a', function(e) {
        e.preventDefault();
        let ele = $(this),
            toggle = ele.attr('data-toggle'),
            url = ele.attr('href');
        console.log(toggle);
        $.get(url, {
            Action: `watchlist_${toggle}`,
            ajax: '1'
        }, function(data) {
            if(data == 'login') {
                document.location.href = zl_account_login;
            } else if(data == '1') {
                if(toggle == 'add') {
                    ele.attr('data-toggle', 'remove');
                    ele.find('.icon').removeClass('fa-binoculars');
                    ele.find('.icon').addClass('fa-check-circle');
                } else {
                    ele.attr('data-toggle', 'add');
                    ele.find('.icon').addClass('fa-binoculars');
                    ele.find('.icon').removeClass('fa-check-circle');
                }
            }
        });
    });

    $('.listing-filter .listing-filter-nav .filter-option.filter-dropdown a').click(function(e) {
		e.preventDefault();
		let type = $(this).data('type'),
			filterOption = $(this).closest('.filter-option');
		if(filterOption.hasClass('active')) {
			$('.listing-filter .listing-filter-nav .filter-option.active').removeClass('active');
			$('.listing-filter-dropdown.active').removeClass('active');
		} else {
			$('.listing-filter .listing-filter-nav .filter-option.active').removeClass('active');
			$('.listing-filter-dropdown.active').removeClass('active');
			filterOption.addClass('active');
			$('.listing-filter-dropdown[data-type=\"'+type+'\"]').addClass('active');
		}
	});
	$('.listing-filter .listing-filter-dropdown a').click(function(e) {
		e.preventDefault();
		let val = $(this).html(),
			container = $(this).closest('.listing-filter-dropdown');
		if(val == 'All') {
			val = '';
		}
		container.find('input').val(val);
		$(this).closest('form').submit();
	});

    if($(".faq").length) {
        $(".faq .faq-inner").hide();
    	var f_i = 0;
    	var f_c = null;
    	$(".faq-inner").each(function() {
    		$(this).data("num","n_" + f_i);
    		f_i++;
    	});
    	$(".faq h3").click(function() {
            let heading = $(this);
    		if(heading.next("div").data("num")!=f_c) {
    			$(".faq .faq-inner").hide(300);
    		}
    		heading.next('div').slideToggle(300);
    		f_c = heading.next("div").data("num");
    		return false;
    	});
    }

    $('.addon-slidebox').hide();
});

$(document).scroll(function() {
    console.log('scroll');
    if($(document).scrollTop() > 50) {
        $('#header').addClass('scrolling');
    } else {
        $('#header').removeClass('scrolling');
    }
});

$(document).on('click','.button.toggle-addon-slidebox',function() {
    console.log('Toggle slidebox target: ' + '.addon-slidebox.' + $(this).data('target'));
    $('.addon-slidebox.' + $(this).data('target')).slideToggle(500);
    return false;
});

//-- Toggle
$(document).on('click','.js-sidebox-toggle .sidebox-top', function() {
    if(window.outerWidth <= 900) {
        var sidebox = $(this).closest('.sidebox');
        var tog_value = sidebox.data('toggled');
        if(tog_value!=1) {
            sidebox.addClass('toggled');
            sidebox.data('toggled',true);
            sidebox.find('.sidebox-body').slideDown(200);
        } else {
            sidebox.removeClass('toggled');
            sidebox.data('toggled',false);
            sidebox.find('.sidebox-body').slideUp(200);
        }
    }
    return false;
});

//DO NOTIFICATION
function notification(text,type,redir) {
    var class_type,
        notify_run = 0;
	if(notify_run == 0) {
		notify_run = 1;
		if(type==2) {
			class_type = "red";
		} else {
			class_type = "green";
		}
		$("#note_box").html("<p>" + text + "</p><p><span id='note_box_redir'></span></p>");
		$("#note_box").addClass(class_type);
		if(redir == null || redir == "undefined") {
			$("#note_box").slideDown(500).delay(3000).slideUp(500,function() { notify_run = 0; });
		} else {
			$("#note_box_redir").html("Redirecting...");
			$("#note_box").slideDown(500).delay(3000).slideUp(500,function () { $("#container").slideUp(500,function() { window.location.href=redir; notify_run = 0; })});
		}
	}
}

function closeMenus() {
    $('.navigation .menu li.open').removeClass('open');
}

function init_field_inset() {
    if($('.field-inset-wrapper').length) {
        $('.field-inset-wrapper input, .field-inset-wrapper textarea').trigger('change');
        if($('.field-inset-wrapper select').length) {
            $('.field-inset-wrapper select').each(function() {
                let parent = $(this).closest('.field-inset-wrapper');
                if(parent.find('label').length) {
                   parent.addClass('has-content');
                }
            });
        }
    }
}

function scrollToTop() {
    $('html, body').animate({
        scrollTop: 0
    }, 200);
}

function getGeoLocation() {
    if(navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(function(position) {
            /*$.post('/', {
                'action': 'store_geo',
                'geo_lat': position.coords.latitude,
                'geo_long': position.coords.longitude,
            });*/
            geocodeLatLng(position.coords.latitude, position.coords.longitude);
        });
    } else {
        console.log('Geolocation is not supported by this browser.');
    }
}

function geocodeLatLng(lat, long) {
    const latlng = {
        lat: parseFloat(lat),
        lng: parseFloat(long),
    };
    const geocoder = new google.maps.Geocoder();

    geocoder
        .geocode({ location: latlng })
        .then((response) => {
            if (response.results[0]) {
                console.log(response);
                /*map.setZoom(11);

                const marker = new google.maps.Marker({
                    position: latlng,
                    map: map,
                });

                infowindow.setContent(response.results[0].formatted_address);
                infowindow.open(map, marker);*/
            } else {
                window.alert("No results found");
            }
        })
        .catch((e) => window.alert("Geocoder failed due to: " + e));
}

function getTimezone() {
    let timezone = Intl.DateTimeFormat().resolvedOptions().timeZone;
	return timezone;
}

function setTimezone() {
    let timezone = getTimezone();
    if(timezone != null) {
        $.post('/', {
            'action': 'store_timezone',
            'timezone': timezone,
        });
    }
}
