//DO LOAD
var site_rel = zl_main_rel;

//Selected?
$(document).on('click','.sf-selector',function() {
	var this_val = $(this).data('id');
	var this_orig = $(this).data('raw');
	var this_populate = $(this).data('populate');
	//$(".sf-input-" + this_populate).val(this_orig);
	$(this).parent().parent().parent().prev("input").val(this_orig);
	$(this).parent().parent().parent().prev("input").trigger("change");
	$(this).parent().parent().parent().nextAll("input.sf-value").val(this_val).trigger('change');
	$(".guessbox-" + this_populate + " ul").html('');
	$(".guessbox-" + this_populate).hide(200);
	$(".guessbox").hide(200);
	return false;
});
$(document).on('keyup','.sf-input',function() {
	var val = $(this).val();
	var action = $(this).data('sf');
	var val_pop = $(this).data('populate');
	var thisc = $(this);
	$.get(site_rel + "admin/index.php?Page=smartfind&Action=" + action + "&Populate=" + val_pop + "&Value=" + val,function(data) {
		thisc.next().find('ul').html(data);
		thisc.next().show(500);
		//$(".guessbox-" + val_pop + " ul").html(data);
		//$(".guessbox-" + val_pop).show(500);
	});
});