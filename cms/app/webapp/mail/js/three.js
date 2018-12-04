(function(){
	//拡張
	$.extend(
		$.fn,{
		textarea : function(cond){
			return new advanced_textarea($(this));
		}
	});
}());
