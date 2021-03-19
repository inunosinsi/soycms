//tinymce4
tinymce.init({
	mode : "specific_textareas",
	editor_selector : "mceEditor",
	theme : "modern",
	skin : "lightgray",
	plugins : "save,pagebreak,table,hr,insertdatetime,searchreplace,contextmenu,code,textcolor,paste,directionality,noneditable,charmap,visualchars,nonbreaking,innerlink,insertimage,insertwidget,media,emoticons",
	tools : "inserttable",
	language : soycms.language,
	height : "400px",
	resize: "both",
	mobile: { theme: 'mobile' },

	menubar : false,
	toolbar1 : "save | cut copy paste pastetext | searchreplace | bold italic underline strikethrough | subscript superscript | forecolor backcolor | alignleft aligncenter alignright | formatselect fontselect fontsizeselect",
	toolbar2 : "bullist numlist | outdent indent blockquote | undo redo | cleanup removeformat code styleprops attribs | preview | insertdate inserttime | innerlink insertimage media insertwidget | emoticons charmap | table",

	init_instance_callback : function(editor) {
		onInitTinymceEditor(editor.id);
	},
	oninit : function(){
		onInitTinymce();
	},

	cleanup : true,
	verify_html : false,
	convert_urls : false,
	relative_urls : false,
	entity_encoding : "named",
	urlconverter_callback : common_convert_urls
});

//tinymce
// tinymce.init({
// 	selector:'textarea',
// 	height: 400,
// 	plugins: [
//     	'advlist autolink lists link image charmap print preview anchor',
//     	'searchreplace visualblocks code fullscreen',
//     	'insertdatetime media table paste code help wordcount'
//   ],
// });

function applyTemplate(){
	var template = $("#list_templates").val();

	if(template.length == 0){
		return;
	}

	var post = "id=" + template;

	var callback = function(oResponse) {
		var result = eval('('+oResponse.responseText+')');

		$("#style").val(result['templates']['style']);

		if(result["templates"]["content"].length > 0){
			tinymce.get("entry_content").setContent(result["templates"]["content"]);
		}

		if(result["templates"]["more"].length > 0){
			tinymce.get("entry_more").setContent(result["templates"]["more"]);
		}

		if(result['style_path'].length > 0){
			tinymce.get('entry_content').dom.loadCSS(result['style_path']);
			tinymce.get('entry_more').dom.loadCSS(result['style_path']);
		}
	};

	$.ajax({
		url: templateAjaxURL,
		data: post,
		type: 'post',
		complete: callback
	});
}

// tinymceのすべてのエディタがinitされたときに一度だけ呼ばれる
function onInitTinymce(){
	$("#entry_content_wrapper").css({
		"position" : "relative",
		"visibility" : "visible"
	});

	$("#entry_more_wrapper").css({
		"position" : "absolute",
		"visibility" : "hidden",
		"display" : "none"
	});

	$("#entry_content_switch").click(function(){
		$("#entry_content_switch").attr("class", "content_tab_active");
		$("#entry_more_switch").attr("class", "content_tab_inactive");

		$("#entry_content_wrapper").css({
			"position" : "relative",
			"visibility" : "visible",
			"display" : ""
		});

		$("#entry_more_wrapper").css({
			"position" : "absolute",
			"visibility" : "hidden",
			"display" : "none"
		});

		tinymce.get('entry_content').focus();
	});


	$("#entry_more_switch").click(function(){
		$("#entry_content_switch").attr("class","content_tab_inactive");
		$("#entry_more_switch").attr("class","content_tab_active");

		$("#entry_content_wrapper").css({
			"position" : "absolute",
			"visibility" : "hidden",
			"display" : "none"
		});

		$("#entry_more_wrapper").css({
			"position" : "relative",
			"visibility" : "visible",
			"display" : ""
		});

		tinymce.get('entry_more').focus();
	});


	//本文にフォーカス
	tinyMCE.get('entry_content').focus();

	//toggle label
	var obj = $('#labels input[type="checkbox"]');
	$.each(obj,function(){
		toggle_labelmemo(this.value,this.checked);
	});
}

// tinymceのエディタがinitされる毎に呼ばれる
function onInitTinymceEditor(id){
	if(entry_css_path == undefined) var entry_css_path = null;
	if(entry_css_path){
		$.ajax({
			type: "GET",
			url: entry_css_path,
			success: function(res){
				//スタイルの適用
				tinyMCE.get(id).dom.loadCSS(entry_css_path);

				//デフォルトの本文
				if(tinyMCE.get(id).getContent().length < 1){
					tinyMCE.get(id).setContent("<p></p>");
				}
			},
			error: function(res){
				//何もしない
			}
		});
	}
}

function common_convert_urls(url, node, on_save) {

	if(url[0] == "/")return url;

	if(url[0] == "."){
		var img = new Image;
		img.src = url;
		url = img.src;

		return url;
	}

	return url;
}
