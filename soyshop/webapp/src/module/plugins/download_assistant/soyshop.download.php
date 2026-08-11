<?php

class DownloanAssistantDownload extends SOYShopDownload{

	function execute(){

		if(!isset($_GET["token"])){
			exit;
		}

		$token = $_GET["token"];

		$assistantLogic = SOY2Logic::createInstance("module.plugins.download_assistant.logic.DownloadAssistantLogic");
		$file = $assistantLogic->getFileByToken($token);

		//支払状態が支払確認済みであることを確認
		if($file instanceof SOYShop_Download && is_numeric($file->getReceivedDate())){

			//ダウンロード期限の確認
			$timeLimit = $file->getTimeLimit();
			if(is_null($timeLimit) || (is_numeric($timeLimit) && $timeLimit > time())){

				//残りのダウンロード回数を確認する
				$count = $file->getCount();
				if(is_null($count) || (is_numeric($count) && $count > 0)){
					$assistantLogic->downloadFile($file);
					echo "ダウンロードに成功しました。";
					exit;
				}
			}
		}

		echo "ダウンロードに失敗しました。";
		exit;
	}
}
SOYShopPlugin::extension("soyshop.download", "download_assistant", "DownloanAssistantDownload");
