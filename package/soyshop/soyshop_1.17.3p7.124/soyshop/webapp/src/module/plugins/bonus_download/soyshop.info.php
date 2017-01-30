<?php
/**
 * プラグイン インストール画面
 */
class BonusDownloadInfo extends SOYShopInfoPageBase{

	function getPage($active = false){
		if($active){
			return '<a href="'.SOY2PageController::createLink("Config.Detail?plugin=bonus_download").'">設定画面へ</a>';
		}else{
			return "";
		}
	}

}

SOYShopPlugin::extension("soyshop.info", "bonus_download", "BonusDownloadInfo");
?>