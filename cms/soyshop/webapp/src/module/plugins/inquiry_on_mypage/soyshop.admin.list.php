<?php
class InquiryOnMypageAdminList extends SOYShopAdminListBase{

	function getTabName(){
		return "";
	}

	function getTitle(){
		return "";
	}

	function getContent(){
		return "dummy";
	}
}
SOYShopPlugin::extension("soyshop.admin.list", "inquiry_on_mypage", "InquiryOnMypageAdminList");
