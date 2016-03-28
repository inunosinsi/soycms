var TemplateEditor = {
	
	scrolling : false,
	
	//initialize
	initialize  : function(){
		cc = '\u2009';
		editor = document.getElementById("main");
		//document.designMode = "on";
		document.addEventListener('keyup', this.keyHandler, true);
		window.addEventListener('scroll', function() { if(!TemplateEditor.scrolling) TemplateEditor.syntaxHighlight('scroll') }, false);
	},
	
	//textarea to frame
	setCode : function(html){
		html = html.replace(/&/g,"&amp;");
		html = html.replace(/</g,'&lt;');
		html = html.replace(/>/g,'&gt;');
		
		html = template_editor_replace(html);
		html = html.replace(/\n/g,"<br>");
		editor.innerHTML = "<pre>" + html + "</pre>";
	},
	
	//frame to texterea
	getCode : function(){
				
		var html = editor.innerHTML;
		html = html.replace(/\u2008/g,'\t');
		
		html = html.replace(/<br>/gi,"\n");
		html = html.replace(/<\/pre><pre>/gi,'\n');
		html = html.replace(/<\/p>/gi,'\n');
	
		html = html.replace(/<[^>]*>/g,"");
		
		//html = html.replace(/&lt;/g,'<');
		//html = html.replace(/&gt;/g,'>');
		//html = html.replace(/&nbsp;/g,' ');
		//html = html.replace(/&amp;/g,"&");
			
		return html;
	},
	
	//find cursor
	findString : function() {
		var sel = window.getSelection();
		var range = window.document.createRange();
		var span = window.document.getElementById('cursor');
		
		range.selectNode(span);
		sel.removeAllRanges();
		sel.addRange(range);
		span.setAttribute("id","");
	},
	
	
	//keyEvent
	keyHandler : function(e){
		
		var keyCode = e.keyCode;
		var charCode = e.charCode;
		var fromChar = String.fromCharCode(charCode);
					
		if(keyCode == 13){
			top.setTimeout(function(){TemplateEditor.syntaxHighlight();},100);	
		
		}else if(charCode==118 && e.ctrlKey)  { // handle paste
		 	top.setTimeout(function(){TemplateEditor.syntaxHighlight();},100);
		}
				
		return true;
	},
	
	syntaxHighlight : function(flag){
		
		if(flag != "scroll"){
			window.getSelection().getRangeAt(0).insertNode(document.createTextNode(cc));
		}
				
		var html = this.getEditor().innerHTML;
		
		html = html.replace(/<br>\u2009<\/span><\/span>&nbsp;/gi,"\n\u2009\n");
		html = html.replace(/<br>\u2009&nbsp;/gi,"\n\u2009\n");
		html = html.replace(/<br>/gi,"\n");
		
		html = html.replace(/<[^>]*>/g,"");
		
		x = z = this.split(html,flag);
		x = x.replace(/&lt;/g,'<');
		x = x.replace(/&gt;/g,'>');
		x = x.replace(/&nbsp;/g,' ');
		x = x.replace(/&amp;/g,"&");
		x = template_editor_replace(x);
		
		x = x.replace(/\n/g,"<br>");
		
		if(x.match(/\u2009/)){
			x = x.replace(/\u2009(<br>)?/i,"<span id=\"cursor\"><br></span>")
		}
		
		if(flag == "scroll"){
			editor.innerHTML = "<pre>" + x + "</pre>";
		}else{
			editor.innerHTML = "<pre>" + html.split(z).join(x) + "</pre>";
		}
		
		this.findString();
	},
	
	getEditor : function(){
		var main = document.getElementById("main");
		
		if(!main){
			main = document.createElement("div");
			main.setAttribute("id","main");
			var html = document.body.innerHTML;
			document.body.innerHTML = "<div id=\"main\">" + document.body.innerHTML + "</div>";
			main = document.getElementById("main");
		}
		
		editor = main;
		return editor;
	},
	
	split : function(code, flag){
		
		//スクロール時は全て
		if(flag == "scroll"){
			this.scrolling = true;
			return code;
		}
		
		this.scrolling = false;
		
		var mid = code.indexOf(cc);
		if(mid-2000<0) {ini=0;end=4000;}
		else if(mid+2000>code.length) {ini=code.length-4000;end=code.length;}
		else {ini=mid-2000;end=mid+2000;}
		code = code.substring(ini,end);
		return code;
	},
	
	insertCode : function(code,replaceCursorBefore){
		var range = window.getSelection().getRangeAt(0);
		var node = window.document.createTextNode(code);
		var selct = window.getSelection();
		var range2 = range.cloneRange();
		// Insert text at cursor position
		selct.removeAllRanges();
		range.deleteContents();
		range.insertNode(node);
		// Move the cursor to the end of text
		range2.selectNode(node);		
		range2.collapse(replaceCursorBefore);
		selct.removeAllRanges();
		selct.addRange(range2);
	}

};

window.addEventListener('load', function() { TemplateEditor.initialize(); }, true);