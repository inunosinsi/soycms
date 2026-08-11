<?php
class OrderEditOnMypageAdminTop extends SOYShopAdminTopBase{

	function getLink(){
		return null;
	}

	function getLinkTitle(){
		return null;
	}

	function getTitle(){
		return "マイページで顧客による注文編集";
	}

	function getContent(){
		SOY2::import("module.plugins.order_edit_on_mypage.page.OrderHistoryOnMyPage");
		$form = SOY2HTMLFactory::createInstance("OrderHistoryOnMyPage");
		$form->setConfigObj($this);
		$form->execute();
		return $form->getObject();
	}

	function allowDisplay(){
		return (SOYShopAuthUtil::getAuth() != SOYShopAuthUtil::AUTH_STORE_OWNER);
	}
}
SOYShopPlugin::extension("soyshop.admin.top", "order_edit_on_mypage", "OrderEditOnMypageAdminTop");
