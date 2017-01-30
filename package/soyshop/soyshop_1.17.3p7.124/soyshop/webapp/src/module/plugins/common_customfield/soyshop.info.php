<?php
/*
 */
class CommonItemCustomFieldInfo extends SOYShopInfoPageBase{

	function getPage($active = false){
		if($active){
			return '<a href="'.SOY2PageController::createLink("Item.CustomField").'">カスタムフィールドの追加</a>';
		}else{
			return "";
		}
	}

}
SOYShopPlugin::extension("soyshop.info","common_customfield","CommonItemCustomFieldInfo");
?>
