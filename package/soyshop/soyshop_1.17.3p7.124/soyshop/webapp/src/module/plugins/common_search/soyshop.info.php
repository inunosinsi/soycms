<?php
/*
 */
class SOYShopCommonSearchInfo extends SOYShopInfoPageBase{

	function getPage($active = false){

		if($active){
			return '<a href="'.SOY2PageController::createLink("Config.Detail?plugin=common_search").'">検索フォームの設置方法</a>';
		}else{
			return "";
		}
	}

}
SOYShopPlugin::extension("soyshop.info","common_search","SOYShopCommonSearchInfo");
?>