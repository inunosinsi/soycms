<?php
/*
 */
class CommonRelativeItemInfo extends SOYShopInfoPageBase{

	function getPage($active = true){
		if($active){
			$html = array();
			$html[] = '<a href="'.SOY2PageController::createLink("Config.Detail?plugin=common_relative_item").'">テンプレートへの記述例</a>';
			$html[] = "※商品数が10000件を超えると管理画面の商品詳細画面が表示されなくなることがあります。";
			return implode("<br>", $html);
		}else{
			return "";
		}
	}

}
SOYShopPlugin::extension("soyshop.info","common_relative_item","CommonRelativeItemInfo");
?>
