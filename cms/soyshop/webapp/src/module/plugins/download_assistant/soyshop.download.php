<?php

class DownloanAssistantDownload extends SOYShopDownload{

	function execute(){
		
		if(!isset($_GET["token"])){
			exit;
		}
			
		$token = $_GET["token"];
		
		$assistantLogic = SOY2Logic::createInstance("module.plugins.download_assistant.logic.DownloadAssistantLogic");
		$file = $assistantLogic->getFileByToken($token);
		
		if(!is_null($file)){
			
			//支払状態が支払確認済みであることを確認
			if(!is_null($file->getReceivedDate())){

				//ダウンロード期限の確認
				$timeLimit = $file->getTimeLimit();
				if(is_null($timeLimit) || $timeLimit > time()){
					
					//残りのダウンロード回数を確認する
					$count = $file->getCount();
					if(is_null($count) || $count > 0){
											
						$assistantLogic->downloadFile($file);
						exit;
					}
				}
			}
		}
	
		echo "ダウンロードに失敗しました。";
		exit;
	}
}
SOYShopPlugin::extension("soyshop.download", "download_assistant", "DownloanAssistantDownload");
?>