var template_editor_configuration = {};
function setConfig(config){
	template_editor_configuration = config;
}

var editor_mode = "editor";

$(function(){
	PanelManager.init("template_wrapper");
	
	//ブロックリスト
	if($("#block_list").length>0){
		PanelManager.getPanel("west").addTab(soycms.lang.template_editor.block_list_tab_name,$("#block_list"),{deletable : false});
	}

	//ヘルプ
	if($("#block_help_area").length>0){
		PanelManager.getPanel("west").addTab(soycms.lang.template_editor.block_help_erea_tab_name,$("#block_help_area"),{deletable : false});
	}

	//HTMLテンプレート
	if($("#template_editor_wrapper").length>0){
		PanelManager.getPanel("west").addTab(soycms.lang.template_editor.html_editor_tab_name,$("#template_editor_wrapper"),{onresize : resizeTextArea, deletable : false, onactive : activeTemplateEditor});
	}

	//携帯ページ用仮想ツリー
	if($("#virtual_tree").length>0){
		$("#virtual_tree").show();
		PanelManager.getPanel("west").addTab(soycms.lang.template_editor.virtual_tree_tab_name,$("#virtual_tree"),{ deletable : false});
	}

	PanelManager.getPanel("west").show();

	//クッキーからエディターの状態の取得
	var regexp = new RegExp('; editor_mode=(.*?);');
	var match  = ('; ' + document.cookie + ';').match(regexp);
	editor_mode = (match) ? match[1] : 'editor';

	//テキストエリアの初期化
	var textarea = $("#template_content").css({
		"position": "relative",
		"width"   : "100%",
		"height"  : "100%"
	});
	textarea.hide();

	//フレームの初期化
	var iframe = $("#template_editor_frame");
	iframe.get(0).contentWindow.location.href = iframe.attr("_src") + "?tn" + (new Date()).getTime();
	iframe.css({
		"position": "relative",
		"width"   : "100%",
		"height"  : "100%"
	}).show();

	if(getStyleSheet().length == 0 && $("#cssButton")){
		$("#cssButton").hide();
	}
	
	//tabキーの実行
	textarea.keydown(function(e){
		// キーコードが Tabキー押下時と一致した場合
        if (e.which == 9 || e.keyCode == 9) {
        	var current_position = this.selectionStart;
            //var end_position = this.selectionEnd;	//end_positionの取得が無くてもtext2のsubstrは動く
            var text1 = $(this).val().substr(0, current_position);
            var text2 = $(this).val().substr(current_position);
            
            // タブを挿入
            var value = text1 + '\t' + text2;
            $(this).val(value);
            this.selectionStart = current_position + 1;
            this.selectionEnd = current_position + 1;
            
            // Tabキー押下時の通常の動作を無効化
            return false;
        }
  });

	$("#main_form").submit(function(){
		sync_code();
	});

	//絵文字
	try{
		if(typeof mceSOYCMSEmojiURL == "undefined"){
			throw "error";
		}
	}catch(e){
		if($("#emojiButton"))$("#emojiButton").hide();
	}
});

function debug(str,flag){
	if(flag){
		var html = $("#debug").html() + str + "<br>";
		$("#debug").html(html);
	}else{
		$("#debug").html(str);
	}
}

/**
 *	エディタの切り替え
 */
function toggle_editor(){

	if(editor_mode == "editor"){
		sync_code();
		editor_mode = "textarea";
	}else{
		var code = $("#template_content").val();
		if(code.length < 1)code = "\n";
		$("#template_editor_frame").get(0).contentWindow.TemplateEditor.setCode(code);
		editor_mode = "editor";
	}

	document.cookie = 'editor_mode=' + editor_mode + '; expires=' + new Date(2030, 1).toUTCString();

	$("#template_content").toggle();
	$("#template_editor_frame").toggle();
}

/**
 *	HTMLコードの同期を取る
 */
function sync_code(){

	if(editor_mode == "editor"){
		$("#template_content").val(template_editor_get_code());
	}else{
		var code = $("#template_content").val();
		if(code.length < 1)code = "\n";
		if($("#template_editor_frame").get(0).contentWindow.TemplateEditor){
			$("#template_editor_frame").get(0).contentWindow.TemplateEditor.setCode(code);
		}
	}

	return $("#template_content").val();

}

var old_template_value = "";
function showPreview(){

	//同期
	sync_code();
	//$("#template_content").val(template_editor_get_code());

	var content = $("#template_content").val();
	//iframeの追加
	if($("#template_content_preview_wrapper").length == 0){
		var $iframe = $("<iframe>", {
			id: "template_content_preview",
			frameborder: 0,
			src: "about:blank"
		}).css({
			width: "100%",
			height: "100%",
			"background-color": "white",
			border: "none"
		});

		var $iframeWrapper = $("<div>", {id: "template_content_preview_wrapper"}).append($iframe);
			
		$("body").append($iframeWrapper);

		if($("#block_list")){
			if($("#block_list").prop("panel_pos") == "south"){
				PanelManager.inactiveSouthPanel();
			}
		}

		PanelManager.getPanel("east").addTab("Preview", $iframeWrapper, {onactive : showPreview});

		if($("#block_list").length>0){
			if($("#block_list").prop("panel_pos") == "south"){
				PanelManager.activeSouthPanel();
			}
		}
	}else{
		//変化がなければ何もしない（CSSの変更は無視して良いのか？）
		if($("#template_content").val() == old_template_value){
//			return;
		}
	}

	//前の値を保存
	old_template_value = $("#template_content").val();

	//contentを書き換える
	content = '<base href="'+siteURL+'"/>'+content;

	var lines = content.replace("\r\n","\n").split("\n");
	var newContent = new Array();
	for(var i=0; i < lines.length; i++){
		var line = lines[i];

		var r = line.match(/<(div)[\s>]|<(p)[\s>]|<(h[1-5])[\s>]|<(li)[\s>]/i);
		if(r){

			var tag =	(r[1]) ? r[1] :
						(r[2]) ? r[2] :
						(r[3]) ? r[3] :
						(r[4]) ? r[4] : null;

			if(!tag)continue;

			var pos = content.indexOf(line) + line.indexOf("<" + tag);

			//onclick無効化
			line = line.replace("onclick=","_onclick=");

			//行数属性追加
			line = line.replace('<'+tag,'<'+tag+' text:line="'+i+'" text:pos="'+pos+'" ');

			//onclick属性追加
			var ondblclick = new Array();
			ondblclick.push('if(!event)event=arguments.callee.caller.arguments[0];');
			ondblclick.push('event.returnValue=false;event.cancelBubble=true;');
			ondblclick.push('event.returnValue=false;event.cancelBubble=true;');
			ondblclick.push('var pos = this.getAttribute(\'text:pos\');');
			ondblclick.push('window.parent.scrollTextArea(this.getAttribute(\'text:line\'),pos);');
			ondblclick.push('return false;');
			line = line.replace('<'+tag,'<'+tag+' ondblclick="'+ondblclick.join("")+'" ');
		}

		if(line.match(/<a/i)){
			//onclick無効化
			line = line.replace("onclick=","_onclick=");
			line = line.replace(/<a/i,'<a onclick="return false;" ');
		}

		newContent.push(line);
	}
	content = newContent.join("\n");

	//絵文字用の書き換え
	try{
		if(typeof(mceSOYCMSEmojiURL) != "undefined"){
			var imageUrl = mceSOYCMSEmojiURL.replace("index.html","");
			content = content.replace(/\[e:([0-9]+)\]/g,'<img src="'+imageUrl + '$1.gif' + '" />');
		}
	}catch(e){
	}
	
	//編集中のCSSを反映
	if($("#css_list").length>0 && $("#css_list").val() != "none"){
		content += "<style type=\"text/css\">"+$("#css_editor").val()+"</style>";
	}

	//プレビューコンテンツ描画
	var $iframe = $("#template_content_preview");
	if($iframe.length>0){
		$iframe.hide();
	
		var d = $iframe.get(0).contentWindow.document;
		d.clear();
		d.write(content);
		d.close();
	
		$iframe.show();
	
		$iframe.get(0).contentWindow.document.onclick = function(){
			showPreview();
		}
	}
}
function scrollTextArea(line, pos){

	var tab_id = $("#template_editor_wrapper").prop("tab_id");
	if($("#"+tab_id).length == 0) return;
	var panel_pos = $("#"+tab_id).prop("panel_pos");

	PanelManager.getPanel(panel_pos).activeTab(tab_id);

	var $textarea = $("#template_content");
		
	$textarea.scrollTop(line * 12);

	$textarea[0].focus();
	$textarea[0].setSelectionRange(pos, pos);
	$textarea[0].focus();

	// TODO ie
	/*$textarea[0].focus();

	if(is_ie){
		if($textarea.value.substring(0,pos).indexOf("\n") != -1){
			var step = $textarea.value.substring(0,pos).match(/\n/g).length;
		}else{
			var step = 0;
		}

		var range = $textarea.createTextRange();
		range.move('character', pos-step);
		range.select();
	}else{
		$textarea[0].setSelectionRange(pos, pos);
	}

	$textarea[0].focus();*/
}

function resizeTextArea($wrapper, $container){

	var $textarea = $("#template_content");
		
	if($textarea.length == 0) return;
	if($wrapper.length == 0) return;

	var diff = (is_ie) ? 0 : 2;

	$textarea.css({
		"width" : $wrapper.prop("offsetWidth") - diff,
		"height": $wrapper.prop("offsetHeight") - diff
	});
}

function resizeCSSEditArea($wrapper, $container){

	var $textarea = $("#css_editor");

	if($wrapper.length == 0) return;
	if($textarea.length == 0) return;

	var adjustSize = function($element, $node, size){
		var $pNode = $node.length ? $node : $element.parent();
		var height = $pNode.prop("offsetHeight");
		height = height - $element.prop("offsetTop") - size;
		$element.css("height", height);	//borderとかの計算はとりあえず無視

		$textarea.css("width", $wrapper.prop("offsetWidth") - 173);
	};

	if($container.length){
		if(is_ie){
			if($("#css_editor").length) adjustSize($("#css_editor"), $container.parent(), 40);
			if($("#cssMenu").length) adjustSize($("#cssMenu").parent(), $container.parent(), 1);
		}else{
			if($("#css_editor").length) adjustSize($("#css_editor"), $container.parent(), 2);
			if($("#cssMenu").length) adjustSize($("#cssMenu").parent(), $container.parent(), 2);
		}
	}

}

function showCSSEditor(){
	var $cssEditArea = $("#css_editarea");

	var styles = getStyleSheet();
	if(styles.length == 0){
		return;
	}

	var $cssList = $("#css_list");
	$cssList.empty();

	var $option = $("<option>");
	$option.val("none");
	$option.html(soycms.lang.template_editor.css_list_text);
	$cssList.append($option);

	$.each(styles, function(i, href){
		var $option = $("<option>");
		$option.val(href);
		$option.html(href);
		$cssList.append($option);
	});
	
	if($cssEditArea.hasClass("active")) return;

	$cssEditArea.show();
	$cssEditArea.addClass("active");
	PanelManager.getPanel("south").addTab("CSS", $cssEditArea, {onresize : resizeCSSEditArea, onclose : function(){
		$cssEditArea.removeClass("active");
		$cssEditArea.hide();
		$("body").append($cssEditArea);
	}});

	$("#css_editor").blur(function(){
		if($("#template_content_preview_wrapper").length) showPreview();
	});
}

function onSelectCSS(selectedValue){
	CSSView.clear();

	if(selectedValue == "" || selectedValue == "none"){
		return false;
	}

	selectedValue.indexOf(siteURL) == 0 ? $("#save_css_button").show() : $("#save_css_button").hide();

	$.ajax({
		type: "POST",
		url: cssURL + "/GetCSS",
		data: {
			path: selectedValue
		},
		dataType: "text",
		success: function(css){
			$("#css_editor").val(css);
			CSSView.render();
		},
		error: function(httpObj){
			$("#css_editor").val(soycms.lang.common.read_error);
		}
	});
}

function getStyleSheet(){

	sync_code();

	var content = $("#template_content").val();
	
	var $iframe = $("#getstylesheet_iframe").contents().length ? $("#getstylesheet_iframe") : null;
	
	if(!$iframe){
		$iframe = $("<iframe>");
		$iframe.attr("id", "getstylesheet_iframe");
		$iframe.attr("src", "about:blank");
		$iframe.css("display", "none");
		$("body").append($iframe);
		$iframe.contents().find("head").append("<base href='"+siteURL+"'/>");
	}

	// firefoxではbodyにiframeを入れると何故かbaseタグが消えるので再度挿入
	var head = $iframe.contents().find("head");
	//IE10対応
	if(head.html() == undefined){
		head.append("<base href='"+siteURL+"'/>");
	//Firefox対応
	}else if(head.html().length < 1){
		head.append("<base href='"+siteURL+"'/>");
	}

	$iframe.contents()[0].getStyleSheet = function(){
		// ※iframe内ではjqueryは使えないよ！！！
		var styles = new Array();

		var regExp = new RegExp('<\(link[^>]*\)>',"gi");
		var relRegExp = new RegExp('rel\s*=\s*["\']([^"\']*)["\']',"i");
		var hrefRegExp = new RegExp('href\s*=\s*["\']([^"\']*)["\']',"i");

		while(rs = regExp.exec(content)){
			var link = rs[1];
			var rel = relRegExp.exec(link);
			if(!rel || !rel[1].match(/stylesheet/i))continue;

			var href = hrefRegExp.exec(link);

			var tmp = this.createElement("a");
			tmp.href = href[1];

			styles.push(tmp.href);
		}

		return styles;
	};

	return $iframe.contents()[0].getStyleSheet();
}

function saveCSS(){

	if($("#css_list").val() == "none"){
		return;
	}

	$.ajax({
		type: "post",
		url: cssURL+"/SaveCSS",
		data: $("#css_editor").serialize()+'&'+$("#css_list").serialize() + "&soy2_token=" + $("#main_form").find("[name=soy2_token]").val(),
		dataType: "json",
		success: function(json){
			alert(json.result);
			$("#main_form").find("[name=soy2_token]").val(json.soy2_token);
		},
		error: function(json){
			alert(soycms.lang.common.read_error);
		}
	});
}

function insertHTML(code){
	
	if(editor_mode == "editor"){
		var frame = $("#template_editor_frame");
		frame.get(0).contentWindow.TemplateEditor.insertCode(code);


	}else{		
		textarea = $("#template_content");
		var text = textarea.val()+ "\n\n" + code;
		textarea.val(text);
	}
}

//エディタの初期化
function init_template_editor(){

	var textarea = $("#template_content");
	var frame = $("#template_editor_frame");
	
	if(!frame.get(0).contentWindow || !frame.get(0).contentWindow.TemplateEditor){
		return;
	}

	var ua = navigator.userAgent;
	
	try{
		if(ua.match('MSIE')){
			frame.get(0).contentWindow.document.getElementById("main").contentEditable = true;
		}else{
			frame.get(0).contentWindow.document.designMode = "On";
		}
		frame.get(0).inited = true;
	}catch(e){
		//do nothing
	}

	var code = textarea.val();
	if(code.length < 1)code = "\n";
	frame.get(0).contentWindow.TemplateEditor.setCode(code);

	if(editor_mode != "editor"){
		editor_mode = "editor";
		toggle_editor();
	}

	//TextAreaも拡張する
	init_text_area(textarea);
}

//テンプレートエディタがactiveになったときに呼び出される
function activeTemplateEditor(){

	var ua = navigator.userAgent;
	var frame = $("#template_editor_frame");

	if(frame.inited)return;

	if(!frame.get(0).contentWindow || !frame.get(0).contentWindow.TemplateEditor){
		return;
	}

	try{
		if(ua.match('MSIE')){
			frame.get(0).contentWindow.document.getElementById("main").contentEditable = true;
		}else{
			frame.get(0).contentWindow.document.designMode = "On";
		}
		frame.get(0).inited = true;

	}catch(e){
		//do nothing
	}

}

//エディタからHTMLを取得
function template_editor_get_code(){
	var frame = $("#template_editor_frame");
	
	if(!frame.get(0).contentWindow || !frame.get(0).contentWindow.TemplateEditor){
		return $("#template_content").val();
	}

	return $("#template_editor_frame").get(0).contentWindow.TemplateEditor.getCode();
}

//テンプレートのみ保存
function save_template(url,ele){

	var toolbox = $("#template_toolbox");

	var loading;
	
	//ローディング
	if(ele != null){
		ele = $(ele);
		
		loading = $("<span/>");
		loading.attr("class","loading");
		loading.html("&nbsp;&nbsp;&nbsp;&nbsp;");
		
		ele.prop("disabled", true);
		ele.after(loading);
	}
	
	//AJAXで保存：soy2_tokenでこけたら5回までやり直す
	save_template_ajax(url,5,loading,ele);	
}

function save_template_ajax(url,trials,loading,ele){
	var content = sync_code();
	
	if(trials>0){
		$.ajax({
			url: url,
			type : "post",
			data : "template=" + encodeURIComponent(content) + "&soy2_token=" + $("#main_form").children('input[name=soy2_token]').val(),
			success : function(data){
				
					var res = eval("array="+data);
					
					if($("#main_form")){
						$("#main_form").children('input[name=soy2_token]').val(res.soy2_token);
					}
					if(res.text.match(/^0$/)){
						trials--;
						//soy2_tokenが古い場合に備えて何回かやり直す
						save_template_ajax(url,trials,loading,ele);
					}else{
						if($("#block_list")) $("#block_list").html(res.text);
												
						$(".loading").remove();
						ele.prop("disabled", false);
						
						//CSSが追加されたらCSS編集ボタンを表示する
						if(getStyleSheet().length != 0 && $("#cssButton")){
							$("#cssButton").show();
						}
					}
				}
		});
	}else{
		alert("保存に失敗しました");
	}
}

var CSSView;
$(function(){
	CSSView = new CSSList("cssMenu", "css_editor");
	CSSView.lineHeight = 12;
});
//$.event.add(window, "load", function(){
//	CSSView = new CSSList("cssMenu", "css_editor");
//	CSSView.lineHeight = 12;
//});

function changeImageIcon(id){
	common_element_to_layer($("#image_list"),
		{	width:200,
			height:150,
			targetId : "image_list_layer",
			onclose:function(){
				$("<div/>").appendChild($("#image_list"));
				$("#image_list").hide();
			}});
	$("#image_list").label_id = id;
	$("#image_list").show();
}

function setChangeLabelIcon(filename,fileAddress){
	$("#page_icon").val(filename);
	$("#page_icon_show").attr("src",fileAddress);
	
	common_close_layer_by_targetId("image_list_layer");
}