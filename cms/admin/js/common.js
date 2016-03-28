//ブラウザ判断
var is_opera = (navigator.userAgent.toLowerCase().indexOf("opera") != -1);
var is_ie = (navigator.appName=="Microsoft Internet Explorer");
var is_safari = (navigator.userAgent.toLowerCase().indexOf("Safari") != -1);

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

function common_toggle_password(ele){
    if(typeof(ele) ==  "string"){
        ele = document.getElementById(ele);
    }
		
    if(ele.type != "password"){
        ele.type = "password";
    }else{
        ele.type = "text";
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
	
//	if(!ele.tagName.match(/a/i)){
//		alert(ele.tagName);
//		return false;
//	}
	
	var href = ele.getAttribute("href");
	common_to_layer(href,option);	
		
	//always
	return false;

}

//submit to layer
function common_submit_to_layer(ele,option){
	
	//ここがよくわからないわ
//	if(!ele.tagName.match(/form/i)){
//		alert(ele.tagName);
//		return false;
//	}

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

	if($("#" + targetId + "_wrapper")){
		$("#" + targetId + "_wrapper").remove();
	}
	
	//wrapper
	var wrapper = $("<div/>");
	
	wrapper.css("position","absolute");
	wrapper.css("width",option.width + "px");
	wrapper.css("height",option.height + "px");
			
	var scroll = document.scroll();
		
	var left = Math.max(10, parseInt(document.body.scrollLeft + document.body.clientWidth / 2 - option.width / 2)) + "px";
	var top = "";
	if (is_ie) {
		if (document.documentElement.clientHeight == 0) {
			top = Math.max(0, parseInt(scroll.y + document.body.clientHeight / 2 - option.height / 2));
		}
		else {
			top = Math.max(0, parseInt(scroll.y + document.documentElement.clientHeight / 2 - option.height / 2));
		}
			
			
	}
	else {
		top = Math.max(0, parseInt(scroll.y + window.innerHeight / 2 - option.height / 2)) + "px";
	}
	
	//仮
	wrapper.css("left",left);
	wrapper.css("top",top);

	wrapper.attr("id",targetId + "_wrapper");
	wrapper.attr("class","common_to_layer_wrapper");
	
	$("body").append(wrapper);
	
	//jQueryでドラック＆ドロップ
	$("#" + targetId + "_wrapper").draggable();
		 
	//window bar
	var bar = $("<div/>");
	bar.attr("id",targetId + "layer_bar");
	bar.css("height","25px");
	wrapper.append(bar);
	
	var bar_left = $("<div/>");
	bar_left.attr("id",targetId + "_layer_bar_left");
	bar_left.attr("class","layer_bar_left");
	bar.append(bar_left);
	
	var bar_mid = $("<div/>");
	bar_mid.attr("id",targetId + "_layer_bar_mid");
	bar_mid.attr("class","layer_bar");
	bar_mid.css("width",option.width - 27 + "px");
	bar.append(bar_mid);

	var bar_right = $("<div/>");
	bar_right.attr("id",targetId + "_layer_bar_right");
	bar_right.attr("class","layer_bar_right");
	bar.append(bar_right);

		
	//close button
	var close = $("<div/>");
	close.attr("id",targetId + "_close");
	close.attr("class","click_to_layer_close");
	close.html("<a href='#'></a>");
	
	//ここを何とかする
	close.click(function(){
		if(option.onclose){
			var result = option.onclose();
			if(result == false)return;
		}
		$("#" + targetId + "_wrapper").remove();
	});
	if(option.disableClose == true){
		close.css("visibility","hidden");
	}
	
	bar.append(close);
	
	//small button
	var small = $("<div/>");
	small.attr("id",targetId + "_small");
	small.attr("class","click_to_layer_small");
	small.html("<a href='#'></a>");
	bar.append(small);
	
	if(option.disableClose == true){
		small.css("right","5px");
	}
		
	small.click(function(){
		$("#" + targetId + "layer_left").toggle();
		$("#" + targetId + "layer_right").toggle();
		$("#" + targetId + "_frame_wrapper").toggle();
		//$(targetId + "_layer_bottom").toggle();
	});
		
	//iframeの両脇を作成
	var layer_left = $("<div/>");
	layer_left.attr("id",targetId + "layer_left");
	layer_left.attr("class","layer_left");
	wrapper.append(layer_left);
		
	var layer_right = $("<div/>");
	layer_right.attr("class","layer_right");
	layer_right.attr("id",targetId + "layer_right");
	wrapper.append(layer_right);
	
	var iframe_wrapper = $("<div/>");
	iframe_wrapper.attr("id",targetId + "_frame_wrapper");
	iframe_wrapper.attr("class","layer_wrapper");
		
	//iframeを生成する場合
	if(typeof(href) != "object"){
		//iframeを作成
		iframe_wrapper.html('<iframe src="'+href+'" name="'+ targetId + '" frameborder="0" id="'+targetId+'" class="click_to_layer_frame"></iframe><div style="clear:both;height:0;width:0;line-height:0px;"><!----></div>');
	}else{
		iframe_wrapper.append(href);
	}
	
	iframe_wrapper.css("width",option.width - 10 + "px");
	iframe_wrapper.css("height",option.height + "px");
	
	wrapper.append(iframe_wrapper);
		
	//iframeの下を作成	
	var bar_bottom = $("<div/>");
	bar_bottom.attr("id",targetId + "_layer_bottom");
	
	var layer_bottom_left = $("<div/>");
	layer_bottom_left.attr("id",targetId + "_layer_bottom_left");
	layer_bottom_left.attr("class","layer_bottom_left");
	bar_bottom.append(layer_bottom_left);
	
	var layer_bottom_mid = $("<div/>");
	layer_bottom_mid.attr("id",targetId + "_layer_bottom_mid");
	layer_bottom_mid.attr("class","layer_bottom");
	layer_bottom_mid.css("width",option.width - 17 + "px");
	bar_bottom.append(layer_bottom_mid);
	
	var layer_bottom_right = $("<div/>");
	layer_bottom_right.attr("id",targetId + "_layer_bottom_right");
	layer_bottom_right.attr("class","layer_bottom_right");
	bar_bottom.append(layer_bottom_right);
	
	//resize
	layer_bottom_right.mousedown(function(e){

		var iframe = $("#" + targetId);
		if(iframe)iframe.css("visibility","hidden");

		document.mousemove(function(e){
			if(!e)e=event;
			var x = Event.pointerX(e);	
			var y = Event.pointerY(e);
			
			var newWidth = x - wrapper.offsetLeft;
			var newHeight = y - wrapper.offsetTop;
								
			common_resize_layer_by_targetId(targetId,{width:newWidth,height:newHeight});
			
			return false;
		});
			
		layer_bottom_right.mouseup(function(e){
			document.mousemove(function(){});
			var iframe = $("#" + targetId);
			if(iframe)iframe.css("visibility","visible");
		});
		
		document.mouseup(function(e){
			document.onmousemove = function(){};
			var iframe = $("#" + targetId);
			if(iframe)iframe.css("visibility","visible");
		});
			
	});
	
	
	wrapper.append(bar_bottom);
	
	var iframe = $("#" + targetId);
	
	if(iframe){
		iframe.css("width","100%");
		iframe.css("height","100%");
				
		var target_frame_wrapper = $("#" + targetId + "_frame_wrapper");
		target_frame_wrapper.css("overflow","hidden");
		
		/**
		 * @ここら辺、あやしい
		 */
		iframe.load(function(){
			if (this.readyState == "complete" || !is_ie) {
				wrapper.css("visibility","visible");
			}
		});

		iframe.ready(function(){
			if (this.readyState == "complete" || !is_ie) {
				wrapper.css("visibility","visible");
			}
		});

		return iframe.contents();
	}else{
		wrapper.css("visibility","visible");
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
		_common_get("_wrapper").style.width = option.width + "px";
		_common_get("_frame_wrapper").style.width = option.width - 10 + "px";
		_common_get("_layer_bar_mid").style.width = option.width - _common_get("_layer_bar_left").offsetWidth - _common_get("_layer_bar_right").offsetWidth + "px";
		_common_get("_layer_bottom_mid").style.width = option.width - _common_get("_layer_bottom_left").offsetWidth - _common_get("_layer_bottom_right").offsetWidth + "px";
	}
	
	if(option.height){
		_common_get("_wrapper").style.height = option.height + "px";
		_common_get("_frame_wrapper").style.height = option.height + "px";
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

	if($("#message_popup").size() > 0){
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
				if (parseInt(data) == parseInt(id)) {
				
				}
				else {
					alert(soycms.lang.common.double_login);
				}
			}
		});
	},300000);	//5 minute
}