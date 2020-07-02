$(function(){
	var opts = {
		dateFormat:"yy-mm-dd"
	};
	$(".date_picker_start").datepicker(opts);
	//$(".date_picker_start").attr("readonly", true);
	$(".date_picker_end").datepicker(opts);
	//$(".date_picker_end").attr("readonly", true);
});
