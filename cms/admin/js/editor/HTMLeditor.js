var toolbar = [
	{tag : "h1",alter : "h1",insertText : "H1"},
	{tag : "h2",alter : "h2",insertText : "H2"},
	{tag : "h3",alter : "h3",insertText : "H3"},
	{tag : "h4",alter : "h4",insertText : "H4"},
	{tag : "h5",alter : "h5",insertText : "H5"},
	{tag : "h6",alter : "h6",insertText : "H6"},
	{tag : "p",alter : "p",insertText : "Paragraph"},
	{tag : "b",alter : "b",insertText : "Bold"},
	{tag : "i",alter : "i",insertText : "Itaric"},
	{tag : "a",alter : "a",insertText : "Anchor"},
	{tag : "div",alter : "div",insertText : "Div"}
];

var wysiwygToolbar = [
	{label : "<b>太</b>",alter : "Bold",command : "Bold"},
	{label : "<i>斜</i>",alter : "Italic",command : "Italic"},
	{label : "<u>下線</u>",alter : "UnderLine",command : "Underline"},
	{label : "リスト",alter : "InsertUnorderedList",command : "InsertUnorderedList"},
	{label : "番号付きリスト",alter : "InsertOrderedList",command : "InsertOrderedList"}
];

var HTMLEditor = function(){
	
	this.initialize(arguments[0],arguments[1]);
	
	for(var i=0;i<toolbar.length;i++){
		this.addMenu(toolbar[i].tag,{
			label : toolbar[i].insertText,
			onclick : this.wrapTag,
			onclick_arg : {
				tag : toolbar[i].tag,
				insertText : toolbar[i].insertText
			}
		});
	}
	
	for(var i=0;i<wysiwygToolbar.length;i++){
		this.addMenu("wysiwyg_toolbar_button" + i,{
			label : wysiwygToolbar[i].label,
			onclick : this._execCommand,
			onclick_arg : {
				command : wysiwygToolbar[i].command
			}
		},1);
	}
	
	this.addMenu("preview",{
		label : "preview",
		onclick :this.clickPreview
		},0);
	
	this.addKeyMask({
		keyCode : " ".charCodeAt(0),
		ctrl : true,
		callback : this.ctrlSpace
		});
	
	this.addKeyMask({
		keyCode : "tab",
		callback : this.tabReplace
	});
	
	this.addKeyMask({
		keyCode : "S".charCodeAt(0),
		ctrl : true,
		callback : function(editor,e){e.cancelBubble=true;$(editor.id).form.submit();return false;}
	});
	
	this.addMenu("preview_return",{
		label : "戻",
		onclick :this.clickPreview
		},1);
	
	this.addMenu("insert_link",{
		label : "Link",
		onclick : this.openInsertLink
	});
	
	this.addMenu("wysiwyg_toolbar_button" + i,{
		label : "Link",
		onclick : this.openInsertLink
	},1);
	
	//ツールバーを隠す
	this.hideToolbar(1);
	
	var editor = this;
	window.getEditor = function(){
		return editor;
	}
};

//Editorを継承
HTMLEditor.prototype = Editor.prototype;

HTMLEditor.prototype.closeTag = function(id,editor,obj){
		
		var value = $(editor.id).value;
		var selection = editor.getSelectionRange();
		var selectionStart = selection.start;
		var pos = selectionStart;
		var flag = false;
		var tag = "";
		var tmpCloseTag = "";
		
		while(pos>0){
			if(value.charAt(pos) == ">"){
				flag = true;
			}else if(flag && value[pos] != "<"){
				tag = value.charAt(pos) + tag;
			}
			
			if(flag && value.charAt(pos) == "<"){
				flag = false;
				break;
			}
			
			if(flag && value.charAt(pos) == "/"){
				flag = false;
				tag = "";
			}
			
			pos--;
		}
		
		if(tag.indexOf(" ") != -1)tag = tag.substring(0,tag.indexOf(" "));
		tag = "</" + tag + ">";
		
		editor.insertString(selectionStart,selectionStart,tag);
		$(editor.id).focus();
		editor.setSelectionRange(selectionStart+tag.length,selectionStart+tag.length);	
};

HTMLEditor.prototype.dummyLinkAddr = '##internalLinkage##';
HTMLEditor.prototype.wysiwygMode = false;
HTMLEditor.prototype.clickPreviewWYSIWYGMode = function(id,editor,obj){
	
	if(this.wysiwygMode == false){		
		this.wysiwygMode = true;
		
		//いらないものを隠す
		$(this.id + "_wrapper").hide();
		$(this.id + "_status").hide();
		$(this.id + "_wrapper").parentNode.appendChild($(this.id));
		
		editor.hideToolbar(0);
		editor.showToolbar(1);
		
		if(!this.tinyMCE){
			this.tinyMCE = new tinymce.Editor(
				"template_content",{
				// General options
				mode : "textareas",
				theme : "advanced",
				plugins : "safari,pagebreak,style,layer,table,save,advhr,advimage,advlink,emotions,iespell,inlinepopups,insertdatetime,preview,media,searchreplace,print,contextmenu,paste,directionality,fullscreen,noneditable,visualchars,nonbreaking,xhtmlxtras,template",
		
				// Theme options
				theme_advanced_buttons1 : "save,newdocument,|,bold,italic,underline,strikethrough,|,justifyleft,justifycenter,justifyright,justifyfull,|,styleselect,formatselect,fontselect,fontsizeselect",
				theme_advanced_buttons2 : "cut,copy,paste,pastetext,pasteword,|,search,replace,|,bullist,numlist,|,outdent,indent,blockquote,|,undo,redo,|,link,unlink,anchor,image,cleanup,help,code,|,insertdate,inserttime,preview,|,forecolor,backcolor",
				theme_advanced_buttons3 : "tablecontrols,|,hr,removeformat,visualaid,|,sub,sup,|,charmap,emotions,iespell,media,advhr,|,print,|,ltr,rtl,|,fullscreen",
				theme_advanced_buttons4 : "insertlayer,moveforward,movebackward,absolute,|,styleprops,|,cite,abbr,acronym,del,ins,attribs,|,visualchars,nonbreaking,template,pagebreak",
				theme_advanced_toolbar_location : "top",
				theme_advanced_toolbar_align : "left",
				theme_advanced_statusbar_location : "bottom",
				theme_advanced_resizing : true,
		
				// Example content CSS (should be your site CSS)
				content_css : "css/content.css",
		
				// Drop lists for link/image/media/template dialogs
				template_external_list_url : "lists/template_list.js",
				external_link_list_url : "lists/link_list.js",
				external_image_list_url : "lists/image_list.js",
				media_external_list_url : "lists/media_list.js",
		
				// Replace values for the template plugin
				template_replace_values : {
					username : "Some User",
					staffid : "991234"
				},
				
				force_br_newlines :false,
				force_p_newlines : false,
				forced_root_block :false,
				remove_linebreaks : false,
				apply_source_formatting :true,
				verify_html : false,
				preformatted : true,
				encoding : 'xml'
			});
			
			var content = $(this.id).value;
			
			this.tinyMCE.onSetContent.add(function(editor,o){
				if((r1 = content.match(/<body[^>]*>/ig)) && (r2 = content.match(/<\/body>/ig))){
					var start = content.indexOf(r1[0]) + r1[0].length;
					var end = content.indexOf(r2[0]);
					
					head = content.substring(0,start);
					content = content.substring(start,end);
				}
				
				o.content = '<!--cms_header-->' + head + '<!--/cms_header-->' + content;
			});
			
			var editor = this;
			
			this.tinyMCE.onSubmit.add(function(ed, e) {
				e.cancelBubble = true;
				editor.clickPreviewWYSIWYGMode(id,editor,obj);				
				return false;
      		});
			
			this.tinyMCE.render();
		}else{
			this.tinyMCE.show();
		}
		
		return;
	}
	
	$(this.id + "_wrapper").appendChild($(this.id));
	$(this.id + "_wrapper").show();
	$(this.id + "_status").show();
	
	var bodyHTML = this.tinyMCE.getContent();

	var content = $(this.id).value;
	if((r1 = content.match(/<body[^>]*>/ig)) && (r2 = content.match(/<\/body>/ig))){
		var start = content.indexOf(r1[0]) + r1[0].length;
		var end = content.indexOf(r2[0]);
		
		var pre_body = content.substring(0,start);
		var post_body = content.substring(end,content.length);
		
		content = pre_body + "\n" + bodyHTML +  "\n" + post_body;
	}
	
	this.tinyMCE.hide();
	this.wysiwygMode = false;
	
	$(this.id).value = content;
		
	editor.hideToolbar(1);
	editor.showToolbar(0);
	
	return;
};

HTMLEditor.prototype.clickPreview = function(id,editor,obj){
		
		var content = $(editor.id).value;
		
		//wysiwyg mode on
		if((r1 = content.match(/<body[^>]*>/ig)) && (r2 = content.match(/<\/body>/ig))){
			
			if(editor.wysiwygMode == true){
				return editor.clickPreviewWYSIWYGMode(id,editor,obj);
			}
			
			
			if(confirm("WYSIWYGモードで編集しますか？\nHTMLが崩れる可能性があります。") != false){
				return editor.clickPreviewWYSIWYGMode(id,editor,obj);
			}
		}
		
		if(!$(editor.id + "_preview")){
			
			//previewのiframeを作成
			var iframe = document.createElement("iframe");
			iframe.setAttribute("id",editor.id + "_preview");
			iframe.style.position = "absolute";
			iframe.style.top = $(editor.id + "_wrapper").offsetTop + "px";
			iframe.style.left = $(editor.id + "_wrapper").offsetLeft + "px";
			iframe.style.width = $(editor.id + "_wrapper").offsetWidth + "px";
			iframe.style.height = $(editor.id + "_wrapper").offsetHeight + "px";
			iframe.style.border = "none";
			iframe.style.visbility = "hidden";
			iframe.src = "about:blank";
			
			$(editor.id + "_wrapper").parentNode.appendChild(iframe);
		}		
				
		var frame = $(editor.id + "_preview");
		
		if(frame.alreadyShow){
			frame.style.visibility = "hidden";
			$(editor.id + "_wrapper").style.visibility = "visible";
			frame.alreadyShow = false;
			return;
		}
		
		var d = null;
		
		if (document.all) {
			d = frame.contentWindow.document;
		} else {
			d= frame.contentDocument;
		}
		
		d.clear();
		d.write($(editor.id).value);
		d.close();
		
		frame.style.visibility = "visible";
		$(editor.id + "_wrapper").style.visibility = "hidden";
		frame.alreadyShow = true;
}

HTMLEditor.prototype.ctrlSpace = function(editor,e){
		var selection = editor.getSelectionRange();
		var selectionStart = selection.start;
		var selectionEnd  = selection.end;
		var pos = selectionStart-1;
		var value = $(editor.id).value;
		
		var flag = null;
		while(pos >= 0){
			if(value.charAt(pos) == "<"){
				flag = true;
				break;
			}
			if(value.charAt(pos) == ">"){
				flag = false;
				break;
			}
			pos--;
		}
		if(flag){
			var substr = $(editor.id).value.substring(pos+1,selectionStart);

			if(substr.indexOf("block:id") == -1){
				
				if($(editor.id).value.charAt(selectionStart-1) == " "){
					var soyid = 'block:id=""';
				}else{
					var soyid = ' block:id=""';
				}
				editor.insertString(selectionStart,selectionStart,soyid);
				$(editor.id).focus();
				editor.setSelectionRange(selectionStart+(soyid.length)-1,selectionStart+(soyid.length)-1);
				
			}else{
				if($(editor.id).value.charAt(selectionStart) == '"'){
					var elements = substr.split(" ");
					if(elements.length == 0)
						return ;
					if(substr.charAt(0) != "/")
						var closeTag = "</"+elements[0]+">";
					else
						var closeTag = "";
					editor.insertString(selectionStart+1,selectionStart+1,">"+closeTag);
					$(editor.id).focus();
					editor.setSelectionRange(selectionStart+2,selectionStart+2);
				}else{
					var elements = substr.split(" ");
					if(elements.length == 0)
						return ;
					if(substr.charAt(0) != "/")
						var closeTag = "</"+elements[0]+">";
					else
						var closeTag = "";
					editor.insertString(selectionStart,selectionStart,">"+closeTag);
					$(editor.id).focus();
					editor.setSelectionRange(selectionStart+1,selectionStart+1);
				}
			}
			e.cancelBubble = true;
			return false;
		}else if(flag != null){
			editor.closeTag(null,editor,null);
			e.cancelBubble = true;
			return false;
		}
}

HTMLEditor.prototype.wrapTag = function(id,editor,obj){
	var openTag = "<"+obj.tag+">";
	var endTag = '</'+obj.tag+">";
	
	var selection = editor.getSelectionRange();
	
	if(selection.start || selection.start == '0'){
		
		var selectionStart = selection.start;
		var selectionEnd  = selection.end;
		
		var selectedContents = $(editor.id).value.substring(selectionStart,selectionEnd);
		
		if(selectedContents){
			var insertString = openTag + selectedContents + endTag;
		}else{
			var insertString = openTag + obj.insertText + endTag;
		}
		
		editor.insertString(selectionStart,selectionEnd,insertString)
		$(editor.id).focus();
		editor.setSelectionRange(selectionStart+insertString.length,selectionStart+insertString.length);			
	}
}

HTMLEditor.prototype.tabReplace = function(editor,e){
	var selection = editor.getSelectionRange();
	if(selection.start || selection.start == '0'){
		
		
		var selectionStart = selection.start;
		var selectionEnd  = selection.end;
		
		editor.insertString(selectionStart,selectionStart,"\t");
		editor.setSelectionRange(selectionStart+1,selectionStart+1);
		$(editor.id).focus();
		return false;
	}
}

HTMLEditor.prototype._execCommand = function(id,editor,obj){
	$(editor.id + "_previewWYSIWYG").contentWindow.document.execCommand(obj.command, false, null);
	$(editor.id + "_previewWYSIWYG").focus();
}

HTMLEditor.prototype.execCommand = function(command,flag,arg){
	$(this.id + "_previewWYSIWYG").contentWindow.document.execCommand(command, flag, arg);
	$(this.id + "_previewWYSIWYG").focus();
}

HTMLEditor.prototype.changeFullScreen = function(id,editor,obj){
	//未実装
}

HTMLEditor.prototype.openInsertLink = function(id,editor,obj){
	common_to_layer(InsertLinkAddress);
	common_get_layer().editor = editor;
}

var tools = {
	
	findLeftFirstTag : function(cursor){
		var textarea = $(editor.id);
		
		var value = textarea.value;
		var pos = cursor-1;
		var result = {tag:"",tagStart:null,tagEnd:null,isEnd:null};
		
		var inTag = false;
		var tagEnd = 0;
		var resTag = "";
		while(pos > 0){
			if(value.charAt(pos) == ">"){
				inTag = true;
				tagEnd = pos;
			}else if(inTag && value.charAt(pos) == "<"){
				break;
			}else if(inTag){
				resTag = value.charAt(pos) + resTag;
			}
			pos--;
		}
		//�^�O���Ȃ��B�
		if(pos < 0)
			return result;

		var attr = resTag.split(" ");
		var top = attr[0];
		if(top.charAt(0) == "/"){
			result.tag = top.substring(1,top.length);
			result.tagStart = pos;
			result.tagEnd = tagEnd;
			result.isEnd = true;
		}else{
			result.tag = top;
			result.tagStart = pos;
			result.tagEnd = tagEnd;
			result.isEnd = false;
		}
		return result;
	},
	
	findRightFirstTag : function(cursor){
		var textarea = $(editor.id);
		
		var value = textarea.value;
		var pos = cursor;
		var result = {tag:"",tagStart:null,tagEnd:null,isEnd:null};
		
		var inTag = false;
		var tagPos = 0;
		var resTag = "";
		while(pos < value.length){
			if(value.charAt(pos) == "<"){
				inTag = true;
				tagPos = pos;
			}else if(inTag && value.charAt(pos) == ">"){
				break;
			}else if(inTag){
				resTag += value.charAt(pos);
			}
			pos++;
		}
		//�^�O���Ȃ��B�
		if(pos < 0)
			return result;

		var attr = resTag.split(" ");
		var top = attr[0];
		if(top.charAt(0) == "/"){
			result.tag = top.substring(1,top.length);
			result.tagStart = tagPos;
			result.tagEnd = pos;
			result.isEnd = true;
		}else{
			result.tag = top;
			result.tagStart = tagPos;
			result.tagEnd = pos;
			result.isEnd = false;
		}
		return result;
	
	},
	
	isInTag :  function(cursor){
		var textarea = $(editor.id);
		var value = textarea.value;
		var pos = cursor-1;
		var result = {tag:"",tagStart:null,tagEnd:null,isEnd:null};
				
		while(pos >= 0){
			if(value.charAt(pos) == ">"){
				return false;
			}else if(value.charAt(pos) == "<"){
				var tagPos = cursor;
		
				while(tagPos < value.length){
					if(value.charAt(tagPos) == ">"){
						break;
					}else if(tagPos != cursor && value.charAt(tagPos) == "<"){
						tagPos = null;
						break;
					}
					tagPos++;
				}
				
				if(tagPos == value.length)
					tagPos = null;
				
				if(tagPos){
					var innertag = value.substring(pos+1,tagPos);
				}else{
					var innertag = value.substring(pos+1,value.length);
				}
				
				if(innertag.length == 0){
					return result;
				}
				var eles = innertag.split(" ");
				var top = eles[0];
				if(top.charAt(0) == "/"){
					result.tag = top.substring(1,top.length);
					result.tagStart = pos;
					result.tagEnd = tagPos;
					result.isEnd = true;
				}else{
					result.tag = top;
					result.tagStart = pos;
					result.tagEnd = tagPos;
					result.isEnd = false;
				}
				return result;
			}
			pos--;
		}
		
		return false;
		
	}


}
