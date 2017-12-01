<?php

class GroupImageLogic extends SOY2LogicBase {

	function __construct(){
		SOY2::import("module.plugins.user_group.util.UserGroupCustomSearchFieldUtil");
	}

	function uploadFile($file, $tmp, $groupId){
		$dir = UserGroupCustomSearchFieldUtil::getUploadFileDir($groupId);
		@move_uploaded_file($tmp, $dir . $file);
	}
}
