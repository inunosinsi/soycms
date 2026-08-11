<?php
/*
 */
class ConvertImageFileFormatInfo extends SOYShopInfoPageBase{

	function getPage(bool $active=false){
		if($active){
			return '<a href="'.SOY2PageController::createLink("Config.Detail?plugin=convert_image_file_format").'">画像フォーマット変換プラグインの設定</a>';
		}else{
			return "";
		}
	}
}
SOYShopPlugin::extension("soyshop.info", "convert_image_file_format", "ConvertImageFileFormatInfo");
