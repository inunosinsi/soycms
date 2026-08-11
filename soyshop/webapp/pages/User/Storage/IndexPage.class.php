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

	function __construct($args){
		$this->userId = (isset($args[0])) ? (int)$args[0] : null;

		parent::__construct();

		$activedPointPlugin = (class_exists("SOYShopPluginUtil") && (SOYShopPluginUtil::checkIsActive("store_user_folder")));
		DisplayPlugin::toggle("storage", $activedPointPlugin);

		if($activedPointPlugin){
			$files = SOY2Logic::createInstance("module.plugins.store_user_folder.logic.StorageLogic")->getFiles($this->userId);

			SOY2::import("module.plugins.store_user_folder.util.StoreUserFolderUtil");
			$downloadUrl = StoreUserFolderUtil::getDownloadUrl();
		}else{
			$files = array();
			$downloadUrl = "";
		}

		$this->addForm("form", array(
			"enctype" => "multipart/form-data"
		));

		DisplayPlugin::toggle("has_storage", count($files));

		$this->createAdd("storage_list", "_common.User.StorageListComponent", array(
			"list" => $files,
			"downloadUrl" => $downloadUrl
		));
	}
}
