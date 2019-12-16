<?php
/**
 * プラグイン インストール画面の表示
 */
class EntryImportModuleInfo extends SOYShopInfoPageBase{

	function getPage($active = false){
		if($active){
			return '<a href="'.SOY2PageController::createLink("Config.Detail?plugin=parts_entry_import").'">ブログ記事表示設定</a>';
		}else{
			return "";
		}
	}

}
SOYShopPlugin::extension("soyshop.info","parts_entry_import","EntryImportModuleInfo");
?>
