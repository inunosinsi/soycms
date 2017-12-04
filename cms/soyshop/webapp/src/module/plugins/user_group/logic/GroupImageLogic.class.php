<?php

class GroupImageLogic extends SOY2LogicBase {

	function __construct(){
		SOY2::import("module.plugins.user_group.util.UserGroupCustomSearchFieldUtil");
	}

	function uploadFile($file, $tmp, $groupId){
		$dir = UserGroupCustomSearchFieldUtil::getUploadFileDir($groupId);

		SOYShopPlugin::load("soyshop.upload.image");
		$filename = SOYShopPlugin::invoke("soyshop.upload.image", array(
			"mode" => "profile",
			"pathinfo" => pathinfo($file)
		))->getName();

		if(is_null($filename)){
			$extension = substr($file, strrpos($file, "."));
			$filename = md5(time() . $file) . $extension;
		}
		@move_uploaded_file($tmp, $dir . $filename);
		return $filename;
	}
}
