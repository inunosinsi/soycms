$.event.add(window,"load",function(){
	
	$('#entry_content_wrapper').css("position","relative");
	$('#entry_more_wrapper').css("position","absolute");
	$('#entry_content_wrapper').css("visibility","visible");
	
	$('#entry_content_switch').click(function(){
		$('#entry_content_wrapper').css("position","relative");
		$('#entry_content_wrapper').css("visibility","visible");
		
		$('#entry_more_wrapper').css("position","absolute");
		$('#entry_more_wrapper').css("visibility","hidden");
		
		$('#entry_content_switch').attr("class","content_tab_active");
		//これ意味あるの？
//		$('entry_content_switch').className = "content_tab_active";
		
		$('#entry_more_switch').attr("class","content_tab_inactive");
//		$('entry_more_switch').className = "content_tab_inactive";
		
	});


	$('#entry_more_switch').click(function(){
		$('#entry_more_wrapper').css("position","relative");
				
		$('#entry_content_wrapper').css("position","absolute");
		$('#entry_content_wrapper').css("visibility","hidden");
		
		$('#entry_more_switch').attr("class","content_tab_active");
//		$('entry_more_switch').className = "content_tab_active";
		
		$('#entry_content_switch').attr("class","content_tab_inactive");
//		$('entry_content_switch').className = "content_tab_inactive";
		
		//safari rendering is too slow.
		$('#entry_more_wrapper').css("visibility","visible");
	});
	
	//toggle label
	//toggle label
	var obj = $('#labels input[type="checkbox"]');
	$.each(obj,function(){
		toggle_labelmemo(this.value,this.checked);
	});
	
},false);


function applyTemplate(){
	var template = $("#list_templates").val();
	
	if(template.length == 0){
		return;
	}
	
	var post = "id=" + template;
	
	var callback = function(oResponse) {
			var result = eval('('+oResponse.responseText+')');
			
			$("#style").val(result['templates']['style']);
			
			if(result['templates']['content'].length > 0){
				$('#entry_content').val(result['templates']['content']);
			}
			
			if(result['templates']['more'].length){
				$('#entry_more').val(result['templates']['more']);
			}
	};
	
	$.ajax({
		url: templateAjaxURL,
		data: post,
		type: 'post',
		complete: callback
	});
}
