<?php
/*
 */
class InquiryOnMypageInfo extends SOYShopInfoPageBase{

	function getPage(bool $active=false){

		if($active){
			return '<a href="' . SOY2PageController::createLink("Config.Detail?plugin=inquiry_on_mypage") . '">マイページ用お問い合わせフォームの設定</a>';
		}else{
			return "";
		}
	}
}
SOYShopPlugin::extension("soyshop.info", "inquiry_on_mypage", "InquiryOnMypageInfo");
