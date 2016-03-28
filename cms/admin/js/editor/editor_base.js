var Editor = function(){
	this.initialize(arguments[0]);
	this.render();
};

Editor.prototype = {
	
	id : null,
	menu:null,
	search_counter:0,
	lineHeight:20,
	fontSize:15,
	keyMask : null,
	textarea : null,
	init : {},
	menucounter : 0,
	toolbar : [],

	initialize:function(id,initParam){
		this.id = id;
		this.menu = new Array();
		
		if(initParam){
			this.init = initParam;
		}else{
			this.init = {};
		}
	},
	
	addMenu:function(id,obj,depth){
	
		if(!depth){
			depth = 0;
		}
		
		if(!this.menu[depth]){
			this.menu[depth] = new Array();
		}
		
		var icon = document.createElement("button");
		
		icon.id = id;
		icon.obj = obj;

		icon.editor = this;
		
		//for not ie
		if(!document.all){
			icon.setAttribute("type","button");
		}
		icon.setAttribute("type","button");
		
		icon.className = "button";
		icon.onmouseover = function(e){
			this.className = "buttonOver";
		}
		icon.onmouseout = function(e){
			this.className = "button";
		}
		
		//callbackが設定されていたならば
		if(obj.onclick != undefined){
			if(!obj.onclick_arg){
				obj.onclick_arg = {};
			}
			icon.onclick = function(){this.obj.onclick(this.id,this.editor,this.obj.onclick_arg)};
		}
		
		//ラベルが無ければIDを付与
		if(obj.label){
			icon.innerHTML = obj.label;
		}else{
			icon.appendChild(document.createTextNode(id));
		}

		this.menu[depth].push(icon);
		
	},
	
	addKeyMask : function(obj){
		if(!this.keyMask){
			this.keyMask = new Array();
		}
		
		var pushObj = {};
		
		if(obj.keyCode){
			if(typeof(obj.keyCode) == "string"){
				switch(obj.keyCode.toLowerCase()){
					case "backspace":pushObj.keyCode =  8;break;
					case "tab"      :pushObj.keyCode =  9;break;
					case "enter"    :pushObj.keyCode = 13;break;
					case "shift"    :pushObj.keyCode = 16;break;
					case "ctrl"     :pushObj.keyCode = 17;break;
					case "alt"      :pushObj.keyCode = 18;break;
					case "pageup"   :pushObj.keyCode = 33;break;
					case "pagedown" :pushObj.keyCode = 34;break;
					case "end"      :pushObj.keyCode = 35;break;
					case "home"     :pushObj.keyCode = 36;break;
					case "insert"   :pushObj.keyCode = 45;break;
					case "delete"   :pushObj.keyCode = 46;break;
					default:
						pushObj.keyCode = obj.keyCode.charCodeAt(0);
						return;
				}
			}else{
				pushObj.keyCode = obj.keyCode;
			}
		}else{
			pushObj.keyCode = null;
		}
		
		if(obj.ctrl){
			pushObj.ctrl = true;
		}else{
			pushObj.ctrl = false;
		}
		
		if(obj.alt){
			pushObj.alt = true;
		}else{
			pushObj.alt = false;
		}
		
		if(obj.shift){
			pushObj.shift = true;
		}else{
			pushObj.shift = false;
		}
		
		if(obj.callback){
			pushObj.callback = obj.callback;
		}else{
			alert("[Editor:addKeyMask]call back function must exist.");
			return;
		}
		
		this.keyMask.push(pushObj);
	},
	
	/**
	 * rendering html
	 */
	render:function(){
		
		var id = this.id;
		
		if(!$(id))return;
		
		$(id).style.fontSize = this.fontSize + "px";
		$(id).style.lineHeight = this.lineHeight + "px";
		$(id).style.border = "none";
		
	  	if(document.layers)document.captureEvents(Event.KEYPRESS);

		var tmp = $(id).value;
		var line = Math.max(tmp.replace("\r","\n").split("\n").length,1);
		
		var totalwrapper = document.createElement("div");
		totalwrapper.style.position = "relative";
		$(id).parentNode.appendChild(totalwrapper);
		
		var wrapper = document.createElement("div");
		wrapper.setAttribute("id", this.id + "_wrapper");
		wrapper.style.width=$(id).offsetWidth + 1 + "px";
		wrapper.style.height=$(id).offsetHeight + 1 + "px";
		wrapper.style.overflow = "scroll";
		wrapper.style.position = "relative";
		wrapper.style.backgroundColor = "white";
		wrapper.style.zIndex = "0";
		
		totalwrapper.appendChild(wrapper);
		wrapper.appendChild($(id));
		
		$(id).style.width = (wrapper.offsetWidth - 30) + "px";
		$(id).style.lineHeight = this.lineHeight + "px";
		$(id).style.height = this.lineHeight * line + "px";
		$(id).style.width = $(id).offsetWidth + "px";
		$(id).style.position = "absolute";
		$(id).style.overflow = "hidden";
		$(id).style.left = "30px";
		$(id).style.fontSize = this.fontSize + "px";
		$(id).style.lineHeight = this.lineHeight + "px";
		$(id).style.backgroundColor = "#CCCCCC";
		$(id).setAttribute("wrap","off");
		
		//line
		var lines = document.createElement("div");
		lines.setAttribute("id",id + "_lines");
		lines.style.width  = "20px";
		lines.style.cssFloat = "left";
		lines.style.styleFloat = "left";		
		lines.style.textAlign = "right";
		lines.style.paddingRight = "3px";
		lines.style.fontSize = "15px";
		lines.style.lineHeight = this.lineHeight + "px";
		lines.style.overflow = "hidden";
		lines.style.backgroundColor = "#ccffff";
		
		lines.line = line;
		for(var i=1; i<=line; i++){
			lines.innerHTML += i + "<br>";
		}
		
		wrapper.insertBefore(lines,$(id));
		
		//menubar
		for(var i=0;i<this.menu.length;i++){
			
			var toolbar = document.createElement("div");
			toolbar.setAttribute("id",id + "_toolbar_" + i);
			toolbar.className = "toolbar";
			toolbar.style.width= wrapper.offsetWidth + "px";
			
			if(this.toolbar[i] == "none"){
				toolbar.style.display = "none";
			}
			
			for(var j=0;j<this.menu[i].length;j++){
				toolbar.appendChild(this.menu[i][j]);			
			}
			
			wrapper.parentNode.insertBefore(toolbar,wrapper);
		}

		//statusbar
		var status = document.createElement("div");
		status.setAttribute("id",id + "_status");
		status.className = "searchBox";
		status.style.width= wrapper.offsetWidth + "px";
		wrapper.parentNode.insertBefore(status,wrapper.nextSibling);
		
		//regex incremental search in statusbar
		var search = document.createElement("span");
		search.innerHTML = '検索：<input type="text" id="'+ this.id +'_searchbox">';
		status.appendChild(search);
		
		var editor = this;
		
		//search box
		$(this.id + '_searchbox').onkeydown = function(e){
			
			if(!e)e=event;
			
			if(window.navigator.appName.toLowerCase().indexOf("microsoft") == -1) {//for firefox
				var keyCode = e.which;
			}else{//for ie
				var keyCode = e.keyCode;
			}
			
			if(keyCode == 13){
				if(e.shiftKey){
					editor.search_counter-=2;
					if(editor.search_counter<0)editor.search_counter=0;
				}
				editor.search($(editor.id + '_searchbox').value,true);
				
				e.cancelBubble=true;
				e.returnValue = false;
				
				return false;
				
			}else if(keyCode){
				editor.search($(editor.id + '_searchbox').value);
			}
			
			return true;
		};
		
		//キーイベントの登録
		$(id).onkeydown = function(e){
			if(!e) e = event;
			if(window.navigator.appName.toLowerCase().indexOf("microsoft") == -1) {//for firefox
				var keyCode = e.which;
			}else{//for ie
				var keyCode = e.keyCode;
			}
			for(var i=0;i<editor.keyMask.length;i++){
				var _keyMask = editor.keyMask[i];
				if(e.ctrlKey == _keyMask.ctrl && e.shiftKey == _keyMask.shift && e.altKey == _keyMask.alt){
					if(keyCode == _keyMask.keyCode){
						return _keyMask.callback(editor,e);
					}
				}		
			}
		};
		$(id).onkeyup = function(e){
			editor.updateLines();
		};
		
		//dummy
		var dummy_textarea = document.createElement("div");
		dummy_textarea.setAttribute("id",id + "_dummy");
		dummy_textarea.style.visibility = "hidden";
		dummy_textarea.style.position = "absolute";
		dummy_textarea.style.fontSize = this.fontSize + "px";
		dummy_textarea.style.whiteSpace = "pre";
		dummy_textarea.style.height = "1px";
		dummy_textarea.style.width = "1px";
		dummy_textarea.style.overflow = "scroll";
		dummy_textarea.style.lineHeight = this.lineHeight + "px";
				
		document.body.appendChild(dummy_textarea);
		
		//clear:both;
		var clearBox = document.createElement("div");
		clearBox.style.clear = "both";
		clearBox.style.height = "1px";
		totalwrapper.appendChild(clearBox);
		
		this.updateLines();
		$(id).focus();
		
		wrapper.scrollLeft = 0;
	},
	
	/**
	 * 検索を行います。
	 */
	search:function(value,option){
		
		var reg = new RegExp(value,"g");
		
		if(option){
			for(var i=0; i<=this.search_counter; i++){
				reg.exec($(this.id).value);
			}
			this.search_counter++;			
		}else{
			this.search_cuonter = 0;
		}
		
		var result = reg.exec($(this.id).value);
		
		if(!result){
			$(this.id + "_searchbox").style.backgroundColor = "red";
			return;
		}else{
			$(this.id + "_searchbox").style.backgroundColor = "";
		}
		
		var pos = result.index;
		var lines = $(this.id).value.substring(0,pos).split("\n").length-1;
		$(this.id + "_wrapper").scrollTop =  lines * this.lineHeight;
	},
	
	/**
	 *　行数チェック
	 */
	updateLines:function(){
		
		var id = this.id;
		
		$(id + "_dummy").innerHTML = $(id).value.replace(/[<> ]/g,'a');
		$(id).style.width = Math.max($(id).scrollWidth,$(id).offsetWidth,($(id+"_dummy").scrollWidth + $(id+"_dummy").offsetWidth),$(id + "_wrapper").offsetWidth) + "px";
		
		var lines = $(this.id + "_lines");
		var line = Math.max($(id).value.replace("\r\n","\n").split("\n").length,1);
		if(lines.line != line){
			lines.line = line;
			var html = "";
			for(var i=1; i<=line; i++){
				html += i + "<br>";
			}
				
			lines.innerHTML = html;
			lines.style.height = this.lineHeight * line + "px";
			$(id).style.height = this.lineHeight * line + "px";
		}
	},
	
	/**
	 * カーソル位置にカーソルを動かします。
	 */
	showCurrentCursor:function(){
		return;		
	},
	
	getSelectionRange:function(){
		var textarea = $(this.id);
		if (document.selection != null){
			textarea.focus();
			
			var docRange = window.document.selection.createRange();
			var textRange = document.body.createTextRange();
			textRange.moveToElementText(textarea);
			
			var start = 0;
			var end = 0;

			if(docRange.text.length){
				var range = textRange.duplicate();
				range.setEndPoint('EndToStart', docRange);
				start = range.text.length;
		
				var range = textRange.duplicate();
				range.setEndPoint('EndToEnd', docRange);
				end = range.text.length;
				
			}else{
				
				var docRange = document.selection.createRange();
				var bookmark = docRange.getBookmark();
				
				var marker = "##TEMP##" + new Date() + "/##TEMP##";
				
				while(textarea.value.indexOf(marker) != -1){
					marker = "##TEMP##" + new Date() + "/##TEMP##";
				}
				
				docRange.text = marker;
				
				var range = textarea.createTextRange();
				start = textarea.value.indexOf(marker);
				end = start;
				
				textarea.value = textarea.value.replace(marker,"");

				range.moveToBookmark(bookmark);
				range.select();
				
			}
		}else{
			var start = textarea.selectionStart;
			var end = textarea.selectionEnd;
		}
		
		return {"start":start,"end":end};
	},
	
	getSelectionText:function(){
	},
	
	setSelectionRange:function(start,end){
		
		var textarea = $(this.id);
		
		if(document.all){//ie
			var range = textarea.createTextRange();
			if(textarea.value.indexOf("\n") != -1){
				var step = textarea.value.substring(0,start).match(/\n/g).length;
			}else{
				var step = 0;
			}
			
			range.move('character', start-step);
			range.select();
		}else{//other
			textarea.setSelectionRange(start,end);
		}
	},
	
	insertString:function(selectionStart,selectionEnd,str){
		var textarea = $(this.id);
		var beforeString = textarea.value.substring(0,selectionStart);
		var afterString = textarea.value.substring(selectionEnd,textarea.value.length);
		textarea.value = beforeString + str + afterString;
	},
	
	showToolbar:function(depth){
		if(!$(this.id + "_toolbar_" + depth)){
			this.toolbar[depth] = "";
			return;
		}
		
		$(this.id + "_toolbar_" + depth).style.display = "";
	},
	
	hideToolbar:function(depth){
		if(!$(this.id + "_toolbar_" + depth)){
			this.toolbar[depth] = "none";
			return;
		}
		
		
		$(this.id + "_toolbar_" + depth).style.display = "none";
	}
};