$(document).ready(function() {
	$(".accordion").hover(function(){
		$(this).css("cursor","pointer"); 
	},function(){
		$(this).css("cursor","default"); 
		});
	$(".information").css("display","none");
	$(".accordion").click(function(){
		$(this).next(".information").slideToggle("normal");
		});
});
