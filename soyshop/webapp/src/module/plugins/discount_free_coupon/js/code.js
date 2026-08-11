$("#coupon_category").on("change", function(){
	var selected = $(this).val();
	var prefix = (selected && prefixList[selected]) ? prefixList[selected] : "";	//PHP側でprefixList配列を生成している
	$("#coupon_code").val(prefix);
});
