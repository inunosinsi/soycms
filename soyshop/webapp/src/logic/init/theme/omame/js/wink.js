$(function(){
	$("img.wink").hover(function(){
		$(this).css("opacity", "0.2");
		$(this).css("filter", "alpha(opacity=20)");
		$(this).fadeTo("slow", 1.0);
	});
	$("input.wink").hover(function(){
		$(this).css("opacity", "0.2");
		$(this).css("filter", "alpha(opacity=20)");
		$(this).fadeTo("slow", 1.0);
	});

});
