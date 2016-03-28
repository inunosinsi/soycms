var CSSList = function(){
	this.initialize(arguments[0],arguments[1]);
}

CSSList.prototype = {

	id: null,
	ul : null,
	list : null,
	init : {},
	targetId : null,
	lineHeight : 20,
	mode : "all",
	
	
	initialize : function(id,targetId,initParam){
		this.id = id;
		this.targetId = targetId;
		this.list = new Array();
		this.init = initParam;
		this.ul = document.createElement("UL");
	},
	
	createLi : function(text,comment,parent,type){
		var li = document.createElement("li");
		li.onclick = function(){
			//LI要素が押されたとき
			var cssArea = document.getElementById(this.contentId);
			var point   = cssArea.value.indexOf(this.comment,0);
			var line = Math.max(cssArea.value.substring(0,point+1).replace("\r\n","\n").split("\n").length,1);
			$(this.contentId).scrollTop =  (line-1) * this.lineHeight;
		};
		li.onmouseover = function(){
			this.style.textDecoration = 'underline';
		};
		li.onmouseout = function(){
			this.style.textDecoration = 'none';
		}
		
		if(type == "class"){
			if(text.indexOf("#") != -1){
				li.style.color = 'pink';
			}else if(text.indexOf(".") != -1){
				li.style.color = 'aqua';
			}else{
				li.style.color = "white";
			}
		}else{
			li.style.color = 'lawngreen';
		}
		
		li.innerHTML = text;
		li.comment = comment;
		li.parent  = parent;
		li.index = $(this.targetId).value.indexOf(text);
		li.contentId = this.targetId;
		li.lineHeight = this.lineHeight;
		li.setAttribute("class","cssMenuLi");
		li.style.cursor = 'pointer';
		li.style.listStyleType ="square";
		
		return li;
	},
	
	render : function(flag){
		
		this.list = new Array();
		
		var commentList = this.renderComment();
		var classList = this.renderClass();

		for(var i = 0; i<commentList.length; i++){
			this.list.push(commentList[i]);
		}
		
		for(var i = 0; i<classList.length; i++){
			this.list.push(classList[i]);
		}
		
		this.list.sort(function(a,b){return (a.index - b.index);} );
	
		this.ul = document.createElement("UL");
		for(var i =0; i<this.list.length; i++){
			this.ul.appendChild(this.list[i]);
		}
		
		$(this.id).appendChild(this.ul);
		
	},
	
	renderComment : function(){
		var contents = document.getElementById(this.targetId).value;
		var commentRegex = new RegExp(
			"\\/\\*([^\n]*?)\\*\\/",
			"g"
		);
		
		var ret_val = new Array();
		
		while((commentRes = commentRegex.exec(contents))){
			if(commentRes[1].replace(/^\s+|\s+$/g, "") ==""){
				continue;
			}
			ret_val.push(this.createLi(commentRes[1],commentRes[0],this.ul,"comment"));
		}
		return ret_val;
	
	},
	
	renderClass : function(){
		var contents = document.getElementById(this.targetId).value;
		contents = this.replaceString("\\/\\*.*?\\*\\/","",contents);
		contents = this.replaceString("\\/\\*[^\\*\\/]*?\\*\\/","",contents);
		
		var regex = new RegExp("([^{\n]*?)\n*?{[^}]*}","g");
		
		//this.ul = document.createElement("UL");
		
		var ret_val = new Array();
		
		while((regRes = regex.exec(contents))){
			if(regRes[1].replace(/^\s+|\s+$/g, "") =="*"){
				continue;
			}
			
			ret_val.push(this.createLi(regRes[1],regRes[0],this.ul,"class"));
			//this.ul.appendChild(this.createLi(regRes[1],regRes[1],this.ul));

		}
		
		return ret_val;
		//$(this.id).appendChild(this.ul);
		
	},
	
	replaceString : function(regex,replace,contents){
		var regex = new RegExp(
			regex
		);
		
		while(true){
			var res = regex.exec(contents);
			if(!res){
				break;
			}
			contents = contents.replace(res[0],replace);
		}
		return contents;
	},
	
	toggleList : function(mode){
		if(this.mode == mode){
			return;
		}
		this.mode = mode;
		
		$$("li.cssMenuLi").each(function(ele){
			if(CSSView.mode == "all"){
				ele.show();
			}else if(CSSView.mode == ele.type){
				ele.show();
			}else{
				ele.hide();
			}
		});

	},
	
	clear : function(){
		if($(this.id).hasChildNodes()){
			$(this.id).removeChild(this.ul);
		}
		document.getElementById(this.targetId).value = "";
	}

};

