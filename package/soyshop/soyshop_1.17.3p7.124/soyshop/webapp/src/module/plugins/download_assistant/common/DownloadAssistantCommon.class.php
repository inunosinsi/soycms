<?php

class DownloadAssistantCommon{

	/**
	 * 共通の設定
	 */
	public static function getConfig(){
    	return SOYShop_DataSets::get("download_assistant.config", array(
			"timeLimit" => null,
			"count" => null,
			"allow" => 1,
			"mail" => "※ダウンロード\nダウンロードは下記のURLから行えます。\n##DOWNLOAD_URL##\n\n\n"
		));
    }	
}
?>