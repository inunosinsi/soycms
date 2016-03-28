var TemplateEditor = {

	scrolling : false,
	saveRange :null,

	//initialize
	initialize  : function(){

		cc = '\u2009';
		editor = document.getElementById("main");
		document.attachEvent('onkeydown', this.metaHandler);
		document.attachEvent('onkeypress', this.keyHandler);

		var inst = this;
		document.attachEvent('onkeyup', function(){
			inst.saveRange = document.selection.createRange();
		});

		window.attachEvent('onscroll', function() { if(!TemplateEditor.scrolling) TemplateEditor.syntaxHighlight('scroll');});
	},

	//textarea to frame
	setCode : function(html){
		html = html.replace(/&/g,"&amp;");
		html = html.replace(/</g,'&lt;');
		html = html.replace(/>/g,'&gt;');

		html = template_editor_replace(html);
		editor.innerHTML = "<pre>" + html + "</pre>";
	},

	//frame to texterea
	getCode : function(){

		var old_innerHTML = editor.innerHTML;
		var html = old_innerHTML;

		html = html.replace(/\u2008/g,'\t');

		html = html.replace(/<br>/gi,"\n");
		html = html.replace(/<\/pre><pre>/gi,'\n');
		html = html.replace(/<\/p>/gi,'\n');

		html = html.replace(/<[^>]*>/g,"");

		html = html.replace(/&lt;/g,'<');
		html = html.replace(/&gt;/g,'>');
		html = html.replace(/&nbsp;/g,' ');
		html = html.replace(/&amp;/g,"&");

		editor.innerHTML = old_innerHTML;

		return html;
	},

	//find cursor
	findString : function() {
		range = self.document.body.createTextRange();
		if(range.findText(cc)){
			range.select();
			range.text = '';
		}
	},

	//keyEvent before press
	metaHandler : function(){
		var e = event;
		var keyCode = e.keyCode;

		if(keyCode == 86 && e.ctrlKey)  { // handle paste
			var str = window.clipboardData.getData('Text');

			window.clipboardData.setData('Text',str.replace(/\t/g,'\u2008'));
			setTimeout(function(){TemplateEditor.syntaxHighlight();},100);
		}
	},

	//keyEvent
	keyHandler : function(){
		var e = event;
		var keyCode = e.keyCode;

		if(keyCode == 13){
			setTimeout(function(){TemplateEditor.syntaxHighlight();},100);

		}else if((keyCode==122||keyCode==121||keyCode==90) && e.ctrlKey) { // undo and redo
			e.returnValue = false;

		}else if(keyCode == 9){ // handle tab

			var d = document;

			d.getElementById("main").focus();
			d.selection.createRange().text = "\u2008\u2009";

			setTimeout(function(){TemplateEditor.syntaxHighlight('tab');},100);

			return false;
		}

		return true;

	},

	// split big files, highlighting parts of it
	split : function(code,flag) {
		if(flag=='scroll') {
			this.scrolling = true;
			return code;
		}

		this.scrolling = false;
		mid = code.indexOf(cc);
		if(mid - 2000 < 0) {
			ini = 0;
			end = 4000;
		}else if(mid+2000>code.length){
			ini=code.length-4000;
			end=code.length;
		}else {
			ini=mid-2000;
			end=mid+2000;
		}

		return code = code.substring(ini,end);
	},

	syntaxHighlight : function(flag){
		document.selection.createRange().text = cc;

		//remove a tag(on paste)
		var elements = document.getElementsByTagName("a");
		for(var i=0,l=elements.length;i<l;i++){
			var span = document.createElement("span");
			span.innerHTML = elements[i].innerHTML;
			elements[i].parentNode.insertBefore(
				span,
				elements[i]
			);
			elements[i].parentNode.removeChild(elements[i]);
		}


		var html = editor.innerHTML;

		html = html.replace(/<a[^>]+>/ig,"");
		html = html.replace(/<\/a>/ig,"");
		html = html.replace(/\u2008/g,'\t');
		html = html.replace(/<br>/gi,"\n");
		html = html.replace(/<\/pre><pre>/gi,'\n');
		html = html.replace(/<\/p>/gi,'\n');
		html = html.replace(/<[^>]*>/g,"");



		x = z = this.split(html,flag);

		//x = x.replace(/&lt;/g,'<');
		//x = x.replace(/&gt;/g,'>');
		//x = x.replace(/&nbsp;/g,' ');
		//x = x.replace(/&amp;/g,"&");

		x = template_editor_replace(x);

		editor.innerHTML = "<pre>" + html.replace(z,x) + "</pre>";

		this.findString();
	},

	insertCode : function(code,replaceCursorBefore) {
		var repdeb = '';
		var repfin = '';

		if(replaceCursorBefore) { repfin = code; }
		else { repdeb = code; }

		if(typeof document.selection != 'undefined') {
			if(this.saveRange){
				this.saveRange.select();
				this.saveRange = null;
			}
			editor.focus();
			var range = document.selection.createRange();
			range.text = repdeb + repfin;
			range = document.selection.createRange();
			range.move('character', -repfin.length);
			range.select();
		}
	}

};

window.attachEvent('onload', function() { TemplateEditor.initialize();});