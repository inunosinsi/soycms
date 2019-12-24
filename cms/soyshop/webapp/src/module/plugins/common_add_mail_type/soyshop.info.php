<?php
/*
 */
class CommonAddMailTypeInfo extends SOYShopInfoPageBase{

	function getPage($active = false){
		if($active){
			$html = array();
			$html[] = '<a href="'.SOY2PageController::createLink("Config.Detail?plugin=common_add_mail_type").'">メール送信種類追加プラグインの設定</a>';
			return implode("\r\n", $html);
		}else{
			return "";
		}
	}

}
SOYShopPlugin::extension("soyshop.info", "common_add_mail_type", "CommonAddMailTypeInfo");
