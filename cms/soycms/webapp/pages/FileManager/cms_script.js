var CMS_FileManager = {
	actions : {
		getFileId : "__AJAX__GET_FILE_ID_PATH__",
		upload: "__AJAX__UPLOAD_PATH__",
		remove: "__AJAX__REMOVE_PATH__",
		reload: "__AJAX__RELOAD_PATH__"
	},
	
	soy2_token : "__AJAX_SOY2_TOKEN__",
	directory_id : "__DIRECTORY_ID__",
	filesInDir : {},
};

/**
 * ファイルとディレクトリのIDを取得
 */
function getFileId(target, files){
	var sUrl = CMS_FileManager.actions.getFileId;
	var fileAttrs = {};
	$.each(files, function(index, file){
		fileAttrs[file.hash] = {
			name: file.name,
		};
		file.fileId = "hogera";
	});
		
	var params = {
		fileAttrs: fileAttrs,
		target: target
	};

	ajaxPost(sUrl, params, function(json){
		CMS_FileManager.directory_id = json["dirId"];
		CMS_FileManager.filesInDir = json["files"];
	});
}

/**
 * ディレクトリの作成
 */
function mkfile(added){
	var sUrl = CMS_FileManager.actions.upload;
	var params = makeUploadParams(added);

	ajaxPost(sUrl, params, function(json){
		CMS_FileManager.soy2_token = json["soy2_token"];
		addFile(added);
	});
}

/**
 * ディレクトリの作成
 */
function mkdir(added){
	var sUrl = CMS_FileManager.actions.upload;
	var params = makeUploadParams(added);

	ajaxPost(sUrl, params, function(json){
		CMS_FileManager.soy2_token = json["soy2_token"];
		addFile(added);
	});
		
	//TODO 日本語のディレクトリ作成を禁止：：ディレクトリ名が文字化けして中のファイルが表示されないため
	//var name =	prompt("新規作成するディレクトリ名を入力してください","default");
	
	//if(!name)return;
	
	//var check = true;
	//if(!name.match(/^([0-9a-zA-Z\-_]*)$/))check = false;
	
	//if(!check){
	//alert("ディレクトリ名が不正です。");
	//return;
	//}
}

/**
 * ファイルの追加
 */
function upload(added){
	var sUrl = CMS_FileManager.actions.upload;
	var params = makeUploadParams(added);

	ajaxPost(sUrl, params, function(json){
		CMS_FileManager.soy2_token = json["soy2_token"];
		addFile(added);
	});
}

/**
 * ファイルの削除
 */
function rm(removed){
	var sUrl = CMS_FileManager.actions.remove;
	var params = makeRmParams(removed);

	ajaxPost(sUrl, params, function(json){
		CMS_FileManager.soy2_token = json["soy2_token"];
		removeFile(removed);
	});
}

/**
 * ファイル追加用のパラメータを生成
 */
function makeUploadParams(added){
	var nameArray = $.map(added, function(file){
		return file.name;
	});

	var params = {
		name: nameArray,
		id: CMS_FileManager.directory_id,
		soy2_token: CMS_FileManager.soy2_token
	};

	return params;
}

/**
 * ファイル削除用のパラメータを生成
 */
function makeRmParams(removed){
	var idArray = $.map(removed, function(hash){
		if(CMS_FileManager.filesInDir[hash]){
			return CMS_FileManager.filesInDir[hash]["id"];
		}
	});
		
	var params = {
		ids: idArray,
		soy2_token: CMS_FileManager.soy2_token
	};

	return params;
}

function addFile(added){
	var sUrl = CMS_FileManager.actions.getFileId;
	var fileAttrs = {};
	$.each(added, function(index, file){
		fileAttrs[file.hash] = {
			name: file.name,
		};
	});
		
	var params = {
		fileAttrs: fileAttrs,
		target: CMS_FileManager.directory_id
	};

	ajaxPost(sUrl, params, function(json){
		CMS_FileManager.directory_id = json["dirId"];
		$.each(json["files"], function(hash, file){
			CMS_FileManager.filesInDir[hash] = file;
		});
	});
}

function removeFile(removed){
	$.each(removed, function(index, hash){
		delete CMS_FileManager.filesInDir[hash];
	});
}

/**
 * ファイルDB更新
 */
function reload(){
	var sUrl = CMS_FileManager.actions.reload;
		
	var params = {
		id: CMS_FileManager.directory_id
	};

	ajaxPost(sUrl, params, function(json){
		CMS_FileManager.soy2_token = json["soy2_token"];
	});
}

/**
 * 名前変更
 */
function rename(removed, added){
	var sUrl = CMS_FileManager.actions.remove;
	var params = makeRmParams(removed);

	ajaxPost(sUrl, params, function(json){
		CMS_FileManager.soy2_token = json["soy2_token"];
		removeFile(removed);
		var sUrl = CMS_FileManager.actions.upload;
		var params = makeUploadParams(added);
		ajaxPost(sUrl, params, function(json){
			CMS_FileManager.soy2_token = json["soy2_token"];
			addFile(added);
		});
	});
}

function ajaxPost(url, params, successCallback){
	$.ajax({
		type: "POST",
		url: url,
		data: params,
		dataType: "json",
		success: successCallback
	});
}