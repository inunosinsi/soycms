$(document).ready(function(){
	//拡張
	$.extend(
		$.fn,{
		textarea : function(cond){
			return new advanced_textarea($(this));
		}
	});
	
	//初期化
	$(".editor").each(function(){
		$(this).keydown(function(e){
			if(e.keyCode == 9){
				$(this).textarea().insertTab(e);
				return false;
			}
			return true;
		});
	});
});