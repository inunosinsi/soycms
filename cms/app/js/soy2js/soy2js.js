var soy2js = {
	
	rootURL : "/",
	server : "/index.php",
	
	debug : false,
	
	actions : {},
	
	addAction : function(key,action){
		if(action instanceof soy2js.Action){
			this.actions[key] = action;
		}else{
			alert("[FAILED]" + key + " is not valid soy2js.Action!!!");
			throw ("[FAILED]" + key + " is not valid soy2js.Action!!!");
		}
	},
	
	removeAction : function(key){
		delete this.actions[key];
	},
	
	loadAction : function(key){
		var url = this.rootURL + "actions/" + key.replace(/\./,"/") + "Action.js";
		if(this.debug)url += "?t=" + (new Date()).getTime();
		
		new Ajax.Request(
			url,
			{	
				method : "get",
				asynchronous : false,
				onComplete : function(req){
					eval("var res = " + req.responseText + ";");
					soy2js.addAction(key,res);
				},
				onFailure : function(){
					alert("failed to load action...");				
				}
			}
		);
	},
	
	/*
	 * run action
	 */
	run : function(key,args){
		if(!this.actions[key])this.loadAction(key);
		if(!this.actions[key]){
			alert("[FAILED]no action exists!(" + key + ")");
			return;
		}
		
		var action = this.actions[key];
		
		var e;
		if(typeof event == "undefined"){
			e = arguments.callee.caller.arguments[0];
		}else{
			e = event;
		}
		
		// run action
		this.beforeRunAction();
		action.element = Event.element(e);
		action.arguments = args;
		action.execute(action.element, action.arguments);
		this.afterRunAction();
	},
	
	redirect : function(_action, key, args){
		
		if(!this.actions[key])this.loadAction(key);
		if(!this.actions[key]){
			alert("[FAILED]no action exists!(" + key + ")");
			return;
		}
		
		var action = this.actions[key];
		
		// run action
		this.beforeRunAction();
		action.element = _action.element;
		action.arguments = args;
		action.execute(action.element, action.arguments);
		this.afterRunAction();
	},
	
	cast : function(to_class, obj){
		
		if(!this.class_exists(to_class)){
			var url = this.rootURL + "classes/" + to_class.replace(/\./,"/") + ".class.js";
			
			var self = this;
			var tmpName = "";
			
			if(to_class.match(/\./)){
				to_class.split(".").each(function(name){
					tmpName += name;
					if(!self.class_exists(tmpName)){
						eval(name + " = {};");
					}
				});			
			}
			
			new Ajax.Request(
				url,
				{	
					method : "get",
					asynchronous : false,
					onComplete : function(req){
						eval("window." + to_class + " = "+ req.responseText);
												
					},
					onFailure : function(){
						alert("failed to load " + to_class + "...");
					}
				}
			);
			
			//読み込んだけど宣言されてない時
			if(!this.class_exists(to_class)){
				alert("failed to load " + to_class + "...");
				return obj;			
			}
		}
		
		//変更するでさ
		var new_obj = eval("new "+ to_class + "();");
		Object.extend(new_obj, obj);
		
		return new_obj;
		
	},
	
	class_exists : function(class_name, prefix){
		var is_loaded = true;
		if(prefix){
			class_name = prefix + "." + class_name;
		}
		eval("if(typeof " + class_name + " == 'undefined')is_loaded=false;");
		return is_loaded;
	},
	
	//trigger run action
	beforeRunAction : function(){},
	afterRunAction : function(){},
	
	
	/* functions for server-side connection */
	observe : function(key, func){
		if(!soy2js.cache.observer[key])soy2js.cache.observer[key] = [];
		soy2js.cache.observer[key].push(func);
		return func;
	},
	
	stopObserving : function(key,func){
		if(!soy2js.cache.observer[key])return;
		soy2js.cache.observer[key] = soy2js.cache.observer[key].without(func);
	}
	
};

//Ajaxで受信したデータのキャッシング
soy2js.cache = {
	
	caches : {},	//キャッシュ
	
	observer : {},	//監視
	
	options : {
		key : "default",
		method : "post",
		asynchronous : true,
		type : "plain",
		params : {},
		callback : function(key,data){
			soy2js.cache.setData(key,data);
		},
		error : function(){
		
		}
	},
	
	/* 
	 * Ajaxでデータを取得(キャッシュがあっても上書き)
	 *　
	 */
	loadData : function(key, options, sync){
		if(!options)options = {};
		options = Object.extend(this.options,options);
				
		if(sync){
			options.asynchronous = false;
		}
		
		var url = soy2js.server + "?key=" + key;
		url += "&t=" + (new Date()).getTime();
		
		new Ajax.Request(
			url,
			{	
				method : options.method,
				asynchronous : options.asynchronous,
				parameters : options.params,
				onComplete : function(req){
					var res = null;
					switch(options.type){
						case "json":
							try{
								eval("res = " + req.resposeText);
							}catch(e){
								
							}
							break;
						default:
							res = req.responseText;
					
					}
					
					options.callback(key,res);
				},
				onFailure : function(){
					options.error();
				}
			}
		);
		
		return (this.caches[key]) ? this.caches[key].data : "";
	},
	
	/*
	 * PostData
	 */
	postData : function(key, options, sync){
		var result = null;
		
		options.callback = function(key,data){
			alert(data);
			result = data;
		};
		
		this.loadData(key, options, sync);
		
		if(sync){
			return result;
		}
		
		return null;
	},	
	
	/* 
	 * SJaxでデータを取得(キャッシュがあればそれを使う)
	 */
	getData : function(key, options){
		if(!this.caches[key] || this.caches[key].lifetime < (new Date()).getTime()){
			return this.loadData(key,options,true);
		}
		
		return this.caches[key].data;
	},
	
	/*
	 * データをセットする
	 */
	setData : function(key, obj, lifetime){
		if(!lifetime)lifetime = (new Date()).getTime() + 10 * 600000;
		
		if(!this.caches[key] || this.caches[key].data != obj){
			this.observer[key].each(function(func){
				func(obj);			
			});
		}
		
		this.caches[key] = {
			data : obj,
			lifetime : lifetime
		};
	},
	
	/*
	 * キャッシュを削除する。
	 * loadDataでいいんでないかという気もするけど
	 */
	clearData : function(key){
		if(!this.caches[key]){
			this.observer[key].each(function(func){
				func(null);			
			});
		}
		
		delete this.caches[key];
	}

};

//AjaxのActionの遷移
soy2js.history = {
	histories : [],
	forward : function(){
	
	},
	back : function(){
	
	}
};

//template
soy2js.template = {
	templates : {},
	getTemplate : function(key){
		if(!this.templates[key])this.loadTemplate(key);
		return (this.templates[key]) ? this.templates[key] : new soy2js.Template();
	},
	
	loadTemplate : function(key){
		var url = soy2js.rootURL + "templates/" + key.replace(/\./,"/") + ".html";
		
		if(soy2js.debug)url += "?t=" + (new Date()).getTime();
		new Ajax.Request(
			url,
			{	
				method : "get",
				asynchronous : false,
				onComplete : function(req){
					soy2js.template.templates[key] = new soy2js.Template(req.responseText);
				},
				onFailure : function(){
					soy2js.template.templates[key] = new soy2js.Template();
				}
			}
		);
	}
};
soy2js.Template = function(){
	this.initialize.apply(this, arguments);
};
soy2js.Template.prototype = {
	template : "",
	initialize: function(template) {
		this.template = new Template(template);
	},
	build : function(data){
		if(!(data instanceof Array)){
			data = [data];
		}
		
		var html = "";
	
		for(var i=0; i<data.length; i++){
			var obj = data[i];
			if((obj == null) || (typeof(obj) != 'object'))continue;
			html += this.template.evaluate(obj);
		}
		
		return "" + html;
	}
};

//soy2js.Action
soy2js.Action = function(){
	this.initialize.apply(this, arguments);
};
soy2js.Action.prototype = {
	element : null,
	arguments : null,
	execute : function(){},
	
	initialize: function(func) {
		this.execute = func;
	},
	

	getTemplate : function(key){
		return soy2js.template.getTemplate(key)
	},
	
	getView : function(id){
		return $(id);
	},
	
	getEl : function(id){
		return $(id);
	},
	
	redirect : function(key, args){
		soy2js.redirect(this,key,args);
	},
	
	/* ajax methods */
	
	getData : function(key, options){
		return soy2js.cache.getData(key,options);
	},
	
	getJson : function(key, options){
		if(!options)options = {};
		options.type = "json";
		return soy2js.cache.getData(key,options);
	},
	
	postData : function(key, data, sync){
		
		//非同期を設定しない限り同期
		if(sync !== false){
			sync = true;
		}
		
		options = {};
		if(typeof data == "string"){
			options.params = data;
		}else{
			options.params = $H(data).toQueryString();
		}
		
		return soy2js.cache.postData(key,options,sync);
	},
	
	setData : function(key, value){
		soy2js.cache.setData(key,value);
	},
	
	getFormParams : function(ele){
		if(!ele)ele = this.element;
		
		if(ele.form){
			return Form.serializeElements(Form.getElements(ele.form));
		}else{
			return "";
		}
	}
};

(function(){
	var scripts = document.getElementsByTagName("script");
	for(var i=0; i<scripts.length; i++){
		if(scripts[i].src.match(/soy2js\.js/)){
			var src = scripts[i].src;
			src = src.substring(0,src.indexOf("/soy2js.js")) + "/";
			soy2js.rootURL = src;
		}	
	}
}());