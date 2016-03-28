var CMS_FileManager = {
	actions : {
		list : "__AJAX__ROOT_PATH__",
		tree : "__AJAX__TREE_PATH__",
		detail : "__AJAX__DETAIL_PATH__",
		mkdir : "__AJAX__MKDIR_PATH__",
		remove: "__AJAX__DELETE_PATH__",
		search: "__AJAX__SEARCH_PATH__",
		reload: "__AJAX__RELOAD_PATH__"
	},
	
	selected : null,
	selcted_list : null,
	selected_detail : null
};

/**
 * 初期化
 */
function init_filetree(ele){
	$("wrapper").style.height = "448px";
	$("wrapper").style.width = "648px"; 
	PanelManager.init("wrapper",{showTab : false});
	PanelManager.getPanel("west").addTab("tree",$("file_list_wrapper"));
	PanelManager.getPanel("west").show();
	
	PanelManager.getPanel("south").addTab("tree",$("file_detail"));
	PanelManager.getPanel("south").show();
	
	$$("li.file").each(function(file_li){
		file_li.onclick = function(e){
			if(!e)e=event;
			Event.stop(e);
			showList("file:" + file_li.getAttribute("file:id"));
			
			return false;
		};
	});
}

/**
 * ファイルの削除
 */
function deleteFile(id){
	var sUrl = CMS_FileManager.actions.remove;
	var post = "id=" + id;
	
	startLoading();
	
	var myAjax = new Ajax.Request(
		sUrl,
		{	method: 'post', 
			parameters: post, 
			onComplete: function(req){
				if($(req.responseText)){
					showList(req.responseText);
				}else{
					alert("Failed to delete.");
					stopLoading();
				}
			}
		}
	);
}

/**
 * ディレクトリの作成
 */
function mkdir(){
	var sUrl = CMS_FileManager.actions.mkdir;
	var id = $("upload_form_current_dir_id").value;
	
	//TODO 日本語のディレクトリ作成を禁止：：ディレクトリ名が文字化けして中のファイルが表示されないため
	var name =	prompt("Enter new directory name.","default");
	var post = "name=" + name + "&id=" + id;
	
	if(!name)return;
	
	var check = true;
	if(!name.match(/^([0-9a-zA-Z\-_]*)$/))check = false;
	
	if(!check){
		alert("Directory name is wrong.");
		return;
	}
	
	startLoading();
	
	var myAjax = new Ajax.Request(
		sUrl,
		{	method: 'post', 
			parameters: post, 
			onComplete: function(req){
				showList("file:"+id);
			}
		}
	);
}

/**
 * アップロード完了
 */
function uploadFinish(id,result){
	stopLoading();
	if(result){
		//alert("成功しました。");
		showList(id);
	}else{
		alert("Failed to upload.");
	}
}

/**
 * ファイルDB更新
 */
function reload(){
	var sUrl = CMS_FileManager.actions.reload;
	var id = $("upload_form_current_dir_id").value;
	var post = "id=" + id;
	startLoading();
	var myAjax = new Ajax.Request(
		sUrl,
		{	method: 'post', 
			parameters: post, 
			onComplete: function(req){
				showList("file:"+id);
			}
		}
	);
}

/**
 * リストを表示
 */
function showList(id){
	
	var ele = $(id);
	if(!ele)return;
	
	if(ele.getAttribute("file:isdir") != 1)return;
	
	startLoading();
	
	var sUrl = CMS_FileManager.actions.list;
	var fileId = ele.getAttribute("file:id");
	var post = "id=" + fileId;
	
	if(CMS_FileManager.selected_list){
		CMS_FileManager.selected_list.style.backgroundColor = "";
		CMS_FileManager.selected_list.style.color = "black";
	}
		
	$("upload_form_current_dir_id").value = ele.getAttribute("file:id");
	
	var myAjax = new Ajax.Request(
		sUrl,
		{	method: 'post', 
			parameters: post, 
			onComplete: function(req){
				stopLoading();
				$("file_list").update(req.responseText);
				
				showDetail("file:" + fileId);
				if(!req.responseText){
					$("no_file").show();
				}else{
					$("no_file").hide();
				}

			}
		}
	);
	
	sUrl = CMS_FileManager.actions.tree;
	var myAjax2 = new Ajax.Request(
		sUrl,
		{	method: 'post', 
			parameters: post, 
			onComplete: function(req){
				ele.update(req.responseText);
				
				$$("li.file").each(function(li_file){
					li_file.onclick = function(e){
						if(!e)e=event;
						Event.stop(e);
						showList("file:" + li_file.getAttribute("file:id"));
						return false;
					};
				});
				
				CMS_FileManager.selected_list = $(id + "_name");
				CMS_FileManager.selected_list.style.color = "white";
				CMS_FileManager.selected_list.style.backgroundColor = "navy";
			}
		}
	);
	
	return false;
}

/**
 * 詳細を表示
 */
function showDetail(id,flag){
	
	if(flag){
		var ele = $("file:"+id);
		var post = "id=" + id;
	}else{
		var ele = $(id);
		var post = "id=" + ele.getAttribute("file:id");
	}
	
	startLoading();
	
	if(CMS_FileManager.selected_detail){
		CMS_FileManager.selected_detail.style.backgroundColor = "";
		CMS_FileManager.selected_detail.style.color = "black";
	}
	
	var sUrl = CMS_FileManager.actions.detail;	
	
	var myAjax = new Ajax.Request(
		sUrl,
		{	method: 'post', 
			parameters: post, 
			onComplete: function(req){
				stopLoading();
				
				var obj = eval('(' + req.responseText + ')');
				
				CMS_FileManager.selected = obj.id;
				
				if(flag){
					CMS_FileManager.selected_detail = flag;
					CMS_FileManager.selected_detail.style.color = "white";
					CMS_FileManager.selected_detail.style.backgroundColor = "navy";
				}
				
				$("delete_button").onclick = function(){
					deleteFile(obj.id);				
				};
				
				var detail = $("file_detail");
				$("detaildiv_filepath").innerHTML = obj.path;
				$("detaildiv_filepath").src = obj.path;
				$("detaildiv_url").innerHTML = obj.url;
				$("detaildiv_url").href = obj.url;
				
				$("detaildiv_filename").innerHTML = obj.name;
				//$("detaildiv_size").innerHTML = obj.size;
				$("detaildiv_update").innerHTML = obj.update;
				$("detaildiv_create").innerHTML = obj.create;
				
				$("file_detail").show();
				
				$$("#custom_functions div").each(function(ele){
					
					var className = ele.className;
										
					if(className == "all"){
						ele.show();
					}else if(className == obj.type){
						ele.show();
					}else{
						ele.hide();
					}
				});
				
				$("file_detail").show();
				
			}
		}
	);
	
	return false;
}

/* 
 * 関数の追加
 * option = {
 * 		type : "タイプでフィルタリングする場合は指定",
 *		label : "ボタンに表示するラベル"
 *	}
 */
function addCustomFunction(func,option){
	if(!option.type)option.type = "all";
	
	var span = document.createElement("div");
	span.setAttribute("class",option.type);
	span.setAttribute("className",option.type);
	span.style.cssFloat = "left";
	span.style.styleFloat = "left";
	span.innerHTML = '<button type="button">' + option.label + "</button>";
	span.style.display = "none";
	
	$("custom_functions").appendChild(span);
	
	span.firstChild.onclick = function(){
		func(CMS_FileManager.selected,$("detaildiv_url").innerHTML);
	};
}

/**
 * タブの切り替え
 */
function activeTab(ele){
	
	$$("#filetree_tab .active").each(function(ele){
		ele.setAttribute("class","inactive");
		ele.setAttribute("className","inactive");
		$(ele.getAttribute("target")).hide();
	});
	
	ele.setAttribute("class","active");
	ele.setAttribute("className","active");
	$(ele.getAttribute("target")).show();
}

/**
 * 検索
 */
function search(){
	var query = $("search_query").value;
	if(!query)return;
	
	startLoading();
	
	var sUrl = CMS_FileManager.actions.search;
	var post = "q=" + query;
	var myAjax = new Ajax.Request(
		sUrl,
		{	method: 'post', 
			parameters: post, 
			onComplete: function(req){
				stopLoading();
				$("file_list").update(req.responseText);
			}
		}
	);
	
}

function startLoading(){
	$("loading_image").style.visibility = "visible";
}
function stopLoading(){
	setTimeout(function(){
		$("loading_image").style.visibility = "hidden";
	},300);
}

init_filetree($("filetree")); 

function debug(str){
	$("debug").innerHTML = str;
}
