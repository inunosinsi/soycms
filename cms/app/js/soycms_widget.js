var soycms = {};

//soycms.UIはUI周りのUtilityクラス
if(!soycms.UI)soycms.UI = {
	getWindow : function(id){
		var wm = this.defaultWindowManager;
		return wm.getWindow(id);
	},

	getTopWindow : function(){
		var wm = this.defaultWindowManager;
		return wm.getFrontWindow();
	}
};

//Manager
soycms.UI.WindowManager = function(){
	this.initialize.apply(this, arguments);
};
soycms.UI.WindowManager.prototype = {
	options: {
		container:null,
		zIndex:0,
		prefix : "soycms_widget_"
	},
	stack : [],
	counter : 0,

	initialize: function(options) {
		this.options = $.extend(this.options, options || {});
	},
	getContainer : function(){
		return (this.options.container) ? this.options.container : document.body;
	},
	getNextId : function(options){
		this.counter++;

		if(options.id){
			return this.options.prefix + options.id;
		}else{
			return this.options.prefix + this.counter;
		}
	},
	add : function(window){
		this.stack.push(window);
		window.setZIndex(100 + this.counter * 1);
	},
	remove : function(window){
		//if(window.getWindowEl().size() > 0)window.getWindowEl().remove();
		//if(window.getWindowEl())this.getContainer().removeChild(window.getWindowEl());
		if(window.getWindowEl()) window.getWindowEl().remove();
		var stack = this.stack;
		stack.some(function(v, i){
			if(v == window)stack.splice(i, 1);
		});
		this.stack = stack;
		//this.stack = this.stack.without(window);

	},
	getWindow : function(id){
		var id = this.options.prefix + id;
		return this.stack.find(function(win) { return win.id == id });
	},
	getFrontWindow : function(){
		return this.stack[this.stack.length-1];
	},
	sendToBack : function(win) {
			var stack = this.stack;
			stack.some(function(v, i){
				if(v == window)stack.splice(i, 1);
			});
			this.stack = stack;
    	//this.stack = this.stack.without(window);
    	this.stack.unshift(win);
    	this.resetZIndexes();
	},
	bringToFront : function(win) {
			var stack = this.stack;
			stack.some(function(v, i){
				if(v == window)stack.splice(i, 1);
			});
			this.stack = stack;
    	//this.stack = this.stack.without(window);
    	this.stack.push(win);
    	this.resetZIndexes();
  	},
  	resetZIndexes : function(){
  		var zIndex = 100;
  		this.stack.some(function(w, i) {
	    	w.setZIndex(zIndex);
	    	zIndex = w.lastZIndex + 1;
	    });
  	}
};
soycms.UI.defaultWindowManager = new soycms.UI.WindowManager();

//Window
soycms.UI.WindowBase = function(){

};
soycms.UI.WindowBase.prototype = {
	id : null,
	lastZIndex : 0,
	options: {},
	initialize: function(options) {
		//do nothing
		return this;
	},

	getManager : function(){
		return this.options.wm;
	},

	build : function(){

		var window = this;
		var targetId = this.getManager().getNextId(this.options);
		this.id = targetId;

		option = this.options;

		//wrapper
		var wrapper = document.createElement("div");

		with(wrapper.style){
			width = option.width + "px";
			height = option.height + "px";

			display = "none";

			var client_width = (window.innerWidth ||
			     document.documentElement.clientWidth ||
			     document.body.clientWidth);
			var client_height = (window.innerHeight ||
			     document.documentElement.clientHeight);

			left = parseInt(document.body.scrollLeft + client_width/2 - option.width/2) + "px";
			top = Math.max(0,parseInt(document.body.scrollTop + client_height/2 - option.height/2)) + "px"
		};

		wrapper.setAttribute("id",targetId + "_wrapper");
		wrapper.setAttribute("class","common_to_layer_wrapper");
		wrapper.setAttribute("className","common_to_layer_wrapper");
		this.getManager().getContainer().appendChild(wrapper);

		//window bar
		var bar = document.createElement("div");
		bar.id = targetId + "layer_bar";
		bar.style.height = "25px";
		wrapper.appendChild(bar);

		var bar_left = document.createElement("div");
		bar_left.id = targetId + "_layer_bar_left";
		bar_left.setAttribute("class","layer_bar_left");
		bar_left.setAttribute("className","layer_bar_left");
		bar.appendChild(bar_left);

		var bar_mid = document.createElement("div");
		bar_mid.id = targetId + "_layer_bar_mid";
		bar_mid.setAttribute("class","layer_bar");
		bar_mid.setAttribute("className","layer_bar");
		bar_mid.style.width = option.width - 27 + "px";
		bar.appendChild(bar_mid);

		var bar_right = document.createElement("div");
		bar_right.id = targetId + "_layer_bar_right";
		bar_right.setAttribute("class","layer_bar_right");
		bar_right.setAttribute("className","layer_bar_right");
		bar.appendChild(bar_right);

		bar._onmousemove = function(e){

			var x,y;

			if (document.all) {
				x = event.clientX - bar.offsetX + document.body.scrollLeft;
				y = event.clientY - bar.offsetY + document.body.scrollTop;
			}else{
				x = e.pageX - bar.offsetX;
				y = e.pageY - bar.offsetY;
			}

			if(x>0){
				wrapper.style.left = x + "px";
			}

			if(y>0){
				wrapper.style.top = y + "px";
			}

			return false;
		};

		bar.onmousedown = function(e){

			//バーをクリックで最前面に
			window.bringToFront();
			window.startMove();

			if(document.onmousemove == this._onmousemove){
				this.onmouseup();
			}

			document._onmousemove = document.onmousemove;
			document.onmousemove = this._onmousemove;

			if (document.all) {
				this.offsetX = event.offsetX + 2;
				this.offsetY = event.offsetY + 2;
			}else{
				this.offsetX = e.pageX - wrapper.offsetLeft;
				this.offsetY = e.pageY - wrapper.offsetTop;
			}

			$(".select").each(function(i, ele){
				ele._visibility = ele.style.visibility;
				ele.style.visibility = "hidden";
			});

			return false;
		};
		bar.onmouseup = function(){

			if(document._onmousemove){
				document.onmousemove = document._onmousemove;
			}else{
				document.onmousemove = null;
			}

			$(".select").each(function(i, ele){
				ele.style.visibility = ele._visibility;
			});

			window.endMove();
		};

		//close button
		var close = document.createElement("div");
		close.setAttribute("id",targetId + "_close");
		close.setAttribute("class","click_to_layer_close");
		close.setAttribute("className","click_to_layer_close");
		close.innerHTML = "<a href='javascript:void(0);'></a>";
		close.onmousedown = function(e){
			if(!e)e = event;
			e.cancelBubble = true;
			e.returnValue = false;

			close.setAttribute("class","click_to_layer_close_down");
			close.setAttribute("className","click_to_layer_close_down");

			return false;
		};
		close.onmouseup = function(e){
			if(!e)e = event;
			e.cancelBubble = true;
			e.returnValue = false;

			close.setAttribute("class","click_to_layer_close");
			close.setAttribute("className","click_to_layer_close");

			return false;
		};

		close.onclick = function(){
			if(option.onclose){
				var result = option.onclose();
				if(result == false)return;
			}
			window.close();
		};
		if(option.disableClose == true){
			close.style.visibility = "hidden";
		}

		bar.appendChild(close);

		//small button
		var small = document.createElement("div");
		small.setAttribute("id",targetId + "_small");
		small.setAttribute("class","click_to_layer_small");
		small.setAttribute("className","click_to_layer_small");
		small.innerHTML = "<a href='javascript:void(0);'></a>";
		bar.appendChild(small);

		if(option.disableClose == true){
			small.style.left = close.offsetLeft + "px";
		}

		small.onmousedown = function(e){
			if(!e)e = event;
			e.cancelBubble = true;
			e.returnValue = false;

			small.setAttribute("class","click_to_layer_small_down");
			small.setAttribute("className","click_to_layer_small_down");

			return false;
		};

		small.onmouseup = function(e){
			if(!e)e = event;
			e.cancelBubble = true;
			e.returnValue = false;

			small.setAttribute("class","click_to_layer_small");
			small.setAttribute("className","click_to_layer_small");

			return false;
		};

		small.onclick = function(){
			$("#" + targetId + "layer_left").toggle();
			$("#" + targetId + "layer_right").toggle();
			$("#" + targetId + "_container").toggle();
		};

		//containerの両脇を作成
		var layer_left = document.createElement("div");
		layer_left.id = targetId + "layer_left";
		layer_left.setAttribute("class","layer_left");
		layer_left.setAttribute("className","layer_left");
		wrapper.appendChild(layer_left);

		var layer_right = document.createElement("div");
		layer_right.setAttribute("class","layer_right");
		layer_right.setAttribute("className","layer_right");
		layer_right.id = targetId + "layer_right";
		wrapper.appendChild(layer_right);

		var container = document.createElement("div");
		container.id = targetId + "_container";
		container.setAttribute("class","layer_wrapper");
		container.setAttribute("className","layer_wrapper");

		//iframeを生成する場合
		container.style.width = option.width - 10 + "px";
		container.style.height = option.height + "px";

		wrapper.appendChild(container);

		//iframeの下を作成
		var bar_bottom = document.createElement("div");
		bar_bottom.id = targetId + "_layer_bottom";

		var layer_bottom_left = document.createElement("div");
		layer_bottom_left.id = targetId + "_layer_bottom_left";
		layer_bottom_left.setAttribute("class","layer_bottom_left");
		layer_bottom_left.setAttribute("className","layer_bottom_left");
		bar_bottom.appendChild(layer_bottom_left);

		var layer_bottom_mid = document.createElement("div");
		layer_bottom_mid.id = targetId + "_layer_bottom_mid";
		layer_bottom_mid.setAttribute("class","layer_bottom");
		layer_bottom_mid.setAttribute("className","layer_bottom");
		layer_bottom_mid.style.width = option.width - 17 + "px";
		bar_bottom.appendChild(layer_bottom_mid);

		var layer_bottom_right = document.createElement("div");
		layer_bottom_right.id = targetId + "_layer_bottom_right";
		layer_bottom_right.setAttribute("class","layer_bottom_right");
		layer_bottom_right.setAttribute("className","layer_bottom_right");
		bar_bottom.appendChild(layer_bottom_right);

		//resize
		layer_bottom_right.onmousedown = function(e){

			if(option.onresize){
				if(false === option.onresize())return false;
			}

			//リサイズ開始
			window.startResize();

			document.onmousemove = function(e){
				if(!e)e=event;
				//var x = Event.pointerX(e);
				//var y = Event.pointerY(e);
				var x = e.x;
				var y = e.y;

				var newWidth = x - wrapper.offsetLeft;
				var newHeight = y - wrapper.offsetTop;

				window.resize(newWidth, newHeight);

				return false;
			};

			layer_bottom_right.onmouseup = document.onmouseup = function(e){
				document.onmousemove = function(){};
				window.endResize();
			};

			return false;
		}

		wrapper.appendChild(bar_bottom);

	},
	getWindowEl : function(){
		return $("#" + this.id + "_wrapper");
	},
	getContainerEl : function(){
		return $("#" + this.id + "_container");
	},
	show : function(){
		this.getWindowEl().show(0.5);
		//new Effect.BlindDown(this.getWindowEl(),{
		//duration : 0.5
		//});
		return this;
	},
	close : function(){
		//if(this.getWindowEl().size() > 0)this.getWindowEl().hide();
		if(this.getWindowEl())this.getWindowEl().hide();
		this.getManager().remove(this);
		return this;
	},
	hide : function(){
		new Effect.Fade(this.getWindowEl());
		return this;
	},
	update : function(str){
		this.getContainerEl().get(0).update(str);
		return this;
	},
	resize : function(w,h){

		var targetId = this.id;

		var _common_get = function(id){
			return $("#" + targetId + id).get(0);
			//return $(targetId + id);
		};

		if(w && w >= 50){
			_common_get("_wrapper").style.width = w + "px";
			_common_get("_container").style.width = w - 10 + "px";
			_common_get("_layer_bar_mid").style.width = w - _common_get("_layer_bar_left").offsetWidth - _common_get("_layer_bar_right").offsetWidth + "px";
			_common_get("_layer_bottom_mid").style.width = w - _common_get("_layer_bottom_left").offsetWidth - _common_get("_layer_bottom_right").offsetWidth + "px";
		}

		if(h && h >= 50){
			_common_get("_wrapper").style.height = h + "px";
			_common_get("_container").style.height = h + "px";
		}

		return false;

	},

	//リサイズ開始のトリガー
	startResize : function(){},

	//リサイズ終了のトリガー
	endResize : function(){},

	//移動トリガー
	startMove : function(){},

	//移動終了トリガー
	endMove : function(){},

	setZIndex : function(zIndex){
		this.lastZIndex = zIndex;
		//this.getWindowEl().style.zIndex = zIndex;
		this.getWindowEl().css("z-index", zIndex);
	},
	bringToFront : function(){
		this.options.wm.bringToFront(this);
	}
};

soycms.UI.Window = function(){
	this.initialize.apply(this, arguments);
};
soycms.UI.Window.prototype = $.extend(new soycms.UI.WindowBase(), {
	initialize: function(options) {
		this.options = {
			id : null,
			width : 180,
			height : 300,
			wm : soycms.UI.defaultWindowManager,	//window manager
			onclose : null,
			onresize : null
		};

		this.options = $.extend(this.options, options || {});
		this.build();

		this.getManager().add(this);
		this.show();

		return this;
	}
});

soycms.UI.TargetWindow = function(){
	this.initialize.apply(this, arguments);
};
soycms.UI.TargetWindow.prototype = $.extend(new soycms.UI.WindowBase(), {

	url : "about:blank",

	initialize: function(element, options) {

		this.options = {
			id : null,
			width : 640,
			height : 480,
			wm : soycms.UI.defaultWindowManager,	//window manager
			onclose : null,
			onresize : null
		};

		this.options = $.extend(this.options, options || {});
		this.build();

		this.getManager().add(this);
		this.show();

		if(element != undefined && element.tagName.match(/a/i)){
			this.url = element.getAttribute("href");
		}

		this.createIframe();

		if(element != undefined && element.tagName.match(/form/i)){
			element.setAttribute("target", this.getIframeId());
		}

		return this;
	},

	startResize : function(){
		this.hideIframe();
	},

	endResize : function(){
		this.showIframe();
	},

	startMove : function(){
		this.hideIframe();
	},

	endMove : function(){
		this.showIframe();
	},

	hideIframe : function(){
		this.getIframe().css("visibility", "hidden");
	},

	showIframe : function(){
		this.getIframe().css("visibility", "visible");
	},

	createIframe : function(){
		this.getContainerEl().html(
			'<iframe src="'+ this.url +'" name="'+ this.getIframeId() +
				 '" frameborder="0" id="'+ this.getIframeId() +
				 '" class="click_to_layer_frame"></iframe>');

		this.getIframe().css("width", "100%");
		this.getIframe().css("height", "100%");

		this.getContainerEl().css("overflow", "visible");
	},

	getIframeId : function(){
		return this.id + "_iframe";
	},

	getIframe : function(){
		return $("#" + this.getIframeId());
	}
});
