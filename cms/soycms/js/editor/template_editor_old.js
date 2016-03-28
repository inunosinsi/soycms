Event.observe(window,"load",function(){
	init();
});

var template_editor_configuration = {};

function setConfig(config){
	template_editor_configuration = config;
}

function init(){
	
	var textarea = $("template_content");
	textarea.onkeydown = function(e){
		if(!e)e = event;
	
		if(e.keyCode == 9){
			e.cancelBubble = true;
			e.returnValue = false;
			insertTab("template_content",e);
			return false;
		}
	}
	
	PanelManager.init("template_wrapper");
		
	if($("block_list")){
		PanelManager.getPanel("west").addTab("ブロック",$("block_list"),{deletable : false});
	}
	
	
	PanelManager.getPanel("west").addTab("HTML",textarea,{onresize : resizeTextArea, deletable : false});
	PanelManager.getPanel("west").show();
	
	textarea.style.position = "relative";
	textarea.style.width = "100%";
	textarea.style.height = "100%";
	
	$("template_content").onblur = function(){
		if($("template_content_preview_wrapper"))showPreview()
	}

	if(getStyleSheet().length == 0){
		$("cssButton").hide();
	}

}

function debug(str,flag){
	if(flag){
		$("debug").innerHTML += str + "<br>";
	}else{
		$("debug").innerHTML = str;
	}	
}

function showPreview(){

	var content = $("template_content").value;
	var is_appended = false;
	//iframeの追加
	if(!$("template_content_preview_wrapper")){
		var iframeWrapper = document.createElement("div");
		iframeWrapper.setAttribute("id","template_content_preview_wrapper");
		iframeWrapper.innerHTML = '<iframe id="template_content_preview" frameborder="0" style="width:100%;height:100%;background-color:white;border:none;" src="about:blank"></iframe>';
		document.body.appendChild(iframeWrapper);
		
		if($("block_list")){
			if($("block_list").panel_pos == "south"){
				PanelManager.inactiveSouthPanel();
			}
		}
		
		PanelManager.getPanel("east").addTab("Preview",iframeWrapper,{onactive : showPreview});
		
		if($("block_list")){		
			if($("block_list").panel_pos == "south"){
				PanelManager.activeSouthPanel();
			}
		}

		is_appended = true;
	}
	
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
	

	if($("css_list").value != "none"){
	
	}
	content += "<style type=\"text/css\">"+$("css_editor").value+"</style>";
	
	
	
	var frame = $("template_content_preview");
	frame.hide();
	var d = frame.contentWindow.document;
	
	d.clear();
	d.write(content);
	d.close();
	
	frame.show();
}
function scrollTextArea(line,pos){

	var tab_id = $("template_content").tab_id;
	var panel_pos = $(tab_id).panel_pos;
	
	PanelManager.getPanel(panel_pos).activeTab(tab_id);
	
	$("template_content").scrollTop = line * 12;

	var textarea = $("template_content");
	textarea.focus();
	
	if(document.all){//ie
		if(textarea.value.substring(0,pos).indexOf("\n") != -1){
			var step = textarea.value.substring(0,pos).match(/\n/g).length;
		}else{
			var step = 0;
		}
		
		var range = textarea.createTextRange();
		range.move('character', pos-step);
		range.select();
	}else{//other
		textarea.setSelectionRange(pos,pos);
	}
	
	textarea.focus();
}

function resizeTextArea(wrapper,container){

	var textarea = $("template_content");
	
	if(!wrapper)return;
	
	var diff = (is_ie) ? 0 : 2;
	
	textarea.style.width = wrapper.offsetWidth - diff + "px";
	textarea.style.height = wrapper.offsetHeight - diff + "px";
}

function resizeCSSEditArea(wrapper,container){

	var textarea = $("css_editor");
	
	if(!wrapper)return;
	
	textarea.style.width = wrapper.offsetWidth -175 + "px";
	textarea.style.height = wrapper.offsetHeight - 22 + "px";
}

function showCSSEditor(){
	var csslist = $("css_editarea");
	
	var styles = getStyleSheet();
	if(styles.length == 0){
		return;
	}
	while($("css_list").firstChild){
		$("css_list").removeChild($("css_list").firstChild);
	}
	
	var option = document.createElement("option");
	option.value = "none";
	option.innerHTML = "このページで使われているCSS一覧";
	$("css_list").appendChild(option);

	for(var i=0; i< styles.length; i++){
		var option = document.createElement("option");
		option.value = styles[i];
		option.innerHTML =styles[i];
		$("css_list").appendChild(option);
	}
	
	if(csslist.active)return;
	
	csslist.show();
	csslist.active = true;
	PanelManager.getPanel("south").addTab("CSS",csslist,{onresize : resizeCSSEditArea, onclose : function(){
		csslist.hide();
		csslist.active = false;
		document.body.appendChild(csslist);
	}});	
	
	$("css_editor").onblur = function(){
		if($("template_content_preview_wrapper"))showPreview()
	}
}

function showHelp(){
	var block_help_area = $("block_help_area");
	
	$("help_frame").src = helpURL;
	if(block_help_area.active)return;
	
	block_help_area.show();
	block_help_area.active = true;
	PanelManager.getPanel("east").addTab("ヘルプ",block_help_area,{ onclose : function(){
		block_help_area.hide();
		block_help_area.active = false;
		document.body.appendChild(block_help_area);
	}});	
	
}

function onSelectCSS(selectedValue){
		
	var link = selectedValue.replace(siteURL,"");

	new Ajax.Request(cssURL+"/GetCSS", {
		method: "post",
		parameters : "path=" + link,
		onSuccess:function(httpObj){
			$("css_editor").value = httpObj.responseText;
			CSSView.render();
		},
		onFailure:function(httpObj){
			alert("エラーで読み込めませんでした");
		}
	});
}

function getStyleSheet(){
	
	var content = $("template_content").value;
	
	var iframe = $("getstylesheet_iframe");
	if(!iframe){
		var iframe = document.createElement("iframe");
		iframe.setAttribute("id","getstylesheet_iframe");
		iframe.src = "about:blank";
		iframe.style.display = "none";
		document.body.appendChild(iframe);
		iframe.contentWindow.document.write('<html><head><base href="'+siteURL+'"/></head><body></body></html>');
		iframe.contentWindow.document.close();
	}
	
	var doc = iframe.contentWindow.document;
	
	iframe.contentWindow.document.getStyleSheet = function(){
		var styles = new Array();
		
		var regExp = new RegExp('<\(link[^>]*\)>',"gi");
		var relRegExp = new RegExp('rel\s*=\s*["\']([^"\']*)["\']',"i");
		var hrefRegExp = new RegExp('href\s*=\s*["\']([^"\']*)["\']',"i");
		
		while(rs = regExp.exec(content)){
			var link = rs[1];
			var rel = relRegExp.exec(link);
			if(!rel || !rel[1].match(/stylesheet/i))continue;
			
			var href = hrefRegExp.exec(link);
			
			var tmp = doc.createElement("img");
			tmp.src = href[1];
			
			if(tmp.src.indexOf(siteURL) == 0)styles.push(tmp.src);			
			
		}
		
		return styles;	
	};
	
	var styles = iframe.contentWindow.document.getStyleSheet();

	//document.body.removeChild(iframe);
		
	return styles;	
}

function saveCSS(){
	if($("css_list").value == "none"){
		return;
	}
	new Ajax.Request(cssURL+"/SaveCSS", {
		method: "post",	
		parameters : $("css_editor").serialize()+'&'+$("css_list").serialize(),
		onSuccess:function(httpObj){
			alert(httpObj.responseText);
		},
		onFailure:function(httpObj){
			alert("エラーで読み込めませんでした");
		}
	});

}

function insertTab(id,e){
	
	var textarea = $(id);
	
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
}

function insertHTML(html){
	var textarea = $("template_content");
	if (document.selection != null){
		textarea.focus();
		textarea.selection = document.selection.createRange();
		textarea.selection.text = html;
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
	if($("template_content_preview_wrapper")){
		showPreview();
	}
}