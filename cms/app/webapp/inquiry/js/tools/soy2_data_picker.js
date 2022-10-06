var SOY2_DatePicker = function(){};

$.extend(SOY2_DatePicker,{

	date_pickers : [],

	show : function(obj,option){

		if(!option){
			option = {
				show_future : false
			};
		}
		
		if(!obj.date_picker){
			option = $.extend(option);

			obj.date_picker = new SOY2_DatePicker();
			obj.date_picker.init(obj,this.date_pickers.length,option);
			this.date_pickers.push(obj.date_picker);


		}
		obj.date_picker.render();

		for(var i=0; i<this.date_pickers.length; i++){
			if(obj.date_picker.id != this.date_pickers[i].id){
				this.date_pickers[i].close();
			}
		}
	},


	hide : function(obj){

		if(obj.date_picker){
			obj.date_picker.close();
		}

	},

	hideAll : function(){

		for(var i=0; i<this.date_pickers.length; i++){
			this.date_pickers[i].close();
		}

	},

	check : function(obj){

		if(obj.date_picker){
			obj.date_picker.check($(obj).val());
		}

	}

});

$.extend(SOY2_DatePicker.prototype,{

	id : null,
	node : null,
	element : null,

	year:null,
	month:null,
	date:null,

	show_future : false,

	today : null,

	init : function(node,counter,option){
		this.node = node;
		this.id = "soy2_date_picker_" + counter;
		$.extend(this,option);
		this.today = new Date();
	},

	render : function(){
		if(!this.element){
			this.element = this.buildElement();
		}

		this.element.css({
			"left" : $(this.node).position().left
		});

		this.element.show();
	},

	close : function(){
		if(this.element)
			return this.element.hide();
	},

	check : function(val){

		if(val.match(/\d{4}-\d{2}-\d{2}/)){

			var array = val.split("-");
			this.setValue(array[0],array[1] - 1,array[2]);

			var date = this.date;
			$("#" + this.id + " .cell").each(function(){
				if($(this).attr("date") == date){
					$(this).addClass("selected");
				}
			});
		}else{
			//unset selected
			$("#" + this.id + " .selected").removeClass("selected");
		}

		return true;

	},

	buildElement : function(){
		var ele = $('<div class="soy2_date_picker">' +
				'<table class="soy2_date_picker_table"><thead><tr class="month_select"><th colspan="7">'+
				'<select class="month_select"></select>'+
				'<select class="year_select"></select>'+
				'</th></tr></thead>'+

				'<tbody><tr class="day_row">' +
				'<th class="holiday_cell">Sun</th><th>Mon</th><th>Tue</th><th>Wed</th><th>Thu</th><th>Fri</th><th class="holiday_cell">Sat</th>'+
				'</tr></tbody>' +
				'' +

				'<tfoot><tr class="next_prev"><th colspan="7">' +
				'<a href="javascript:void(0);" class="prev_btn">&lt;Prev</a>' +
				'<a href="javascript:void(0);" class="today_btn">Today</a>' +
				'<a href="javascript:void(0);" class="next_btn">Next&gt;</a>' +
				'</th></tr></tfoot>'+

				'</tbody></table></div>'
		);

		ele.attr("id",this.id);
		$(this.node).after(ele);

		var inst = this;

		//select boxを作成
		var options = "";
		for(var i=1;i<=12;i++){
			options += '<option value="'+(i-1)+'">'+i+'</option>';
		}
		$("select.month_select",ele).append(options);

		//event
		$("select.year_select",ele).bind("change",function(){
			inst.setValue($(this).val(),inst.month);
		});

		var options = "";
		var max_year = (this.show_future) ? this.today.getFullYear() + 10 : this.today.getFullYear();
		for(var i=1990;i<=max_year+3;i++){
			options += '<option value="'+i+'">'+i+'</option>';
		}
		$("select.year_select",ele).append(options);

		//event
		$("select.month_select",ele).bind("change",function(){
			inst.setValue(inst.year,$(this).val());
		});

		//prev next
		$(".prev_btn",ele).bind("click",function(){
			inst.moveMonth(-1);
		});
		$(".next_btn",ele).bind("click",function(){
			inst.moveMonth(+1);
		});
		$(".today_btn",ele).bind("click",function(){
			inst.setValue(inst.today);
		});

		//値を取得
		this.parseValue();
		var has_value = false;
		if($(this.node).val().match(/\d{4}-\d{2}-\d{2}/)){
			var array = $(this.node).val().split("-");
			this.setValue(array[0],array[1] - 1,array[2]);
			has_value = true;
		}

		//日付部分を作成
		this.buildCalendar(true);

		if(has_value){
			var date = this.date;
			$("#" + this.id + " .cell").each(function(){
				if($(this).attr("date") == date){
					$(this).addClass("selected");
				}
			});
		}

		return ele;
	},

	buildCalendar : function(flag){
		var counter = 1;

		//箱だけ作る
		if(flag){
			var body = $("#" + this.id + " tbody");
			for(var i=0;i<6;i++){
				var tr = $("<tr>");
				tr.addClass("week");
				tr.addClass("row-" + i);

				for(var j=0;j<7;j++){
					var td = $("<td></td>");
					td.attr("id",this.id + "_cell_" + counter);
					td.addClass("cell");
					if(j==0 || j==6)td.addClass("holiday_cell");
					tr.append(td);

					counter++;
				}

				body.append(tr);
			}
		}


		var first = (new Date(this.year,this.month,1)).getDay();
		var last_date = (new Date(this.year,this.month+1,0));
		var last = last_date.getDate();
		var day_counter = 1;
		var year = this.year;
		var month = this.month;
		var counter = 42;

		var inst = this;

		for(var i=1;i<=counter;i++){

			var cell = $("#" + this.id + "_cell_" + i);

			//empty event
			cell.bind("click",function(){});

			//empty
			if(i <= first){
				cell.html("");
				continue;
			}

			if(day_counter > last){
				cell.html("");
				continue;
			}

			html = '<a href="javascript:void(0);">'+ day_counter +'</a>';
			cell.html(html);

			cell.attr("date",day_counter);

			$("#" + this.id + "_cell_" + i).bind("click",function(){
				inst.selectValue(year,month,$(this).attr("date"));
				$(this).addClass("selected");
			});

			day_counter++;
		}

		//使っていない週を隠す
		var max_week = Math.floor((last + 6 - last_date.getDay() - 1) / 7);	//仮想週の金曜日

		for(var i=0;i<6;i++){
			if(i > max_week){
				$("#" + this.id + " .row-" + i).hide();
			}else{
				$("#" + this.id + " .row-" + i).show();
			}

		}

		return;
	},

	parseValue : function(){
		if($(this.node).val().length < 1){
			var date = this.today;
			this.setValue(this.today);
		}
	},

	/*
	 * click calendar cell
	 */
	selectValue : function(year,month,date){

		var next = (year instanceof Date) ? year : new Date(year,month,date);

		this.year = next.getFullYear();
		this.month = next.getMonth();
		this.date = next.getDate();

		month = this.month + 1;

		var value = this.year
		value += "-";
		value += (month < 10) ? "0" + month : month;
		value += "-";
		value += (this.date < 10) ? "0" + this.date : this.date;

		$(this.node).val(value);

		//unset selected
		$("#" + this.id + " .selected").removeClass("selected");

	},

	/**
	 * カレンダーの移動などに使用
	 */
	setValue : function(year,month,date){

		if(!date)date = 1;

		var next = (year instanceof Date) ? year : new Date(year,month,date);

		this.year = next.getFullYear();
		this.month = next.getMonth();
		this.date = next.getDate();

		//カレンダーの再構築
		this.buildCalendar();

		//select, btnの操作
		var ele = this.element;
		$("select.month_select",ele).val(this.month);
		$("select.year_select",ele).val(this.year);

		if(!this.show_future
		 && this.year == this.today.getFullYear()
		 && this.month == this.today.getMonth() ){
			$(".next_btn",ele).hide();
		}else{
			$(".next_btn",ele).show();
		}

		//input
		if($(this.node).val().length > 0)this.selectValue(this.year,this.month,this.date);
	},

	moveMonth : function(flag){
		var to_month = (flag > 0) ? this.month+1 : this.month-1;
		this.setValue(this.year,to_month);
	},

	debug : function(){
		alert(this.year + "-" + (this.month+1) + "-" + this.date);

	}


});

$(function(){

	$("input.date_picker_start,input.date_picker_end").bind("focus",function(e){
		SOY2_DatePicker.show(this);
	});

	$("body").bind("click",function(e){
		if($(e.target).hasClass("date_picker_start") || $(e.target).hasClass("date_picker_end"))return;
		if($(e.target).parents().hasClass("soy2_date_picker"))return;
		SOY2_DatePicker.hideAll();
	});

	$("input.date_picker_start,input.date_picker_end").bind("keyup",function(e){
		SOY2_DatePicker.check(this);
	});


});