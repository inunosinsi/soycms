(function($){

	$(document).ready(function(){

		var doc = document;

		$("table.form_list").each(function(){
			var counter = 1;
			var rows = null;
			if($(this).find("tbody")){
				rows = $(this).find("tbody tr");
			}else{
				rows = $(this).find("tr");
			}
			rows.each(function(){
				if(0 == (counter % 2))$(this).addClass("odd");
				counter++;
			});

		});

		var common_set_default_text = function(obj){
			$(obj).val($(obj).attr("hint"));
			$(obj).attr("old_color",$(obj).css("color"));
			$(obj).css("color","gray");
		};
		var common_remove_default_text = function(obj){
			$(obj).val("");
			$(obj).css("color",$(obj).attr("old_color"));
		};

		$("input[hint],textarea[hint]").each(function(){

			if($(this).attr("hint").length < 1)return;

			if($(this).val().length < 1)common_set_default_text($(this));

			$(this).bind("focus",function(){
				common_remove_default_text($(this));
			});

			$(this).bind("blur",function(){
				if($(this).val() == $(this).attr("hint") || $(this).val().length < 1){
					common_set_default_text($(this));
				}
			});
		});

		$("form").each(function(){

			$(this).bind("submit",function(){

				$(".default_text").each(function(){
					$(this).val("");
				});

			});

		});

		//validate
		$(".validate").each(function(){

			var input = $(this);

			$(this).bind("blur",function(){
				if(input.val().length < 1){
					$("#" + input.attr("id") + "_error").show();
					return false;
				}
			});

			$(this).bind("focus",function(){
				$("#" + input.attr("id") + "_error").hide();
			});
		});

		//popup
		$(".popup").each(function(){

			var popup = $(this);

			$(this).find(".close").bind("click",function(){
				popup.hide();
			});

			$(this).bind("close",function(){
				popup.hide();
			});

			$(this).bind("popup",function(event,options){

				if(!options)options = {};

				try{

					var scrollLeft = Math.max(doc.documentElement.scrollLeft, doc.body.scrollLeft);
					var scrollTop = Math.max(doc.documentElement.scrollTop,  doc.body.scrollTop);

					if(options.width)$(this).css("width",options.width);
					if(options.height)$(this).css("height",options.height);

					var left = Math.floor(($(window).width() - $(this).width()) / 2 + scrollLeft);
		    		var top  = Math.floor(($(window).height() - $(this).height()) / 4 + scrollTop);

		    		$(this).css({
			            "top": top,
			            "left": left
			         });

			         $(this).show();

		         }catch(e){
		         	alert(e);
		         }

			});
		});

		//scroll
		if(location.search.length && location.search.indexOf("?site_id") < 0){
			var offset = $("#main").offset().top;
			$('html,body').animate({ scrollTop: offset },0);
		}


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

		setTimeout(function(){
			$(".success,.notice,.error").each(function(){
				if(!$(this).hasClass("always"))
					$(this).fadeOut();
			});
		},2000);


	});

})(jQuery);

//アカウント情報の修正
var ChangeAccountInfo = {

	id : null,
	image_info : null,
	showInfo : true,

	popup : function(id){

		this.id = id;

		//if onPopup
		if(window.onPopup){
			window.onPopup();
		}

		$("#account_form_el").trigger("popup",{
			width : 640,
			height: 480
		});
	}
};

//アカウント情報の修正
var OptionWindow = {

	type : null,
	image_info : null,
	showInfo : true,
	url : null,

	popup : function(type){

		this.type = type;
		
		if(type != undefined && type.length > 0){
			url = location.href;
			url = url.replace("Detail", type);
			$("#option_window").prop("src", url);
		}
		
		//if onPopup
		if(window.onPopup){
			window.onPopup();
		}

		//srcの値を挿入してからポップアップを表示する
		setTimeout(function(){
			$("#option_window_el").trigger("popup",{
				width : 640,
				height: 480
			});
		}, 250);
	}
};