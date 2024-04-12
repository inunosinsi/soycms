<?php
/*
 */
class BulkPageRemoveInfo extends SOYShopInfoPageBase{

	function getPage(bool $active=true){
		if($active){
			return '<a href="' . SOY2PageController::createLink("Config.Detail?plugin=bulk_page_remove_plugin") . '">ページとテンプレートの一括削除の操作</a>';
		}else{
			return "";
		}
	}
}
SOYShopPlugin::extension("soyshop.info", "bulk_page_remove_plugin", "BulkPageRemoveInfo");
