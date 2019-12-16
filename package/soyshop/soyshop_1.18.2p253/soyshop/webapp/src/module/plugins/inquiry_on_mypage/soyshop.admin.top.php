<?php
class InquiryOnMypageAdminTop extends SOYShopAdminTopBase{

	function getLink(){
		//return SOY2PageController::createLink("Config.ShopConfig");
	}

	function getLinkTitle(){
		return "";
	}

	function getTitle(){
		return "新着のお問い合わせ";
	}

	function getContent(){
		SOY2::import("module.plugins.inquiry_on_mypage.page.InquiryTopPage");
		$form = SOY2HTMLFactory::createInstance("InquiryTopPage");
		$form->setConfigObj($this);
		$form->execute();
		return $form->getObject();
	}
}
SOYShopPlugin::extension("soyshop.admin.top", "inquiry_on_mypage", "InquiryOnMypageAdminTop");
