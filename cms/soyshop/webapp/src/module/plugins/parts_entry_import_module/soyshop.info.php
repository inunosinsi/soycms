<?php
/**
 * プラグイン インストール画面の表示
 */
class EntryImportModuleInfo extends SOYShopInfoPageBase{

	function getPage(bool $active=false){
		if($active){
			return '<a href="'.SOY2PageController::createLink("Config.Detail?plugin=parts_entry_import_module").'">ブログ記事表示設定(モジュール版)</a>';
		}else{
			return "";
		}
	}
}
SOYShopPlugin::extension("soyshop.info","parts_entry_import_module","EntryImportModuleInfo");
