//need prototype.js
var PanelManager = {
	
	wrapper_id : "",
	panels : [],
	split : [],
	
	width : 0,
	height: 0,
	
	oldwidth : 0,
	oldheight : 0,
	
	splitSize : 10,
	tabHeight : 28,
	
	showTab : true,
	
	init : function(wrapper_id,option){
		this.wrapper_id = wrapper_id;
		
		this.showTab = (option && option.showTab != undefined) ? option.showTab : true;
		if(this.showTab != true)this.tabHeight = 0;
		
		this.width = $(this.wrapper_id).offsetWidth;
		this.height = $(this.wrapper_id).offsetHeight;
		
		this.oldwidth = $(this.wrapper_id).offsetWidth;
		this.oldheight = $(this.wrapper_id).offsetHeight;
		
		this.add("west_panel",{width:this.width,height:this.height});
		var east_split = this.addSplit("east_split");
		this.add("east_panel");
		
		var south_split = this.addSplit("south_split");
		this.add("south_panel");
		
		east_split.getEl().style.width = this.splitSize + "px";	//固定
		east_split.getEl().style.cursor = "e-resize";
		south_split.getEl().style.height = this.splitSize + "px";	//固定
		south_split.getEl().style.cursor = "n-resize";
		
		//$(this.wrapper_id).style.overflow = "hidden";
		$(this.wrapper_id).style.width = this.width + 2 + "px";
		$(this.wrapper_id).style.height = this.height + 2 + "px";
		
		east_split.getEl().onmousedown = function(){
			PanelManager.moveSplit("east");
		};
		
		south_split.getEl().onmousedown = function(){
			PanelManager.moveSplit("south");
		}
		
		//resizebox
		var resize = document.createElement("div");
		resize.id = this.wrapper_id + "resize";
		with(resize.style){
			position = "absolute"; 
			width = "10px";
			height = "10px";
			cursor = "se-resize";
			
			bottom  = 0;
			right  = 0;
		}
		
		$(this.wrapper_id).appendChild(resize);
		
		resize.onmousedown = function(e){
		
			var pos = PanelManager.getPosition($(wrapper_id));
			
			if(PanelManager.getPanel("west").getTabContainer())PanelManager.getPanel("west").getTabContainer().style.visibility = "hidden";
			if(PanelManager.getPanel("east").getTabContainer())PanelManager.getPanel("east").getTabContainer().style.visibility = "hidden";
			if(PanelManager.getPanel("south").getTabContainer())PanelManager.getPanel("south").getTabContainer().style.visibility = "hidden";			
			
			document.onmousemove = function(e){
				if(!e)e=event;
				var x = Event.pointerX(e);	
				var y = Event.pointerY(e);
				
				var newWidth = Math.max(PanelManager.oldwidth,x - pos.x);
				var newHeight = Math.max(PanelManager.oldheight,y - pos.y);
				
				var widthDiff = PanelManager.width - newWidth;
				var heightDiff = PanelManager.height - newHeight;
				
				$(wrapper_id).style.width = newWidth + 2 + "px";
				$(wrapper_id).style.height = newHeight + 2 + "px";
				
				PanelManager.width = newWidth;
				PanelManager.height = newHeight;
				
				var size = PanelManager.getPanel("west").getSize();
				PanelManager.resizeWestPanel(size.width - widthDiff,size.height - heightDiff);
			};
			
			resize.onmouseup = document.onmouseup = function(e){
				document.onmousemove = function(){};
				
				if(PanelManager.getPanel("west").getTabContainer())PanelManager.getPanel("west").getTabContainer().style.visibility = "visible";
				if(PanelManager.getPanel("east").getTabContainer())PanelManager.getPanel("east").getTabContainer().style.visibility = "visible";
				if(PanelManager.getPanel("south").getTabContainer())PanelManager.getPanel("south").getTabContainer().style.visibility = "visible";
			};
		};
	},
	
	add : function(id,option){
		if(!option)option = {};
		option.parent_id = this.wrapper_id;
		option.pos = id.replace("_panel","");
		option.showTab = this.showTab;
		this.panels[id] = new PanelManager.Panel(id,option);
		
		return this.panels[id];
	},
	
	addSplit : function(id,option){
		if(!option)option = {};
		option.parent_id = this.wrapper_id;
		option.pos = id.replace("_split","");
		this.split[id] = new PanelManager.Split(id,option);
		
		return this.split[id];
	},
	
	getPanel : function(pos){
		return this.panels[(pos + "_panel")];
	},
	
	getSplit : function(pos){
		return this.split[(pos + "_split")];
	},
	
	getWrapper : function(){
		return $(this.wrapper_id);
	},
	
	resizeWestPanel : function(width,height){
		
		width = Math.floor(Math.min(this.width,width));
		height = Math.floor(Math.min(this.height,height));
		
		//西パネルが無い時は幅は最大
		if(!this.getPanel("east").isVisible()){
			width = this.width;
		}
		
		//南パネルが無い時は高さ最大
		if(!this.getPanel("south").isVisible()){
			height = this.height;
		}
		var size = this.getPanel("west").getSize();

		this.getPanel("west").resize(width,height);
		
		var eastSize = this.getPanel("east").getSize();

		if(size.width != width){
			if(size.height != eastSize.height){
				this.getSplit("south").resizeWidth(width);
				this.getPanel("south").resize(width,null);
			}else{
				this.getSplit("south").resizeWidth(this.width);
				this.getPanel("south").resize(this.width,null);
			}
			
			this.getPanel("east").move(0,width + this.splitSize);
			this.getSplit("east").move(0,width);
			
			this.getPanel("east").resize(this.width - this.splitSize - width,null);
		}
		
		if(size.height != height){
			
			if(size.height == eastSize.height){
				this.getPanel("east").resize(null,height);
				this.getSplit("east").resizeHeight(height);
			}else{
				this.getPanel("east").resize(null,this.height);
				this.getSplit("east").resizeHeight(this.height);
			}
			
			this.getPanel("south").move(height+this.splitSize,0);
			this.getSplit("south").move(height,0);
			
			this.getPanel("south").resize(null,this.height - this.splitSize - height);
		}
	},
	
	resizeSouthHeight : function(height){
		var newHeight = this.height - this.splitSize - height
		var width = this.getPanel("west").getSize().width;
		this.resizeWestPanel(width,newHeight);
	},
	
	resizeEastWidth : function(width){
		var newWidth = this.width - this.splitSize - width
		var height = this.getPanel("south").getSize().height;
		this.resizeWestPanel(newWidth,height);
	},
	
	
	//右側のパネルを有効に
	activeEastPanel : function(){
		var width = (this.width - this.splitSize)/2;
		var size = this.getPanel("west").getSize();
		
		this.getPanel("west").resize(width,null);
		
		this.getSplit("east").getEl().show();
		this.getSplit("east").resizeHeight(size.height);
		this.getSplit("east").move(0,width);
		
		this.getPanel("east").show();
		this.getPanel("east").move(0,width + this.splitSize);
		
		this.getPanel("east").resize(width,size.height);
	},
	
	//左側のパネルを有効に
	activeSouthPanel : function(){
		var height = (this.height - this.splitSize)/2;
		var size = this.getPanel("west").getSize();
		
		this.getPanel("west").resize(null,height);
		
		this.getSplit("south").getEl().show();
		this.getSplit("south").resizeWidth(size.width);
		this.getSplit("south").move(height,0);
		
		this.getPanel("south").show();
		this.getPanel("south").move(height + this.splitSize,0);
		this.getPanel("south").resize(size.width,height);
	},
	
	inactiveEastPanel : function(){
		
		//隠す
		this.getSplit("east").getEl().hide();
		this.getPanel("east").hide();
		
		this.resizeEastWidth(0);
	},
	
	inactiveSouthPanel : function(){
		//隠す
		this.getSplit("south").getEl().hide();
		this.getPanel("south").hide();
		
		this.resizeSouthHeight(0);
	
	},
	
	
	activePanel : function(pos){
		if(pos == "south"){
			return this.activeSouthPanel();
		}
		
		if(pos == "east"){
			return this.activeEastPanel();
		}
	},
	
	inactivePanel : function(pos){
		if(pos == "south"){
			return this.inactiveSouthPanel();
		}
		
		if(pos == "east"){
			return this.inactiveEastPanel();
		}
	},
	
	moveSplit : function(pos){
			
			wrapper_id = PanelManager.wrapper_id;
			
			if(PanelManager.getPanel("west").getTabContainer())PanelManager.getPanel("west").getTabContainer().style.visibility = "hidden";
			if(PanelManager.getPanel("east").getTabContainer())PanelManager.getPanel("east").getTabContainer().style.visibility = "hidden";
			if(PanelManager.getPanel("south").getTabContainer())PanelManager.getPanel("south").getTabContainer().style.visibility = "hidden";
			
			$(wrapper_id).onmousemove = document.onmousemove = function(e){
				if(!e)e=event;
				var x = Math.floor(Event.pointerX(e));
				var y = Math.floor(Event.pointerY(e));
			
				var wrapper_pos = PanelManager.getPosition($(wrapper_id));
				
				if(x < (wrapper_pos.x))document.onmouseup();
				if(x > (wrapper_pos.x + $(wrapper_id).offsetWidth))document.onmouseup();
				if(y < (wrapper_pos.y))document.onmouseup();
				if(y > (wrapper_pos.y + $(wrapper_id).offsetHeight))document.onmouseup();
				
				if(x < (wrapper_pos.x + 92))x = wrapper_pos.x + 92;
				if(x > (wrapper_pos.x + $(wrapper_id).offsetWidth - 92))x = wrapper_pos.x + $(wrapper_id).offsetWidth - 92;
				if(y < (wrapper_pos.y + 30))y = wrapper_pos.y + 30;
				if(y > (wrapper_pos.y + $(wrapper_id).offsetHeight - 30))y = wrapper_pos.y + $(wrapper_id).offsetHeight - 30;
				
				if(pos == "south"){
					PanelManager.resizeWestPanel(
						PanelManager.getPanel("west").getSize().width,
						y - wrapper_pos.y - PanelManager.splitSize/2
					);
				}
				
				if(pos == "east"){
					PanelManager.resizeWestPanel(
						x - wrapper_pos.x - PanelManager.splitSize/2,
						PanelManager.getPanel("west").getSize().height
					);
				}
				
				//反転しないように
				Event.stop(e);
			};
			
			document.onmouseup = function(){
				document.onmousemove = null;
				$(wrapper_id).onmousemove = null;
				
				if(PanelManager.getPanel("west").getTabContainer())PanelManager.getPanel("west").getTabContainer().style.visibility = "visible";
				if(PanelManager.getPanel("east").getTabContainer())PanelManager.getPanel("east").getTabContainer().style.visibility = "visible";
				if(PanelManager.getPanel("south").getTabContainer())PanelManager.getPanel("south").getTabContainer().style.visibility = "visible";
			
			};
	},
	
	moveTab : function(tab_id,e){
		if(!e)e = event;
		
		var x = Event.pointerX(e);
		var y = Event.pointerY(e);
		Event.stop(e);
		
		if(!$(tab_id + "_moving")
			&& x > ($(tab_id).offsetTop + $(tab_id).offsetWidth + 10)){
			
			var moving = document.createElement("div");
			moving.setAttribute("id",tab_id + "_moving");
			moving.style.position = "absolute";
			moving.style.top = y + 10 +"px";
			moving.style.left = x + 10 +"px";
			moving.innerHTML = $(tab_id).firstChild.nodeValue;
			
			document.body.appendChild(moving);
			
			if(PanelManager.getPanel("west").getTabContainer()){
				PanelManager.getPanel("west").getTabContainer().style.visibility = "hidden";
			}
			if(PanelManager.getPanel("east").getTabContainer()){
				PanelManager.getPanel("east").getTabContainer().style.visibility = "hidden";
			}
			if(PanelManager.getPanel("south").getTabContainer()){
				PanelManager.getPanel("south").getTabContainer().style.visibility = "hidden";				
			}
		}
		
		//dropZone
		var ele = Event.element(e);
		
		if($(tab_id + "_moving_drop") && ele.getAttribute("id") != tab_id + "_moving_drop"){
			$(tab_id + "_moving_drop").parentNode.removeChild($(tab_id + "_moving_drop"));
		}
		
		if(!$(tab_id + "_moving_drop") && ele.getAttribute("id") && ele.getAttribute("id").match(/.*_container/)){
			var drop = document.createElement("div");
			drop.setAttribute("id",tab_id + "_moving_drop");
			drop.style.position = "absolute";
			drop.style.backgroundColor = "#ccffcc";
			drop.style.zIndex = "3000";
			drop.pos = ele.getAttribute("id").replace("_panel_container","");
			//$(PanelManager.wrapper_id).appendChild(drop);
			document.body.appendChild(drop);
			
			Position.clone(ele,drop);
			
			if(PanelManager.getPanel(drop.pos).tab_count < 1){
				this.stopMoveTab(tab_id,e);
			}
		}
		
		var moving = $(tab_id + "_moving");
		if(!moving)return this.stopMoveTab(tab_id,e);
		moving.style.top = y + 10 +"px";
		moving.style.left = x + 10 +"px";
	},
	
	stopMoveTab : function(tab_id,e){
		if(!e)e = event;
		
		var moving = $(tab_id + "_moving");
		if(!moving)return;
		document.body.removeChild(moving);
		
		if(PanelManager.getPanel("west").getTabContainer()){
			PanelManager.getPanel("west").getTabContainer().style.visibility = "visible";
		}
		if(PanelManager.getPanel("east").getTabContainer()){
			PanelManager.getPanel("east").getTabContainer().style.visibility = "visible";
		}
		if(PanelManager.getPanel("south").getTabContainer()){
			PanelManager.getPanel("south").getTabContainer().style.visibility = "visible";
		}		
		
		if(Event.element(e) == $(tab_id + "_moving_drop")){
			var pos = $(tab_id + "_moving_drop").pos;
			
			var old_pos = $(tab_id).panel_pos;

			if(pos == old_pos){
				if(old_pos == "west" && PanelManager.getPanel("west").getTabLength() == 1){
					//do nothing
				}else{
					
					var x = Event.pointerX(e);
					var y = Event.pointerY(e);
					
					var wrapper = $(PanelManager.wrapper_id);
					var wrapper_pos = PanelManager.getPosition(wrapper);

					var bottom = wrapper_pos.y + wrapper.offsetHeight;
					var right = wrapper_pos.x + wrapper.offsetWidth;
					
					//右パネルへ移動
					if( x < right && x > right - 100){
						pos = "east";
					}
					
					//下パネルへ移動
					if( y < bottom && y > bottom - 100){
						pos = "south";
					}
					
					if(pos == "east")PanelManager.activeEastPanel();
					if(pos == "south")PanelManager.activeSouthPanel();
				}
			}
			
			//移動処理
			if(pos != old_pos){
				if(old_pos == "west" && PanelManager.getPanel("west").getTabLength() == 1){
					//do nothing
				}else{
					PanelManager.getPanel(pos).addTab($(tab_id).firstChild.nodeValue,$(tab_id).targetElement);
					PanelManager.getPanel(old_pos).removeTab(tab_id);
				}
			}
		}		
		
		if($(tab_id + "_moving_drop"))$(tab_id + "_moving_drop").parentNode.removeChild($(tab_id + "_moving_drop"));
				
		
	},
	
	getPosition : function(ele,flag){
		var x = ele.offsetLeft;
		var y = ele.offsetTop;
		
		if(ele.parentNode != undefined){
			
			var position = (ele.parentNode.style) ? Element.getStyle(ele.parentNode,"position") : "static";
			if(flag == true
				&& Element.getStyle(ele,"position") != "relative"
				&& Element.getStyle(ele,"position") != "absolute"){
				x = y = 0;
			}
			var pos = PanelManager.getPosition(ele.parentNode,true);
			
			if(!isNaN(pos.x))x += pos.x;
			if(!isNaN(pos.y))y += pos.y;
		}
		
		var res = {};
		res.x = x;
		res.y = y;
		
		return res;
	}
};


function debug(str){
	if(!$("debug")){
		var ele = document.createElement("div");
		ele.setAttribute("id","debug");
		ele.style.position = "absolute";
		ele.right = "0px";
		ele.top = "0px";
		document.body.appendChild(ele);
	}
	
	$(debug).innerHTML = str;
}

PanelManager.Panel = function(id,option){
	this.initialize(id,option);
};

PanelManager.Panel.prototype = {
	
	id : "",
	pos : "",
	top : 0,
	left : 0,
	width : 0,
	height: 0,
	
	active_tab_id : "",
	tab_count : 0,
	
	initialize : function(id,option){
		
		this.id = id;
		this.pos = option.pos;
		this.parent_id = option.parent_id;
		this.width = (option.width) ? option.width : 0;
		this.height = (option.height) ? option.height :0;
		
		var div = document.createElement("div");
		div.setAttribute("id",this.id);
		with(div.style){
			position = "absolute";
			visibility = "hidden";
		};
		$(this.parent_id).appendChild(div);
		
		var tab = document.createElement("div");
		tab.setAttribute("id",this.id + "_tab");
		tab.style.overflow = "hidden";
		
		if(option.showTab != true){
			tab.style.display = "none";
			this.tabHeight = 0;
		}
		this.getEl().appendChild(tab);
		
		//タブ下の線を追加
		var tab_underline = document.createElement("div");
		tab_underline.style.borderBottom = "1px solid #cccccc";
		tab_underline.style.marginTop = "27px";
		tab_underline.setAttribute("class","panel_tab_underline");
		tab_underline.setAttribute("className","panel_tab_underline");
		this.getTabEl().appendChild(tab_underline);
		
		var container = document.createElement("div");
		container.setAttribute("id",this.id + "_container");
		container.setAttribute("class","panel_container");
		container.setAttribute("className","container_tab");
		this.getEl().appendChild(container);
		container.style.position = "relative";
		container.style.overflow = "hidden";
		
		this.resize(this.width,this.height);
		
	},
	
	getEl : function(){
		return $(this.id);
	},
	
	getTabEl : function(){
		return $(this.id + "_tab");
	},
	
	getContainerEl : function(){
		return $(this.id + "_container");
	},
	
	getSize : function(){
		var el = this.getEl();
		return {
			top : Math.floor(el.offsetTop),
			left: Math.floor(el.offsetLeft),
			width : Math.floor(this.width),
			height: Math.floor(this.height)
		};
	},
	
	isVisible : function(){
		return (this.getEl().style.visibility == "visible");
	},
	
	show : function(){
		this.getEl().style.visibility = "visible";
	},
	
	hide : function(){
		this.getEl().style.visibility = "hidden";
	},
	
	resize : function(width,height){
		
		if(!width || width < 0)width = this.width;
		if(!height || height < 0)height = this.height;
		
		this.width = width;
		this.height = height;
		
		this.getEl().style.width = width + "px";
		this.getEl().style.height = height + "px";
		
		//border用の調節
		var diff = (document.all) ? 10 : 12;

		this.getTabEl().style.height =  PanelManager.tabHeight + "px";
		this.getContainerEl().style.height = Math.max(0,(height - PanelManager.tabHeight - diff)) + "px";
		this.getContainerEl().style.width = Math.max(0,(width - diff)) + "px";
		
		if(this.getTabContainer() && this.getTabContainer().onresize)this.getTabContainer().onresize(this.getContainerEl(),this.getTabContainer());

	},
	
	move : function(top,left){
		//差分
		var top_diff = top - this.top;
		var left_diff = left - this.left;
		
		this.top = top;
		this.left = left;
		
		this.getEl().style.top = top + "px";
		this.getEl().style.left = left + "px";
		
		if(this.width && this.height){		
			this.resize(
				((left_diff == 0) ? null : this.width - left_diff),
				((top_diff == 0) ? null : this.height - top_diff)
			);
		}
	},
	
	
	addTab : function(label,element,option,flag){
		
		if(!option)option = (element.option) ? element.option : {};
		if(!this.isVisible())PanelManager.activePanel(this.pos);
		
		var new_tab_id = this.id + "_tab_" + element.getAttribute("id");
		var new_container_id = this.id + "_container_" + element.getAttribute("id");
		
		var tab = document.createElement("div");
		tab.setAttribute("id",new_tab_id);
		tab.innerHTML = label;
		tab.style.cursor = "pointer";
		tab.setAttribute("class","panel_tab");		
		tab.setAttribute("className","panel_tab");
		var panel = this;
		tab.onclick = function(){
			panel.activeTab(new_tab_id);
		};
		
		tab.onmousedown = function(e){
			if(!e)e=event;
			document.onmousemove = function(e){
				PanelManager.moveTab(tab.id,e);
			};
			
			document.onmouseup = function(e){
				document.onmousemove = null;
				PanelManager.stopMoveTab(tab.id,e);
			};
			
			Event.stop(e);
		}
		
		this.getTabEl().insertBefore(tab,this.getTabEl().lastChild);
		
		if(option.deletable != false){
			var close = document.createElement("span");
			close.innerHTML = "[x]";
			close.onclick = function(){
				panel.removeTab(this.parentNode.getAttribute("id"));
			};
			tab.appendChild(close);
		}
		
		var container = document.createElement("div");
		container.setAttribute("id",new_container_id);		
		container.appendChild(element);
		this.getContainerEl().appendChild(container);
		container.style.position = "relative";
		container.style.height = "100%";
		container.style.width = "100%";
		container.onactive = (option.onactive) ? option.onactive : null;
		container.onresize = (option.onresize) ? option.onresize : null;
		
		//タブのIDを保存しておく
		element.tab_id = new_tab_id;
		element.container_id = new_container_id;
		element.option = option;
		tab.panel_pos = element.panel_pos = this.pos;
		tab.targetElement = element;
		
		this.tab_count++;
		
		//追加したタブをアクティブに
		if(!flag || flag != false)this.activeTab(new_tab_id);
	},
	
	removeTab : function(id){
		if(!$(id))return;
		
		var no_tab = false;
		
		//有効なタブだった場合
		if(id == this.active_tab_id){
			if($(id).nextSibling && $(id).nextSibling.getAttribute("class") != "panel_tab_underline"){
				this.activeTab($(id).nextSibling.getAttribute("id"));
			}else if($(id).previousSibling && $(id).previousSibling.getAttribute("class") != "panel_tab_underline"){
				this.activeTab($(id).previousSibling.getAttribute("id"));
			}else{
				no_tab = true;
			}
		}
		
		this.getTabEl().removeChild($(id));
		this.getContainerEl().removeChild($(id.replace(this.id + "_tab_",this.id + "_container_")));
		
		if(no_tab){
			this.active_tab_id = "";
			PanelManager.inactivePanel(this.pos);
		}
		
		this.tab_count--;
	},
	
	activeTab : function(id){
		
		if(!$(id))return;
		
		//現在のコンテナを隠す
		if(this.active_tab_id){
			this.getTabContainer(this.active_tab_id).hide();
			$(this.active_tab_id).setAttribute("className", "panel_tab_inactive");
			$(this.active_tab_id).setAttribute("class", "panel_tab_inactive");
		}		
		//新しいコンテナを表示
		this.active_tab_id = id;
		this.getTabContainer(this.active_tab_id).show();
		$(this.active_tab_id).style.backgroundColor = "yellow";
		
		if(this.getTabContainer() && this.getTabContainer().onresize)this.getTabContainer().onresize(this.getContainerEl(),this.getTabContainer());
		if(this.getTabContainer() && this.getTabContainer().onactive)this.getTabContainer().onactive();
		
		$(this.active_tab_id).setAttribute("className", "panel_tab");
		$(this.active_tab_id).setAttribute("class", "panel_tab");
	},
	
	getTabContainer:function(id){
		if(!id)id = this.active_tab_id;
		return $(id.replace(this.id + "_tab_", this.id + "_container_"));
	},
	
	getTabLength : function(){
		return this.tab_count;
	}

};


PanelManager.Split = function(id,option){
	this.initialize(id,option);
}

PanelManager.Split.prototype = {
	
	id : "",
	parent_id : "",
	pos : "",
	
	initialize : function(id,option){
		
		this.id = id;
		this.parent_id = option.parent_id;

		var split = document.createElement("div");
		split.setAttribute("id",id);
		
		with(split.style){
			position = "absolute";
			fontSize = "0px";
		}
		
		$(this.parent_id).appendChild(split);
		
		this.getEl().hide();
	},
	
	getEl : function(){
		return $(this.id);
	},
	
	resizeHeight :function(height){
		if(height < 0)return;
		this.getEl().style.height = height + "px";
	},
	
	resizeWidth : function(width){
		if(width < 0)return;
		this.getEl().style.width = width + "px";
	},
	
	move : function(top,left){
		this.getEl().style.top = top + "px";
		this.getEl().style.left = left + "px";
	}
};
