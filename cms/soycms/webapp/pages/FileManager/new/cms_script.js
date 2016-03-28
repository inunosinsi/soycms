var file = _AJAX_FILE_TREE_;

var CMS_FileManager = {
	
	selected : null,
	
	actions : {
		list : "__AJAX__ROOT_PATH__",
		mkdir : "__AJAX__MKDIR_PATH__",
		remove: "__AJAX__DELETE_PATH__",
		addcss: "__AJAX__ADDCSS_PATH__",
		thumbnail : "__AJAX__THUMBNAIL_PATH__"
	},
	
	mkdir : function(){
		var sUrl = CMS_FileManager.actions.mkdir;
		var selected = $(CMS_FileManager.selected).getAttribute("file:relative_path");
		
		if(!selected.match(/\/$/)){
			selected += "/";
		}
		
		//TODO 日本語のディレクトリ作成を禁止：：ディレクトリ名が文字化けして中のファイルが表示されないため
		var path =	prompt("新規作成するディレクトリ名を入力してください","default");
		var post = "path=" + selected + path;
		
		if(!path)return;
		
		var myAjax = new Ajax.Request(
			sUrl,
			{	method: 'post', 
				parameters: post, 
				onComplete: function(req){
					location.reload();
				}
			}
		);
	},
	
	insertImage : function(){
		window.parent.document.onclick = function(e){
			if(!e)e = event;
			
			var target = (e.target) ? e.target : e.srcElement;
			
			alert(target.tagName);		
		};
		
		window.parent.document.getElementById("click_to_layer_frame").style.display = "none";
	},
	
	uploadFinish : function(result){
		if(result){
			common_to_speak_soy_boy("成功しました。");
		}else{
			common_to_speak_soy_boy("アップロードに失敗しました。");
		}
	}
};

function init_filetree(ele){
		
	var nodes = ele.childNodes;
	
	for(var i=0;i<nodes.length;i++){
		
		var node = nodes[i];
		
		if(nodes[i].tagName && nodes[i].tagName.match(/li|ul/i)){
			nodes[i].ondrop = function(targetId,asSibling){
			};
			if(nodes[i].tagName.match(/li/i)){
				nodes[i].onclick = function(e){
					if(!e)e=event;
					CMS_FileManager.selected = this.getAttribute("id");
					e.cancelBubble = true;
					return showDetail(this.getAttribute("id"));
				}
			}
			
			init_filetree(nodes[i]);
			
			continue;
		}
	}
}

function showDetail(id){
	var ele = $(id);
	if(!ele)return;
	
	var tree = $("filetree");
	//tree.style.height = "250px";
	
	var detail = $("file_detail");
	detail.style.display = "";
	
	$("detaildiv_filepath").innerHTML = ele.getAttribute("file:path");
	$("detaildiv_filepath").src = ele.getAttribute("file:path");
	
	$("detaildiv_filename").value = ele.getAttribute("file:name");
	$("detaildiv_size").innerHTML = ele.getAttribute("file:size");
	$("detaildiv_update").innerHTML = ele.getAttribute("file:update");
	$("detaildiv_owner").innerHTML = ele.getAttribute("file:owner");
	
	if(ele.getAttribute("file:owner").length < 1){
		$("detaildiv_owner_row").hide();
	}
	
	$("detaildiv_permission").innerHTML = ele.getAttribute("file:permission");
	
	
	$("upload_form_current_path").value = ele.getAttribute("file:path");
	
	if(ele.getAttribute("file:type") == "directory"){
		$("detaildiv_dir").style.display = "";
	}else{
		$("detaildiv_dir").style.display = "none";
	}
	
	if(ele.getAttribute("file:type") == "image"){
		$("detaildiv_thumbnail").style.display = "";
		$("detaildiv_thumbnail").style.backgroundImage = "url("+ CMS_FileManager.actions.thumbnail + "?path="+ ele.getAttribute("file:path") +")";
		$("detaildiv_thumbnail").style.cursor = "pointer";
		
		$("detaildiv_thumbnail").src = "/__AJAX__SITE_ID__" + ele.getAttribute("file:path");

	}else{
		$("detaildiv_thumbnail").style.display = "none";
	}
	
	return false;
}

function closeDetail(){
	var tree = $("filetree");
	tree.style.height = "500px";
	
	var detail = $("file_detail");
	detail.style.display = "none";
}

function getJSDragDropTreeObject(id){
	
	var treeObj = new JSDragDropTree();
	treeObj.imageFolder = _AJAX_FILEMANAGER_IMAGE_ROOT_;
	treeObj.filePathRenameItem = '__AJAX__RENAME_PATH__';	//リネーム先の変更が可能
	treeObj.filePathDeleteItem = '__AJAX__DELETE_PATH__';	//削除先の変更が可能
	treeObj.setTreeId(id);
	treeObj.setMaximumDepth(7);
	treeObj.setMessageMaximumDepthReached('Maximum depth reached'); // If you want to show a message when maximum depth is reached, i.e. on drop.
	
	return treeObj;
}

function clearChildDragDropTree(id){
	var ele = $(id);
	if(!ele)return;
	
	
}

//main start
init_filetree($("filetree")); 

var treeObj = getJSDragDropTreeObject("filetree");
treeObj.initTree();
treeObj.expandAll();

CMS_FileManager.selected = "/";
showDetail("/");

function debug(str){
	$("debug").innerHTML = str;
}
