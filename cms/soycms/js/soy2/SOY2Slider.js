/* 
いわゆるスライダーを作成
*/

var SOY2Slider = function(target,option){
	this.init(target,option);
};

SOY2Slider.prototype = {
	
	min : 0,
	max : 255,
	value : 0,
	target : "",
	onchange : null,
	
	cursor_width : 11,
	
	init : function(target, option){
		
		if(!option){
			option = {
				min : 0,
				max : 255
			};
		}
		
		this.min = option.min;
		this.max = option.max;
		if(option.onchange){
			this.onchange = option.onchange;
		}
		
		this.target = target;
		
		//スライダを作成
		$(target).innerHTML = '<div style="position:relative;">'
				+'<input size="3" style="float:right;width:30px;" "type="text" id="'+target+'_current" />'
				+'<div class="slider_bg"><!-- dummy --></div> '
				+'<div id="'+target+'_cursor" class="slider_cursor"><!-- dummy --></div>'
				+'</div>';

		$(target).slider = this;
		
		//カーソルの動作
		var slider = this;
		var pos = this.getPosition($(target + '_cursor').parentNode);
		$(this.target + '_cursor').onmousedown = function(){
			document.onmousemove = function(e){
				if(!e)e=event;
				var x = Event.pointerX(e) - pos.x;
				
				if(x < 5){
					x = 5;
				}
				
				if(x > 205){
					x = 205;
				}
				
				var tmp = ((x - 5) < 1) ? 0 : (x - 5)/200;
				slider.setValue(Math.round(tmp * slider.max + slider.min),true);
				$(target + '_cursor').style.left = x + "px";
			};
			
			$(target + '_cursor').onmouseup = document.onmouseup = function(e){
				document.onmousemove = function(){};
			};
		};
	},
	
	
	setValue : function(value, flag){
		this.value = value;
		$(this.target + '_current').value = this.value;
		if(flag != true)this.moveCursor();
		
		if(this.onchange){
			this.onchange();
		}
	},
	
	getValue : function(){
		return this.value;
	},
	
	
	moveCursor : function(){
		var x = Math.round((this.value - this.min)/this.max * 200 + 5);
		$(this.target + '_cursor').style.left = x + "px";
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
			var pos = this.getPosition(ele.parentNode,true);
			
			if(!isNaN(pos.x))x += pos.x;
			if(!isNaN(pos.y))y += pos.y;
		}
		
		var res = {};
		res.x = x;
		res.y = y;
		
		return res;
	}	
	

};



