var TemplateEditor = {
	
	scrolling : false,
	
	//initialize
	initialize  : function(){
		cc = '\u2009';
		editor = $("#main");
		editor.css("whiteSpace","pre");
		
		//document.designMode = "on";
		document.addEventListener('keypress', this.keyHandler, true);
		document.addEventListener('keyup', this.keyupHandler, true);
		window.addEventListener('scroll', function() { if(!TemplateEditor.scrolling) TemplateEditor.syntaxHighlight('scroll') }, false);
		
		//focus 
		document.body.addEventListener('focus', function(){editor.focus();}, true);
		editor.prop("onblur",function(){return false;});
	},
	
	//textarea to frame
	setCode : function(html){
		html = html.replace(/&/g,"&amp;");
		html = html.replace(/</g,'&lt;');
		html = html.replace(/>/g,'&gt;');
		
		html = template_editor_replace(html);
		
		editor.html(html);
	},
	
	//frame to texterea
	getCode : function(){
				
		var html = editor.html();
		html = html.replace(/\u2008/g,'\t');
		
		html = html.replace(/<br>/g,"\n");
		html = html.replace(/<\/pre><pre>/gi,'\n');
		html = html.replace(/<\/p>/gi,'\n');
		
		html = html.replace(/<[^>]*>/g,"");
		
		html = html.replace(/&lt;/g,'<');
		html = html.replace(/&gt;/g,'>');
		html = html.replace(/&nbsp;/g,' ');
		html = html.replace(/&amp;/g,"&");
			
		return html;
	},
	
	//find cursor
	findString : function() {
		if(self.find(cc)){
			window.getSelection().getRangeAt(0).deleteContents();
		}
	},
	
	
	//keypress
	keyHandler : function(e){
		
		var keyCode = e.keyCode;
		var charCode = e.charCode;
		var fromChar = String.fromCharCode(charCode);
					
		if(keyCode == 13){
			top.setTimeout(function(){TemplateEditor.syntaxHighlight();},300);	
		}
		
		//push tab
		if(keyCode == 9){ // handle tab
			if (e.stopPropagation) e.stopPropagation();
			if (e.preventDefault) e.preventDefault();
			
			TemplateEditor.insertCode("\t",false);
			
			return false;
			
		}
		
		return true;
	},
	
	//keyUp
	keyupHandler : function(e){
		var keyCode = e.keyCode;
		
		if(keyCode == 118 && e.ctrlKey)  { // handle paste
		 	top.setTimeout(function(){TemplateEditor.syntaxHighlight("paste");},200);
		}
	},
	
	syntaxHighlight : function(flag){
		
		if(flag != "scroll"){
			window.getSelection().getRangeAt(0).insertNode(document.createTextNode(cc));
		}
				
		var html = this.getEditor().html();
		html = html.replace(/<br>\u2009<\/span><\/span>&nbsp;/g,"\n\u2009\n");
		html = html.replace(/<br>\u2009&nbsp;/g,"\n\u2009\n");
		html = html.replace(/<br>/g,"\n");
		html = html.replace(/<[^>]*>/g,"");
		
		if(flag != "scroll"){
			//alert(html.replace(/&nbs/")replace(/\n/g,"\\n\n"));
		}
				
		x = z = this.split(html,flag);
		//x = x.replace(/&lt;/g,'<');
		//x = x.replace(/&gt;/g,'>');
		//x = x.replace(/&nbsp;/g,' ');
		//x = x.replace(/&amp;/g,"&");
		
		x = template_editor_replace(x);
		
		if(flag == "scroll"){
			editor.html(x);
		}else{
			editor.html(html.split(z).join(x));
		}
		
		this.findString();
	},
	
	getEditor : function(){
		var main = $("#main");
		
		if(!main){
			main = $("<div/>");
			main.attr("id","main");
			var html = $("body").html();
			$("body").html("<div id=\"main\">" + document.body.innerHTML + "</div>");
			main = ("#main");
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

window.addEventListener('load', function() { TemplateEditor.initialize(); }, false);