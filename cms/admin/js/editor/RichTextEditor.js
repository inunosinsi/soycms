//Event.observe(window,'load',function(){
//	
//	generate_wysiwyg('entry_content',function(){
//			changeCSSStyle('entry_content','current_style');		
//	});
//	
//	generate_wysiwyg('entry_more',function(){
//			if(!document.getElementById("entry_more").value.length){
//				document.getElementById("entry_more_wrapper").style.display = "none";
//				document.getElementById("entry_more_toggle").style.display = "";
//			}
//			changeCSSStyle('entry_more','current_style');
//	});
//		
//},true);
//
tinyMCE.init({
		// General options
		mode : "textareas",
		theme : "advanced",
		plugins : "safari,pagebreak,style,layer,table,save,advhr,advimage,advlink,emotions,iespell,inlinepopups,insertdatetime,preview,media,searchreplace,print,contextmenu,paste,directionality,fullscreen,noneditable,visualchars,nonbreaking,xhtmlxtras,template,innerlink,insertimage",

		// Theme options
		theme_advanced_buttons1 : "save,newdocument,|,bold,italic,underline,strikethrough,|,justifyleft,justifycenter,justifyright,justifyfull,|,styleselect,formatselect,fontselect,fontsizeselect",
		//theme_advanced_buttons2 : "cut,copy,paste,pastetext,pasteword,|,search,replace,|,bullist,numlist,|,outdent,indent,blockquote,|,undo,redo,|anchor,image,cleanup,help,code,|,insertdate,inserttime,preview,|,forecolor,backcolor",
		theme_advanced_buttons2 : "cut,copy,paste,pastetext,pasteword,|,bullist,numlist,|,outdent,indent,blockquote,|,undo,redo,|,cleanup,help,code,|,insertdate,inserttime,preview,|,forecolor,backcolor",
		//theme_advanced_buttons3 : "tablecontrols,|,hr,removeformat,visualaid,|,sub,sup,|,charmap,emotions,iespell,media,advhr,|,print,|,ltr,rtl,|,fullscreen",
		theme_advanced_buttons3 : "tablecontrols,|,hr,removeformat,visualaid,|,sub,sup,|,charmap,emotions,|,fullscreen,|,innerlink,insertimage",
		//theme_advanced_buttons4 : "insertlayer,moveforward,movebackward,absolute,|,styleprops,|,cite,abbr,acronym,del,ins,attribs,|,visualchars,nonbreaking,template,pagebreak",
		theme_advanced_toolbar_location : "top",
		theme_advanced_toolbar_align : "left",
		theme_advanced_statusbar_location : "bottom",
		theme_advanced_resizing : true,

		// Example content CSS (should be your site CSS)
		content_css : "css/content.css",

		// Drop lists for link/image/media/template dialogs
		template_external_list_url : "lists/template_list.js",
		external_link_list_url : "lists/link_list.js",
		external_image_list_url : "lists/image_list.js",
		media_external_list_url : "lists/media_list.js",

		// Replace values for the template plugin
		template_replace_values : {
			username : "Some User",
			staffid : "991234"
		},
		
		//textarea's selector
		editor_selector : "mceEditor",
		
		//カスタム
		cleanup : false,
		verify_html : false,
		button_tile_map : true,
		entity_encoding : "raw"
	});
	


function applyTemplate(){
	var template = document.getElementById("list_templates").value;
	
	if(template.length == 0){
		return;
	}
	
	var post = "file=" + template;
	
	var callback = function(oResponse) {
			var result = eval('('+oResponse.responseText+')');
			
			$("style").value = result['templates']['style'];
						
			tinyMCE.get('entry_content').getWin().document.body.innerHTML = result['templates']['content'];
			tinyMCE.get('entry_more').getWin().document.body.innerHTML = result['templates']['more'];


			tinyMCE.get('entry_content').dom.loadCSS(result['style_path']);
			tinyMCE.get('entry_more').dom.loadCSS(result['style_path']);
	};
	
	var myAjax = new Ajax.Request(
		templateAjaxURL, 
		{
			method: 'post', 
			parameters: post, 
			onComplete: callback
		});
}