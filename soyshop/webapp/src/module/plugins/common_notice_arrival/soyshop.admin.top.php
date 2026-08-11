<?php
class NoticeArrivalAdminTop extends SOYShopAdminTopBase{

	function getLink(){
		return;
	}

	function getLinkTitle(){
		return;
	}

	function getTitle(){
		return "入荷通知希望顧客";
	}

	function getContent(){
		SOY2::import("module.plugins.common_notice_arrival.page.NoticeArrivalAreaPage");
		$form = SOY2HTMLFactory::createInstance("NoticeArrivalAreaPage");
		$form->setConfigObj($this);
		$form->execute();
		return $form->getObject();
	}

	function allowDisplay(){
		return (SOYShopAuthUtil::getAuth() != SOYShopAuthUtil::AUTH_STORE_OWNER);
	}
}
SOYShopPlugin::extension("soyshop.admin.top", "common_notice_arrival", "NoticeArrivalAdminTop");
