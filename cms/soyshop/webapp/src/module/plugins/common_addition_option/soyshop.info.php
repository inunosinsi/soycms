<?php
/*
 */
class CommonAdditionOptionInfo extends SOYShopInfoPageBase{

	function getPage($active = false){
		if($active){
			$html = array();
			$html[] = '<a href="'.SOY2PageController::createLink("Config.Detail?plugin=common_addition_option").'">加算オプションプラグインの設定</a>';
			$html[] = "<p>※このプラグインは加算プランプラグインとの併用はできません。</p>";
			return implode("\r\n", $html);
		}else{
			return "";
		}
	}

}
SOYShopPlugin::extension("soyshop.info", "common_addition_option", "CommonAdditionOptionInfo");
?>
