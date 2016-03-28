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
		this.ul = $("<ul>");
	},
	
	createLi : function(text, comment, parent, type){
		var li = $("<li>");
		li.click(function(){
			//LI要素が押されたとき
			var $cssArea = $("#"+this.contentId);
			var point = $cssArea.val().indexOf(this.comment, 0);
			var line = Math.max($cssArea.val().substring(0, point+1).replace("\r\n","\n").split("\n").length, 1);

			$cssArea.scrollTop((line-1) * this.lineHeight);
		});
		li.mouseover(function(){
			$(this).css("textDecoration",'underline');
		});
		li.mouseout(function(){
			$(this).css("textDecoration",'none');
		});
		
		if(type == "class"){
			if(text.indexOf("#") != -1){
				li.css("color",'pink');
			}else if(text.indexOf(".") != -1){
				li.css("color",'aqua');
			}else{
				li.css("color","white");
			}
		}else{
			li.css("color",'lawngreen');
		}
		
		li.attr("type", type);
		li.html(text);
		li.prop("comment", comment);
		li.prop("parent", parent);
		li.prop("index", $("#"+this.targetId).val().indexOf(text));
		li.prop("contentId", this.targetId);
		li.prop("lineHeight", this.lineHeight);
		li.prop("ClassName", "cssMenuLi");
		li.css("cursor", "pointer");
		li.css("listStyleType", "square");
		
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
	
		this.ul = $("<ul>");
		for(var i =0; i<this.list.length; i++){
			this.ul.append(this.list[i]);
		}
		
		$("#"+this.id).append(this.ul);

	},
	
	renderComment : function(){
		var contents = $("#"+this.targetId).val();
		var commentRegex = new RegExp(
			"\\/\\*([^\n]*?)\\*\\/",
			"g"
		);
		
		var ret_val = new Array();
		
		while((commentRes = commentRegex.exec(contents))){
			if(commentRes[1].replace(/^\s+|\s+$/g, "") ==""){
				continue;
			}
			ret_val.push(this.createLi(commentRes[1], commentRes[0], this.ul, "comment"));
		}
		return ret_val;
	
	},
	
	renderClass : function(){
		var contents = $("#"+this.targetId).val();
		contents = this.replaceString("\\/\\*.*?\\*\\/", "", contents);
		contents = this.replaceString("\\/\\*[^\\*\\/]*?\\*\\/", "", contents);
		
		var regex = new RegExp("([^{\n]*?)\n*?{[^}]*}", "g");
		
		//this.ul = document.createElement("UL");
		
		var ret_val = new Array();
		
		while((regRes = regex.exec(contents))){
			if(regRes[1].replace(/^\s+|\s+$/g, "") =="*"){
				continue;
			}
			
			ret_val.push(this.createLi(regRes[1], regRes[0], this.ul, "class"));
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
		
		$("#cssMenu li").each(function(){
			if(CSSView.mode == "all"){
				$(this).show();
			}else if(CSSView.mode == $(this).attr("type")){
				$(this).show();
			}else{
				$(this).hide();
			}
		});

	},
	
	clear : function(){
		$("#"+this.id).empty();
		$("#"+this.targetId).val("");
	}
};

