var TemplateEditor = {

	scrolling : false,

	//initialize
	initialize  : function(){
		editor = ace.edit("main");
		editor.setFontSize(16);
		editor.session.setUseSoftTabs(false);
		editor.getSession().setMode("ace/mode/html");
	},

	//textarea to frame
	setCode : function(html){
		editor.setValue(html, -1);
	},

	//frame to texterea
	getCode : function(){
		return editor.getValue();
	},

	//find cursor
	findString : function() {},

	//keypress
	keyHandler : function(e){
		return true;
	},

	//keyUp
	keyupHandler : function(e){},
	syntaxHighlight : function(flag){},

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

	split : function(code, flag){},
	insertCode : function(code,replaceCursorBefore){
		editor.insert(code);
	}
};

window.addEventListener('load', function() { TemplateEditor.initialize(); }, false);
