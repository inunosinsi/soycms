<?php

class StoreUserFolderDownload extends SOYShopDownload{

	function execute(){

		if(!isset($_GET["token"])){
			exit;
		}

		$storageLogic = SOY2Logic::createInstance("module.plugins.store_user_folder.logic.StorageLogic");
		$file = $storageLogic->getFileByToken(trim($_GET["token"]));

		if(!is_null($file)){
			$storageLogic->downloadFile($file);
			exit;
		}

		echo "ダウンロードに失敗しました。";
		exit;

	}
}
SOYShopPlugin::extension("soyshop.download", "store_user_folder", "StoreUserFolderDownload");
