var current_categerogy_select;
function show_category_select(element){

	current_categerogy_select = element;
	$(element).attr("readonly","readonly");

	if(!document.getElementById("category_select_wrapper")){

		$(document.body).append($("<div id=category_select_wrapper></div>"));
		$("#category_select_wrapper").hide();
		$("#category_select_wrapper").addClass("popup");

		$("#category_select_wrapper").bind("click",function(){
			$(this).hide();
		});

		//url
		var url = location.href.substring(0,location.href.indexOf("index.php/") + 10) + "Common/Ajax/-/Categories";
		$.ajax({
		 	url: url,
		 	cache: false,
			success: function(html){
				var html = $(html);
				$("#category_select_wrapper").append(html);

				//build tree
				html.treeview({
					persist: "location",
					collapsed: false,
					unique: false
				});

				$("#category_select_wrapper").css({backgroundImage:"none"});
			}
		});



	}

	//show
	$("#category_select_wrapper").insertAfter($(element)).css({marginLeft:$(element).width()+5,marginTop:-200}).show();
	$('html,body').animate({ scrollTop: $("#category_select_wrapper").position().top },0);

	//search
	var val = $(element).val();
	$("#category_select_wrapper").animate({scrollTop : 0},0);
	$("#category_select_wrapper a").each(function(){
		$(this).removeClass("selected_category");
		if($(this).attr("object:id") == val){
			$(this).addClass("selected_category");
			$("#category_select_wrapper").animate({scrollTop : $(this).position().top - 50 },0);
		}
	});


	return false;
}

function common_category_tree_click(element){
	var option = $('<option value="'+element.attr("object:id")+'">'+element.text()+'</option>');
	option.attr("selected");
	$(current_categerogy_select).html('<option value="">------</option>');
	$(current_categerogy_select).append(option);
	$(current_categerogy_select).val(element.attr("object:id"));

}