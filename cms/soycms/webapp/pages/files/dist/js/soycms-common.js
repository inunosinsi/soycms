//ブラウザ判断
var is_opera = (navigator.userAgent.toLowerCase().indexOf("opera") != -1);
var is_ie = (navigator.appName=="Microsoft Internet Explorer");
var is_safari = (navigator.userAgent.toLowerCase().indexOf("Safari") != -1);

//ブラウザのバージョンハック
if(is_ie){
	/*@cc_on
	@if (@_jscript_version == 10)
	    is_ie = false;
	  @elif (@_jscript_version == 9)
	    is_ie = false;
	  @else
	    is_ie = true;
	  @end
	@*/
}

//高速化ハック
/*@cc_on
if (@_jscript_version < 9) {
   var _d = document;
   eval("var document = _d");
}
@*/

//共通オブジェクト
var soycms = {};

//toggle
function common_toggle(ele){

	if(typeof(ele) ==  "string"){
		ele = document.getElementById(ele);
	}

	if(ele.style.display != "none"){
		ele.style.display = "none";
	}else{
		ele.style.display = "";
	}
}

document.scroll = function(){
   return {
      x: this.body.scrollLeft || this.documentElement.scrollLeft,
      y: this.body.scrollTop  || this.documentElement.scrollTop
   };
};

//click to layer
function common_click_to_layer(ele,option){

	ele = $(ele);

	var href = ele.attr("href");

	if(!option){
		option = {};
	}

	if(!option.header || option.header.length == 0){
		if(ele.text().length >0){
			option.header = ele.text();
		}else if(ele.attr("title").length > 0){
			option.header = ele.attr("title");
		}
	}

	common_to_layer(href,option);

	//always surpress default action (jump to the linked url)
	return false;
}

//submit to layer
function common_submit_to_layer(ele,option){

	var action = ele.attr("action");
	var targetId = (option && option.targetId) ? option.targetId : "click_to_layer_frame";

	common_to_layer("about:blank",option);

	ele.attr("target",targetId);

	//always
	return true;
}

//element_to_layer
function common_element_to_layer(ele,option){
	common_to_layer(ele,option);

	//always
	return true;
}

//to_layer
function common_to_layer(href,option){

	if(!option)option = {};
	if(!option.width)option.width = 640;
	if(!option.height)option.height = 480;

	var targetId = (option.targetId) ? option.targetId : "click_to_layer_frame";

	if($("#" + targetId + "_wrapper").length > 0){
		$("#" + targetId + "_wrapper").remove();
	}

	var scroll = document.scroll();
	var left = Math.max(10, parseInt(document.body.scrollLeft + document.body.clientWidth / 2 - option.width / 2));
	var top = "";
	if (is_ie) {
		if (document.documentElement.clientHeight == 0) {
			top = Math.max(0, parseInt(scroll.y + document.body.clientHeight / 2 - option.height / 2));
		}else {
			top = Math.max(0, parseInt(scroll.y + document.documentElement.clientHeight / 2 - option.height / 2));
		}
	}else {
		top = Math.max(0, parseInt(scroll.y + window.innerHeight / 2 - option.height / 2));
	}

	//wrapper
	var $wrapper = $("<div>", {
		"id": targetId + "_wrapper",
		"class": "panel panel-green common_to_layer_wrapper"
	}).css({
		"position": "absolute",
		"width": option.width,
		"height": option.height,
		"left": left,
		"top": top,
		"z-index": "1000",
		"overflow": "hidden"
	});

	//jQuery-UIでドラック＆ドロップ
	if(typeof $wrapper.draggable == 'function') $wrapper.draggable();
	if(typeof $wrapper.resizable == 'function') $wrapper.resizable();

	$("body").append($wrapper);

	//header
	var $header = $("<div>", {
		"class": "panel-heading"
	});
	$wrapper.append($header);
	if(option.header && option.header.length >0){
		$header.text(option.header);
	}else if(typeof(href) == "object" && href.text().length >0){
		$header.text(href.text());
	}

	//close button
	var $close = $("<button>", {
		"id": targetId + "_close",
		"class": "close click_to_layer_close",
		"type": "button"
	});
	//$close.addClass("click_to_layer_close");
	$close.html("×");

	$close.click(function(){
		if(option.onclose){
			var result = option.onclose();
			if(result == false)return;
		}
		$wrapper.remove();
	});
	if(option.disableClose == true){
		$close.css("visibility", "hidden");
	}

	$header.append($close);

	//small button
//	var $small = $("<div>", {"id": targetId + "_small"});
//	$small.addClass("click_to_layer_small");
//	$small.html("<a href='#'></a>");
//	$bar.append($small);
//
//	if(option.disableClose == true){
//		$small.css("right","5");
//	}
//
//	$small.click(function(){
//		$("#" + targetId + "_frame_wrapper").toggle();
//	});

	//iframeの両脇を作成
//	var $layer_left = $("<div>", {"id":targetId + "layer_left"});
//	$layer_left.addClass("layer_left");
//	$wrapper.append($layer_left);
//
//	var $layer_right = $("<div>", {"id":targetId + "layer_right"});
//	$layer_right.addClass("layer_right");
//	$wrapper.append($layer_right);

	var $iframe_wrapper = $("<div>", {
		"id":targetId + "_frame_wrapper",
		"class": "layer_wrapper"
	});

	//iframeを生成する場合
	if(typeof(href) != "object"){
		//iframeを作成
		$iframe = $("<iframe>", {
			"id": targetId,
			"name": targetId,
			"src": href,
			"frameborder": "0",
			"class": "click_to_layer_frame"
		});
		$iframe_wrapper.append($iframe);
	}else{
		$iframe_wrapper.append(href);
	}

	$wrapper.append($iframe_wrapper);

	//footer
	//リサイズと相性が悪い
//	if(option.footer && option.footer.length >0){
//		var $footer = $("<div>", {
//			"class": "panel-footer"
//		}).text(option.footer);
//		$footer.css({"clear": "both"});
//		$wrapper.append($footer);
//	}


	var $iframe = $("#" + targetId);

	if($iframe.length > 0){
		$iframe.css({
				"width": option.width,
				"height": option.height - 40//40pxはヘッダーの高さ
		});

		var $target_frame_wrapper = $("#" + targetId + "_frame_wrapper");
		$target_frame_wrapper.css("overflow", "hidden");

		$iframe.ready(function(){
			//!is_ieを指定しておかないとchromeでdynamic編集が表示されない
			if (this.readyState == "complete" || !is_ie) {
				$wrapper.css("visibility", "visible");
			}

			if(is_ie){
				$wrapper.css("visibility", "visible");
			}
		});

		return $iframe.contents();
	}else{
		$wrapper.css("visibility", "visible");
	}

}

/**
 *	only avaiable when layer's id is default.
 */
function common_close_layer(opener){
	common_close_layer_by_targetId("click_to_layer_frame",opener);
}

/*
 *
 */
function common_close_layer_by_targetId(targetId,opener){
	if(opener){
		opener.document.getElementById(targetId + "_close").click();
	}else{
		document.getElementById(targetId +"_close").click();
	}
}



/**
 *	only avaiable when layer's id is default.
 */
function common_get_layer(opener){
	if(opener){
		return opener.document.getElementById("click_to_layer_frame").contentWindow;
	}else{
		return document.getElementById("click_to_layer_frame").contentWindow;
	}
}

function common_resize_layer(option,opener){
	common_resize_layer_by_targetId("click_to_layer_frame",option,opener);
}

function common_resize_layer_by_targetId(targetId,option,opener){

	var _common_get = function(id){
		if(opener){
			return opener.document.getElementById(targetId + id);
		}else{
			return document.getElementById(targetId + id);
		}
	};

	if(!option){
		return;
	}

	if(option.width){
		_common_get("").style.width = option.width + "px";
		_common_get("_wrapper").style.width = option.width + "px";
	}

	if(option.height){
		_common_get("").style.height = (option.height -40) + "px";//40pxはヘッダーの高さ
		_common_get("_wrapper").style.height = option.height + "px";
	}

}

function common_speack_soy_boy(str){
	$("#popup_content").html() = str;
}

function common_show_soy_boy(flag){

	if(!$("#popup_content").html())return;

	/**
	 * @ToDo
	 */
//	if($('#popup').effect){
//		$('#popup').effect.cancel();
//		$('#popup').appear();
//	}


//	$('#popup').mouseover(function(){common_show_soy_boy(true);});
//	$('#soy_logo').mouseover(function(){});
//	$('#popup').mouseout(function(){common_hide_soy_boy();});
//	$("#popup").show();


	if(!flag){
		setTimeout(common_hide_soy_boy,800);
	}
};

function common_hide_soy_boy(){
	$('#popup').mouseover(function(){common_show_soy_boy(true);});
	$('#popup').mouseout(function(){});
	$('#popup').fadeOut("slow");
	/**
	 * @ToDo afterFinishに変わる効果を追加する
	 */
//	$('#popup').effect = new Effect.Fade("popup",{
//		from:1,
//		to : 0,
//		afterFinish : function(){
//			$('popup').mouseover(function(){});
//			$('soy_logo').mouseover(function(){common_show_soy_boy(true);});
//			$('popup').effect = null;
//		}
//	});

}

function common_show_message_popup(node, message){

	var $node = $(node);

	if($("#message_popup").length > 0){
			$("#message_popup").remove();
	}

	var offset = $node.offset();
	var $div = $("<div>", {
			"id": "message_popup"
	}).css({
			"left": offset["left"]+20,
			"visible": "hidden"
	}).addClass("help_popup").html(message);
	$("body").append($div);

	var top = offset["top"] - $div.prop("offsetHeight");
	if(top < 0){
		top = 0;
	}
	console.log(top);
	$div.css("top", top);

	$node.mouseout(function(){
			$div.remove();
			$node.unbind("mouseout", this);
	});
}

//TextAreaに関数付加
function init_text_area(textarea){

	//テキストのペースト
	textarea.insertHTML = function(html){
		if (document.selection != null){
			if(!textarea.selection)textarea.selection = document.selection.createRange();
			textarea.selection.text = html;
			textarea.focus();
			textarea.selection.select();

		}else{
			var start = textarea.selectionStart;
			var end = textarea.selectionEnd;

			var beforeString = textarea.value.substring(0,start);
			var afterString = textarea.value.substring(end);

			var scroll = textarea.scrollTop;
			var scrollLeft = textarea.scrollLeft;

			textarea.value = beforeString + html + afterString;

			textarea.scrollTop = scroll;
			textarea.scrollLeft = scrollLeft;

			textarea.setSelectionRange(start,start + html.length);

			textarea.focus();
		}
	};

	//タブの挿入
	textarea.insertTab = function(e){

		if (document.selection != null){
			textarea.selection = document.selection.createRange();

			var value = textarea.selection.text;

			if(textarea.selection.compareEndPoints('StartToEnd',textarea.selection) == 0){
				textarea.selection.text = String.fromCharCode(9);
			}else{
				if(e.shiftKey){
					value = value.replace( /\n\t/g, "\n" );
					if(value.substr( 0, 1 ) == "\t"){
						value = value.substr( 1, value.length-1 ) + "\n";
					}
				}else{
					value = value.replace( /\n/g, "\n\t" );
					value = "\t" + value + "\n";
				}

				textarea.selection.text = value;
			}
			return;
		}else{
			var start = textarea.selectionStart;
			var end = textarea.selectionEnd;

			var scroll = textarea.scrollTop;

			var beforeString = textarea.value.substring(0,start);
			var afterString = textarea.value.substring(end);

			if(start == end){
				textarea.value = beforeString + "\t" + afterString;
				textarea.scrollTop = scroll;
				textarea.setSelectionRange(start + 1,start + 1);
			}else{
				var value = textarea.value.substring(start,end);
				if(e.shiftKey){
					value = value.replace( /\n\t/g, "\n" );
					if(value.substr( 0, 1 ) == "\t"){
						value = value.substr( 1, value.length-1 );
					}
				}else{
					value = value.replace( /\n/g, "\n\t" );
					if(value.substr(value.length-1, 1) == "\t") {
						value = "\t" + value.substr( 0, value.length-2 ) + "\n";
					}else{
						value = "\t" + value;
					}
				}

				textarea.value = beforeString + value + afterString;
				textarea.scrollTop = scroll;
				textarea.setSelectionRange(start,start + value.length);
			}
			return;
		}
	};

	textarea.moveCursor = function(){
		if(document.selection != null){
			var sel=document.selection.createRange();
			textarea.selection = textarea.createTextRange();
			textarea.selection.moveToPoint(sel.offsetLeft,sel.offsetTop);
		}
	};

	//タブの入力
	textarea.onkeydown = function(e){
		if(!e)e = event;

		textarea.moveCursor();

		if(e.keyCode == 9){
			e.cancelBubble = true;
			e.returnValue = false;
			textarea.insertTab(e);
			return false;
		}

		return true;
	}

}

//独自スタイルのボタン
function common_init_custom_button(){
	var targetClassName = "soycms_custom_button";
	var defaultBorderStyle = {
		color : "#909090",
		style : "outset"
	};

	var buttons = document.getElementsByClassName(targetClassName);
	$(function(){
		_init_custom_buttons();
	});

	function _init_custom_buttons(){
		for(var i=0;i<buttons.length;i++){
			_init_custom_button(buttons[i]);
		}
	}

	function _init_custom_button(button){
		var button = $(button);

		function setDefault(){
			button.css("color","#000080");
			button.css("backgroundColor","#ffffff")
			button.css("borderStyle",defaultBorderStyle.style);
			button.css("borderColor",defaultBorderStyle.color);
			button.css("borderWidth","1px");
		}
		function setOnFocus(){
			button.css("borderColor","#303030");
		}
		function setOnBlur(){
			button.css("borderColor",defaultBorderStyle.color);
			button.css("borderStyle",defaultBorderStyle.style);
		}
		function setOnPress(){
			button.css("borderStyle","inset");
		}

		setDefault();
		button.hover(function(){
			setOnFocus();
		}, function(){
			setOnBlur();
		});
		button.focus(function(){
			setOnFocus();
		});
		button.blur(function(){
			setOnBlur();
		});
		button.mousedown(function(){
			setOnPress();
		});
		button.keypress(function(){
			setOnPress();
		});
	}

}

//
function buildDateString(date,isdate,isend){
	var yy = date.getFullYear();
	var mm = date.getMonth() + 1;
	var dd = date.getDate();
	if (yy < 2000) { yy += 1900; }
	if (mm < 10) { mm = "0" + mm; }
	if (dd < 10) { dd = "0" + dd; }

	if(isdate){
		if(isend){
			return  yy + "-" + mm + "-" + dd + " " + "00:00:00";
		}else{
			return  yy + "-" + mm + "-" + dd + " " + "00:00:00";
		}
	}else{
		return  yy + "-" + mm + "-" + dd + " " + date.getHours() +":"+ date.getMinutes() +":" + date.getSeconds();
	}

}

function movedate(date,y,mo,d,h,mi,s){

	date.setFullYear(date.getFullYear() + y);
	date.setMonth(date.getMonth() + mo);
	date.setDate(date.getDate() + d);
	date.setHours(date.getHours() + h);
	date.setMinutes(date.getMinutes() + mi);
	date.setSeconds(date.getSeconds() + s);
	return date;
}


function soycms_check_site(id, url){

	var timerId = setInterval(function(){
		$.ajax({
			url: url,
			type: 'get',
			success: function(data, req){
				if (parseInt(data) != parseInt(id)) {
					alert(soycms.lang.common.double_login);
				}
			}
		});
	},300000);	//5 minute
}

if($('.help').tooltip && typeof $('.help').tooltip() == "function"){
	$('.help').tooltip({
		selector: "[data-toggle=tooltip]",
		container: "body"
	});
}
