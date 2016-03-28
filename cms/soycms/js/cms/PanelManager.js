var PanelManager = {
	
	wrapper_id : "",
	wrapper: null,//jQuery Object
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
		this.wrapper = $("#"+wrapper_id);
		
		this.showTab = (option && option.showTab != undefined) ? option.showTab : true;
		if(this.showTab != true)this.tabHeight = 0;
		
		this.width  = this.oldwidth  = this.wrapper.prop("offsetWidth");
		this.height = this.oldheight = this.wrapper.prop("offsetHeight");
		
		this.add("west_panel",{width:this.width,height:this.height});
		var east_split = this.addSplit("east_split");
		this.add("east_panel");
		
		var south_split = this.addSplit("south_split");
		this.add("south_panel");
		
		east_split.getEl().css({
			"width" : this.splitSize + "px",//固定
			"cursor": "e-resize"
		});	
		south_split.getEl().css({
			"height": this.splitSize + "px",//固定
			"cursor": "n-resize"
		});
		
		//this.wrapper.style.overflow = "hidden";
		this.wrapper.css({
			"width" : this.width + 2 + "px",
			"height": this.height + 2 + "px"
		});
		
		east_split.getEl().mousedown(function(){
			PanelManager.moveSplit("east");
		});
		
		south_split.getEl().mousedown(function(){
			PanelManager.moveSplit("south");
		});
		
		//resizebox（右下の角）
		var resize = $("<div/>")
			.prop("id",this.wrapper_id + "resize")
			.css({
			"position": "absolute",
			"width"   : "10px",
			"height"  : "10px",
			"cursor"  : "se-resize",
			"bottom"  : 0,
			"right"   : 0
			});
		this.wrapper.append(resize);

		resize.mousedown(function(e){
			var pos = PanelManager.getPosition(PanelManager.wrapper);
			
			PanelManager.hidePanels();
			
			$(document).mousemove(function(e){
				var x = e.pageX;
				var y = e.pageY;

				var newWidth  = Math.max(PanelManager.oldwidth,  x - pos.x);
				var newHeight = Math.max(PanelManager.oldheight, y - pos.y);
				
				var widthDiff = PanelManager.width - newWidth;
				var heightDiff = PanelManager.height - newHeight;
				
				PanelManager.wrapper.width(newWidth + 2);
				PanelManager.wrapper.height(newHeight + 2);
				
				PanelManager.width = newWidth;
				PanelManager.height = newHeight;
				
				var size = PanelManager.getPanel("west").getSize();
				PanelManager.resizeWestPanel(size.width - widthDiff,size.height - heightDiff);
			});

			$(document).mouseup(function(){
				PanelManager.showPanels();
				$(document).unbind("mousemove");
				$(document).unbind("mouseup");
			});
		});


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
		return this.wrapper;
	},
	
	hidePanels : function(){
		if(PanelManager.getPanel("west").getTabContainer() )PanelManager.getPanel("west").getTabContainer().css("visibility", "hidden");
		if(PanelManager.getPanel("east").getTabContainer() )PanelManager.getPanel("east").getTabContainer().css("visibility", "hidden");
		if(PanelManager.getPanel("south").getTabContainer())PanelManager.getPanel("south").getTabContainer().css("visibility", "hidden");
	},
	showPanels : function(){
		if(PanelManager.getPanel("west").getTabContainer() )PanelManager.getPanel("west").getTabContainer().css("visibility", "visible");
		if(PanelManager.getPanel("east").getTabContainer( ))PanelManager.getPanel("east").getTabContainer().css("visibility", "visible");
		if(PanelManager.getPanel("south").getTabContainer())PanelManager.getPanel("south").getTabContainer().css("visibility", "visible");
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
		
		//this.getPanel("east").getEl().show();
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
		
		//this.getPanel("south").getEl().show();
		this.getPanel("south").show();
		this.getPanel("south").move(height + this.splitSize,0);
		this.getPanel("south").resize(size.width,height);
	},
	
	inactiveEastPanel : function(){
		
		//隠す
		this.getSplit("east").getEl().hide();
		//this.getPanel("east").getEl().hide();
		this.getPanel("east").hide();
		
		this.resizeEastWidth(0);
	},
	
	inactiveSouthPanel : function(){
		//隠す
		this.getSplit("south").getEl().hide();
		//this.getPanel("south").getEl().hide();
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

		var $wrapper = PanelManager.wrapper;

		PanelManager.hidePanels();
				
		$(document).mousemove(function(e){
				if(!e) e = event;

				var x = e.pageX;
				var y = e.pageY;

				var wrapper_pos = PanelManager.getPosition($wrapper);
					
				if(x < (wrapper_pos.x)) $(document).mouseup();
				if(x > (wrapper_pos.x + $wrapper.prop("offsetWidth"))) $(document).mouseup();
				if(y < (wrapper_pos.y)) $(document).mouseup();
				if(y > (wrapper_pos.y + $wrapper.prop("offsetHeight"))) $(document).mouseup();
				
				if(x < (wrapper_pos.x + 92))x = wrapper_pos.x + 92;
				if(x > (wrapper_pos.x + $wrapper.prop("offsetWidth") - 92))x = wrapper_pos.x + $wrapper.prop("offsetWidth") - 92;
				if(y < (wrapper_pos.y + 30))y = wrapper_pos.y + 30;
				if(y > (wrapper_pos.y + $wrapper.prop("offsetHeight") - 30))y = wrapper_pos.y + $wrapper.prop("offsetHeight") - 30;
				
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

				return false;
		});


		$(document).mouseup(function(){
			PanelManager.showPanels();
			$(document).unbind("mousemove");
			$(document).unbind("mouseup");
		});
	},
	
	moveTab : function(tab_id, e){
		if(!e) e = event;
		
		var x = e.pageX;
		var y = e.pageY;

		var $tab = $("#"+tab_id);
		var $moving = $("#"+tab_id + "_moving");

		var tab_offset = $tab.offset();

		if($moving.length == 0){
			$moving = $("<div>", {id:tab_id+"_moving"});
			$moving.css("position", "absolute");
			$moving.css("top", y+10);
			$moving.css("left", x+10);
			$moving.html($tab.text());
			$("body").append($moving);
			PanelManager.hidePanels();
		}
			
		//dropZone
		var $ele = $(e.target);
		var $movingDrop = $("#"+tab_id + "_moving_drop");
		if($movingDrop.length > 0 && $ele.prop("id") != tab_id + "_moving_drop"){
			$movingDrop.remove();
		}
		
		//TODO 未検証
		if($movingDrop.length == 0 && $ele.prop("id").length > 0 && $ele.prop("id").match(/.*_container/)){
			var $drop = $("<div>", {id:tab_id+"_moving_drop"});
			$drop.css("position", "absolute");
			$drop.css("background-color", "#ccffcc");
			$drop.css("z-index", 3000);
			var pos = $ele.prop("id").replace("_panel_container","");
			$drop.prop("pos", pos);
			$("body").append($drop);

			$drop.offset($ele.offset());
			$drop.width($ele.width());
			$drop.height($ele.height());
			
			if(PanelManager.getPanel(pos).tab_count < 1){
				this.stopMoveTab(tab_id,e);
			}
		}
		
		if($moving.length==0){
			return this.stopMoveTab(tab_id,e);
		}
		
		$moving.css("top", y + 10);
		$moving.css("left",x + 10);
	},
	
	stopMoveTab : function(tab_id, e){
		if(!e)e = event;
		
		var $moving = $("#"+tab_id + "_moving");
		if($moving.length == 0)return;
		$moving.remove();

		PanelManager.showPanels();
		
		if($(e.target).prop("id") == tab_id + "_moving_drop"){
			var pos = $("#"+tab_id + "_moving_drop").prop("pos");
			
			var old_pos = $("#"+tab_id).prop("panel_pos");

			if(pos == old_pos){
				if(old_pos == "west" && PanelManager.getPanel("west").getTabLength() == 1){
					//do nothing
				}else{
					
					var x = e.pageX;
					var y = e.pageY;
					
					var $wrapper = PanelManager.wrapper;
					var wrapper_pos = PanelManager.getPosition($wrapper);

					var bottom = wrapper_pos.y + $wrapper.prop("offsetHeight");
					var right = wrapper_pos.x + $wrapper.prop("offsetWidth");

					console.log("x="+x+", y="+y+", r="+right+", b="+bottom);
						
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
					$tmp = $("#"+tab_id).clone();
					$tmp.children().remove();
					PanelManager.getPanel(pos).addTab($tmp.text(), $("#"+tab_id).prop("targetElement"));
					PanelManager.getPanel(old_pos).removeTab(tab_id);
				}
			}
		}		
		
		if($("#" + tab_id + "_moving_drop")){
			$("#" + tab_id + "_moving_drop").remove();
		}
				
		
	},
	
	getPosition : function($ele, flag){
		var x = $ele[0].offsetLeft;
		var y = $ele[0].offsetTop;

		if($ele.parent().length > 0){
			var $parent = $ele.parent();
			if(flag == true && $ele.css("position") != "relative" && $ele.css("position") != "absolute"){
				x = y = 0;
			}
			var pos = PanelManager.getPosition($parent, true);
			
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
	if($("#debug").size() < 1){
		var $ele = $("<div>", {id:"debug"});
		$ele.css({
			position: "absolute",
			right: "0px",
			top: "0px"
		});
		$("body").append($ele);
	}
	
	$("#debug").text(str);
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
	
	//jQuery Object
	panel: null,
	tab  : null,
	container: null,
	
	initialize : function(id,option){
		
		this.id = id;
		this.pos = option.pos;
		this.parent_id = option.parent_id;
		this.width = (option.width) ? option.width : 0;
		this.height = (option.height) ? option.height :0;
		
		this.panel = $("<div/>")
			.attr("id",this.id)
			.css({
				"position"  : "absolute",
				"visibility": "hidden"
			});
		$("#" + this.parent_id).append(this.panel);
		
		this.tab = $("<div/>")
			.attr("id",this.id + "_tab")
			.css("overflow","hidden");
		
		if(option.showTab != true){
			this.tab.css("display","none");
			this.tabHeight = 0;
		}
		this.panel.append(this.tab);
		
		//タブ下の線を追加
		var tab_underline = $("<div/>")
			.css({
				"borderBottom": "1px solid #cccccc",
				"marginTop"   : "27px"
			})
			.addClass("panel_tab_underline");
		this.tab.append(tab_underline);
		
		this.container = $("<div/>")
			.attr("id",this.id + "_container")
			.addClass("panel_container")
			.css({
				"position": "relative",
				"overflow": "hidden"
			})
		this.panel.append(this.container);
		
		this.resize(this.width,this.height);
	},
	
	getEl : function(){
		return this.panel;
	},
	
	getTabEl : function(){
		return this.tab;
	},
	
	getContainerEl : function(){
		return this.container;
	},
	
	getSize : function(){
		var el = this.getEl();
		var offset = el.offset();
		return {
			top : Math.floor(offset.top),
			left: Math.floor(offset.left),
			width : Math.floor(this.width),
			height: Math.floor(this.height)
		};
	},
	
	isVisible : function(){
		return (this.panel.css("visibility") == "visible");
	},
	
	show : function(){
		this.panel.css("visibility","visible")
		          .show();
	},
	
	hide : function(){
		this.panel.css("visibility","hidden")
		          .hide();
	},
	
	resize : function(width, height){
		
		if(!width  || width  < 0) width  = this.width;
		if(!height || height < 0) height = this.height;
		
		this.width = width;
		this.height = height;
		
		this.panel.width(width).height(height);
		
		//border用の調節
		var diff = ($("body").html()) ? 10 : 12;

		this.tab.height(PanelManager.tabHeight);
		this.container
			.height(Math.max(0,(height - PanelManager.tabHeight - diff)))
			.width(Math.max(0,(width - diff)));
		
		var $tabContainer = this.getTabContainer();
		var option = $tabContainer.prop("option") ? $tabContainer.prop("option") : {};
		if($tabContainer.length && option.onresize){
			option.onresize(this.container, $tabContainer);
		}

	},
	
	move : function(top,left){
		//差分
		var top_diff = top - this.top;
		var left_diff = left - this.left;
		
		this.top = top;
		this.left = left;
		
		this.getEl().css({
			top : top+"px",
			left: left+"px"
		});
		
		if(this.width && this.height){		
			this.resize(
				((left_diff == 0) ? null : this.width - left_diff),
				((top_diff  == 0) ? null : this.height - top_diff)
			);
		}
	},
	
	
	addTab : function(label, $element, option, flag){
		if(!$element.length){
			return;
		}

		if(!option) option = $element.prop("option") ? $element.prop("option") : {};
		if(!this.isVisible())PanelManager.activePanel(this.pos);
		
		var new_tab_id = this.id + "_tab_" + $element.attr("id");
		var new_container_id = this.id + "_container_" + $element.attr("id");

		var $tab = $("<div>", {id:new_tab_id}).addClass("panel_tab").css("cursor", "pointer").text(label);
		var panel = this;
		$tab.click(function(){
			panel.activeTab(new_tab_id);
		});
		
		$tab.mousedown(function(e){
			$(document).mousemove(function(e){
				PanelManager.moveTab(new_tab_id,e);
			});
			
			$(document).mouseup(function(e){
				$(document).unbind("mousemove");
				PanelManager.stopMoveTab(new_tab_id,e);
			});
			
			return false;
		});
		
		this.getTabEl().children(":last").before($tab);
		
		if(option.deletable != false){
			var close = $("<span>");
			close.html("[x]");
			close.click(function(){
				if(option.onclose)option.onclose();
				panel.removeTab($(this).parent().attr("id"));
			});
			$tab.append(close);
		}
		
		var $container = $("<div>", {id: new_container_id});
		$container.append($element);
		this.getContainerEl().append($container);
		$container.css({
				position: "relative",
				height: "100%",
				width: "100%"
		});
		
		var active = (option.onactive) ? option.onactive : null;
		var resize = (option.onresize) ? option.onresize : null;

		$container.prop("option", {
			onactive: active,
			onresize: resize
		});
		
		//タブのIDを保存しておく
		$element.prop({
				tab_id: new_tab_id,
				container_id: new_container_id,
				option: option,
				panel_pos: this.pos
		});

		$tab.prop({
				panel_pos: this.pos,
				targetElement: $element
		});
		
		this.tab_count++;
		
		//追加したタブをアクティブに
		if(!flag || flag != false) this.activeTab(new_tab_id);
	},
	
	removeTab : function(id){
		if(id.length <= 0) return;
		
		var no_tab = false;
		
		//有効なタブだった場合
		if(id == this.active_tab_id){
			var $currentTab = $("#" + id);
			if($currentTab.next().length && !$currentTab.next().hasClass("panel_tab_underline")){
				this.activeTab($currentTab.next().attr("id"));
			}else if($currentTab.prev().length && !$currentTab.prev().hasClass("panel_tab_underline")){
				this.activeTab($currentTab.prev().attr("id"));
			}else{
				no_tab = true;
			}
				
			//if($(id).nextSibling && $(id).nextSibling.getAttribute("class") != "panel_tab_underline"){
//				this.activeTab($(id).nextSibling.getAttribute("id"));
//			}else if($(id).previousSibling && $(id).previousSibling.getAttribute("class") != "panel_tab_underline"){
//				this.activeTab($(id).previousSibling.getAttribute("id"));
//			}else{
//				no_tab = true;
//			}
		}
		
		//this.getTabEl().removeChild($(id));
		//this.getContainerEl().removeChild($(id.replace(this.id + "_tab_",this.id + "_container_")));
		this.getTabEl().find("#"+id).remove();
		this.getContainerEl().find("#"+id.replace(this.id + "_tab_",this.id + "_container_")).remove();
		
		if(no_tab){
			this.active_tab_id = "";
			PanelManager.inactivePanel(this.pos);
		}
		
		this.tab_count--;
	},
	
	activeTab : function(id){
		
		if(id.length <0 || !$(id))return;
		
		//現在のコンテナを隠す
		if(this.active_tab_id){
			this.getTabContainer(this.active_tab_id).hide();
			$("#"+this.active_tab_id).addClass("panel_tab_inactive").removeClass("panel_tab");
		}		
		//新しいコンテナを表示
		this.active_tab_id = id;
		this.getTabContainer(this.active_tab_id).show();
		//$("#" + this.active_tab_id).css("backgroundColor","yellow");
		
		var $tabContainer = this.getTabContainer();
		var option = $tabContainer.prop("option") ? $tabContainer.prop("option") : {};
		if($tabContainer.length && option.onresize) option.onresize(this.getContainerEl(), $tabContainer);
		if($tabContainer.length && option.onactive) option.onactive();
			
		$("#"+this.active_tab_id).removeClass("panel_tab_inactive").addClass("panel_tab");
	},
	
	getTabContainer : function(id){
		if(!id) id = this.active_tab_id;
		var tabContainerId = id.replace(this.id + "_tab_", this.id + "_container_")
		if(tabContainerId.length){
			return $("#" + tabContainerId);
		}else{
			return $();
		}
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
	
	split: null,//jQuery Object
	
	initialize : function(id,option){
		
		this.id = id;
		this.parent_id = option.parent_id;

		this.split = $("<div/>")
			.attr("id",id)
			.css({
				"position" : "absolute",
				"font-size": "0px",
				"display"  : "none"
			});

		$("#" + this.parent_id).append(this.split);
		
	},
	
	getEl : function(){
		return this.split;
	},
	
	resizeHeight :function(height){
		if(height < 0)return;
		this.split.height(height);
	},
	
	resizeWidth : function(width){
		if(width < 0)return;
		this.split.width(width);
	},
	
	move : function(top,left){
		this.split.css({
			top : top + "px",
			left: left + "px"
		});
	}
};
