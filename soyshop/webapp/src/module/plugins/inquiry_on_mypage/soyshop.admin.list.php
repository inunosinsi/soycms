<?php
class InquiryOnMypageAdminList extends SOYShopAdminListBase{

	function getTabName(){
		SOY2::import("module.plugins.inquiry_on_mypage.util.InquiryOnMypageUtil");
		$config = InquiryOnMypageUtil::getConfig();
		if(isset($config["tab"]) && $config["tab"] == 1){
			return "お問い合せ";
		}
		return "";
	}

	function getTitle(){
		return "";
	}

	function getContent(){
		SOY2PageController::jump("Config.Detail?plugin=inquiry_on_mypage&list");
	}
}
SOYShopPlugin::extension("soyshop.admin.list", "inquiry_on_mypage", "InquiryOnMypageAdminList");
