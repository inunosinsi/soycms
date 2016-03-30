<?php

class IndexPage extends WebPage{
	
	private $userId;
	
	function doPost(){
		
		if(soy2_check_token() && isset($_FILES["storage"])){
			$storageLogic = SOY2Logic::createInstance("module.plugins.store_user_folder.logic.StorageLogic");
			$storageLogic->upload($_FILES["storage"], $this->userId);
			SOY2PageController::jump("User.Storage." . $this->userId . "?updated");
		}
	}
	
	function IndexPage($args){
		$this->userId = (isset($args[0])) ? (int)$args[0] : null;
		
		WebPage::WebPage();
		
		$this->addModel("updated", array(
			"visible" => (isset($_GET["updated"]))
		));
				
		$activedPointPlugin = (class_exists("SOYShopPluginUtil") && (SOYShopPluginUtil::checkIsActive("store_user_folder")));
		$this->addModel("is_storage", array(
			"visible" => $activedPointPlugin
		));
		
		if($activedPointPlugin){
			$storageLogic = SOY2Logic::createInstance("module.plugins.store_user_folder.logic.StorageLogic");
			$files = $storageLogic->getFiles($this->userId);
			
			SOY2::import("module.plugins.store_user_folder.util.StoreUserFolderUtil");
			$downloadUrl = StoreUserFolderUtil::getDownloadUrl();
		}else{
			$files = array();
			$downloadUrl = "";
		}
		
		$this->addForm("form", array(
			"enctype" => "multipart/form-data"
		));
		
		$this->addModel("has_storage", array(
			"visible" => (count($files))
		));
		
		
		$this->createAdd("storage_list", "_common.User.StorageListComponent", array(
			"list" => $files,
			"downloadUrl" => $downloadUrl
		));
	}
}
?>