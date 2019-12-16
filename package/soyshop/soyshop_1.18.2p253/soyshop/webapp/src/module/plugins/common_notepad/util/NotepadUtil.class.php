<?php

class NotepadUtil {
	public static function getDirectory($loginId){
		$dir = SOYSHOP_SITE_DIRECTORY . ".notepad/";
		if(!file_exists($dir)){
			mkdir($dir);
			file_put_contents($dir . ".htaccess", "deny from all");
		}
		return $dir . $loginId . "/";
	}

	public static function checkBackupFile($loginId){
		$dir = SOYSHOP_SITE_DIRECTORY . ".notepad/" . $loginId . "/";
		return (file_exists($dir . "title.backup") || file_exists($dir . "content.backup"));
	}

	public static function deleteBackup($loginId){
		$dir = SOYSHOP_SITE_DIRECTORY . ".notepad/" . $loginId . "/";
		if(file_exists($dir . "title.backup")) unlink($dir . "title.backup");
		if(file_exists($dir . "content.backup")) unlink($dir . "content.backup");
	}

	public static function getLabel(SOYShop_Notepad $notepad){
		if(!is_null($notepad->getItemId())){
			return soyshop_get_item_object($notepad->getItemId())->getName();
		}else if(!is_null($notepad->getCategoryId())){
			return soyshop_get_category_object($notepad->getCategoryId())->getName();
		}else if(!is_null($notepad->getUserId())){
			return soyshop_get_user_object($notepad->getUserId())->getName();
		}

		return null;
	}

	public static function buildBackLink(SOYShop_Notepad $notepad, $pluginId=null){
		if(isset($pluginId)){
			if(!is_null($notepad->getItemId())){
				$id = $notepad->getItemId();
			}else if(!is_null($notepad->getCategoryId())){
				$id = $notepad->getCategoryId();
			}else if(!is_null($notepad->getUserId())){
				$id = $notepad->getUserId();
			}
			return SOY2PageController::createLink("Extension.Detail." . htmlspecialchars($pluginId, ENT_QUOTES, "UTF-8") . "." . $id);
		}else{
			if(!is_null($notepad->getItemId())){
				return SOY2PageController::createLink("Item.Detail." . $notepad->getItemId());
			}else if(!is_null($notepad->getCategoryId())){
				return SOY2PageController::createLink("Item.Category.Detail." . $notepad->getCategoryId());
			}else if(!is_null($notepad->getUserId())){
				return SOY2PageController::createLink("User.Detail." . $notepad->getUserId());
			}
		}
	}
}
