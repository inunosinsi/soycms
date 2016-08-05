var InnertLinkPage = '/main/app/index.php/lpo/Editor/FileUpload';
var InsertImagePage = '/main/app/index.php/lpo/Editor/FileUpload';

$(function(){
	tinyMCE.init({
		// General options
		mode : "textareas",
		theme : "advanced",
		skin : "soycms",
		language : "ja",
	
		//textarea's selector
		editor_selector : "mceEditor",
	
		plugins : "save,pagebreak,table,advhr,advimage,advlink,inlinepopups,insertdatetime,media,searchreplace,contextmenu,paste,directionality,fullscreen,noneditable,visualchars,nonbreaking,xhtmlxtras",
	
		// Theme options
		theme_advanced_buttons1 : "save,|,cut,copy,paste,pastetext,pasteword,|,search,replace,|,bold,italic,underline,strikethrough,|,sub,sup,|,forecolor,backcolor",
		theme_advanced_buttons2 : "bullist,numlist,|,outdent,indent,blockquote,|,undo,redo,|,cleanup,removeformat,code,styleprops,attribs,|,fullscreen,preview,|,insertdate,inserttime",
		theme_advanced_buttons3 : "justifyleft,justifycenter,justifyright,|,formatselect,fontselect,fontsizeselect",
		theme_advanced_buttons4 : "",
		theme_advanced_toolbar_location : "top",
		theme_advanced_toolbar_align : "left",
		theme_advanced_statusbar_location : "bottom",
		theme_advanced_resizing : true,
	
		//カスタム
		cleanup : true,
		verify_html : false,
		convert_urls : false,
		
		relative_urls : false,
		button_tile_map : true,
		entity_encoding : "named"
		/**oninit : "init_soycms_tinymce",
		urlconverter_callback : common_convert_urls**/
	});
});