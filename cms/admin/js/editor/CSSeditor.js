var CSSEditor = function(){
	this.initialize(arguments[0],arguments[1]);
	
	this.addKeyMask({
		keyCode : "tab",
		callback : this.tabReplace
	
	});
	this.addMenu("togglesize",{
		label : "大",
		onclick :this.toggleSize
		});
	
	this.addMenu("toggleFontSize",{
		label : "文字大",
		onclick : this.toggleFontSize
		});
	
};

//Editorを継承
CSSEditor.prototype = Editor.prototype;

CSSEditor.prototype.tabReplace = function(editor,e){
	var selection = editor.textarea.getSelection();
	if(selection.start || selection.start == '0'){
		
		var selectionStart = selection.start;
		var selectionEnd  = editor.textarea.selectionEnd;
		
		editor.textarea.insertString(selectionStart,selectionStart,"\t");
		editor.textarea.focus();
		editor.textarea.setCursor(selectionStart+1,selectionStart+1);
		return false;
		
	}
}

CSSEditor.prototype.toggleSize = function(id,editor,obj){
	var button = document.getElementById(id);
	
	//初期
	if(button.toggleSize == undefined){
		button.toggleSize = false;
	}
	
	//文字列の切り替え
	if(button.toggleSize){
		button.innerHTML = "大";
		
		with(editor.textarea.style){
			width = editor.textarea.oldWidth  + "px";
			height = editor.textarea.oldHeight + "px";
		}
	}else{
		button.innerHTML = "小";

		editor.textarea.oldWidth = editor.textarea.offsetWidth;
		editor.textarea.oldHeight = editor.textarea.offsetHeight;
		
		with(editor.textarea.style){
			width = 640  + "px";
			height = 800 + "px";
		}
	}
	
	button.toggleSize = !button.toggleSize;
		
}

CSSEditor.prototype.toggleFontSize = function(id,editor,obj){
	var button = document.getElementById(id);
	
	if(button.toggleFontSize == undefined){
		button.toggleFontSize = "medium";
	}
	
	switch(button.toggleFontSize){
	
		case "big":
			editor.textarea.style.fontSize = "75%";
			button.innerHTML="文字中";
			button.toggleFontSize = "small"; 
			break;
		case "medium":
			editor.textarea.style.fontSize = "125%";
			button.innerHTML="文字小";
			button.toggleFontSize = "big"; 
			break;
		case "small":
			editor.textarea.style.fontSize = "100%";
			button.innerHTML="文字大";
			button.toggleFontSize = "medium"; 
			break;
	}

}