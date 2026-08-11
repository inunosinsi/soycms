<?php
class InquiryOnMypageAdminTop extends SOYShopAdminTopBase{

	function getLink(){
		return SOY2PageController::createLink("Config.Detail?plugin=inquiry_on_mypage&list");
	}

	function getLinkTitle(){
		return "マイページからのお問い合わせ一覧";
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

	function allowDisplay(){
		return (SOYShopAuthUtil::getAuth() != SOYShopAuthUtil::AUTH_STORE_OWNER);
	}
}
SOYShopPlugin::extension("soyshop.admin.top", "inquiry_on_mypage", "InquiryOnMypageAdminTop");
