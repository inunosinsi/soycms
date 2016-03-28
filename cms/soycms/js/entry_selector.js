var Entry = function(){
	this.initialize(arguments[0],arguments[1]);
}
Entry.currentLabel = null;
Entry.allLength = 0;
Entry.selectedLength = 0;

Entry.Render = function(){
	$("#all_entry_list").empty();
	Entry.allLength = 0;

	$.each(Entries, function(id, entry){
		if(!Entry.currentLabel || entry.label.indexOf(Entry.currentLabel) != -1){
			entry.buildTable($("#all_entry_list"));
		}
	});
	
	Entry.RenderTitle();

}

Entry.RenderTitle = function(){
	$("#all_entry_title").html(soycms.lang.entry_selector.entry_list +" ("+Entry.allLength+soycms.lang.entry_selector.items+")");
	$("#selected_entry_title").html(soycms.lang.entry_selector.selected_entry+"( "+Entry.selectedLength+soycms.lang.entry_selector.items+")");
}


Entry.prototype = {

	id : null,
	title : null,
	label : new Array(),
	selected : false,
	order : null,
	
	initialize : function(obj){
		this.id = obj.id;
		this.title = obj.id+"\t:\t"+obj.title;
		this.label = obj.label;
	},
	
	buildTable : function(target){
		
		var option = $("<option/>");
		option.val(this.id);
		
		option.html(this.title);
		
		option.attr("id", "entry_"+this.id);
	
		option.attr("className", "free");
		option.attr("class", "free");
		
		$(target).append(option);
		
		Entry.allLength++;
	},
	
	buildSelectedTable : function(target){
		var option = $("<option/>");
		option.val(this.id);
		
		option.html(this.title);
		option.attr("className", "selected");
		option.attr("class", "selected");
		
		$(target).append(option);
		
		Entry.selectedLength ++;
	}
}

var Entries = {};

$.event.add(window,"load",function(){
	$.each(entryList, function(index, obj){
		var entry = new Entry(obj);
		Entries[obj.id] = entry;
	});

	$.each(initEntries, function(index, obj){
		if(Entries[obj.id]){
			Entries[obj.id].buildSelectedTable($("#selected_entry_list"));
		}
	});
	
	//$("#outlineFrame").attr("src", outlineLink);
	
	$("#all_entry_list").change(function(){
		$("#up_button").attr("disabled", true);
		$("#down_button").attr("disabled", true);
		$("#move_button").val(soycms.lang.entry_selector.entry_add);
		$("#selected_entry_list").prop("selectedIndex", -1);
	});
	
	$("#selected_entry_list").change(function(){
		$("#up_button").attr("disabled", false);
		$("#down_button").attr("disabled", false);
		$("#move_button").val(soycms.lang.entry_selector.entry_delete);
		$("#all_entry_list").prop("selectedIndex", -1);
	});
	
	//For use in block setting
	if($("#update")){
		$("#update").click(function(){
			var ids = new Array();
		
			$(".selected").each(function(index, ele){
				ids.push($(ele).val());
			});
			
			var $form = $("form[name='update_form']");
			$form.attr("method", "POST");
			$form.css("display", "none");

			$.each(ids, function(index, value){
				var input = $("<input/>");
				input.attr("type", "hidden");
				input.val(value);
				input.attr("name", "object[entryId][]");
				$form.append(input);
			});
			$("body").append($form);
			$form.submit();
		});
	}
	
	if($("#move_entry_form")){
		$("#move_entry_form").click(function(){
			window.location.href = entry_form_address;
		});
	}
	
	$("#move_button").click(function(){
		if($("#selected_entry_list").val()){
			var $selected = $("#selected_entry_list").children(":selected");
			var $sibling = $selected.prev().length ? $selected.prev() : $selected.next();
			var tempVal = $sibling.val();
			$sibling.val(-1);
			$("#selected_entry_list").val(-1);
			$sibling.val(tempVal);
			$selected.remove();
			Entry.selectedLength--;
		}else if($("#all_entry_list").val()){
			//Select from all entries
			var dom_id = "entry_" + $("#all_entry_list").val();
			
			var clone = $("#" + dom_id).clone(true);
			clone.attr("id", null);
			clone.attr("className", "selected");
			clone.attr("class", "selected");
			
			$("#selected_entry_list").append(clone);
			Entry.selectedLength++;
		}else{
			//do nothing
		}
		
		Entry.RenderTitle();
	});
	$("#up_button").click(function(){
		$selected = $("#selected_entry_list").children(":selected");
		$selected.prev().before($selected);
	});
	
	$("#down_button").click(function(){
		$selected = $("#selected_entry_list").children(":selected");
		$selected.next().after($selected);
	});
	
	$("#display_outline").click(function(){
		var selectedEntryId = null;
		if($("#selected_entry_list").val()){
			selectedEntryId = $("#selected_entry_list").val();
		}
		if($("#all_entry_list").val()){
			selectedEntryId = $("#all_entry_list").val();
		}
		
		if(selectedEntryId){
			$("#outlineFrame").attr("src", outlineLink+"/"+selectedEntryId);
		}
	});
	
	Entry.Render();
});

function labelFilter(value){
	
	if(value == "##NO_FILTER##"){
		Entry.currentLabel = null;
	}else{
		Entry.currentLabel = value;
	}
	Entry.Render();
}
