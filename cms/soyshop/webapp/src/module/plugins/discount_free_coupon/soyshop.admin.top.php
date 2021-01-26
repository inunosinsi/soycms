<?php
class DiscountFreeCouponAdminTop extends SOYShopAdminTopBase{

	function getLink(){
		return SOY2PageController::createLink("Config.Detail?plugin=discount_free_coupon");
	}

	function getLinkTitle(){
		return "クーポン自由設定プラグイン";
	}

	function getTitle(){
		return "新着のクーポン使用履歴";
	}

	function getContent(){
		SOY2::import("module.plugins.discount_free_coupon.page.NewCouponAreaPage");
		$form = SOY2HTMLFactory::createInstance("NewCouponAreaPage");
		$form->setConfigObj($this);
		$form->execute();
		return $form->getObject();
	}

	function allowDisplay(){
		return (SOYShopAuthUtil::getAuth() != SOYShopAuthUtil::AUTH_STORE_OWNER);
	}
}
SOYShopPlugin::extension("soyshop.admin.top", "discount_free_coupon", "DiscountFreeCouponAdminTop");
