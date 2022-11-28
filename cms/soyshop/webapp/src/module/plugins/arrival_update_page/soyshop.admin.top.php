<?php
class ArrivalUpdatePageAdminTop extends SOYShopAdminTopBase{

	function allowDisplay(){
		return AUTH_SITE;
	}

	function getLink(){
		return SOY2PageController::createLink("Site.Pages");
	}

	function getLinkTitle(){
		return "ページ管理";
	}

	function getTitle(){
		return "最近更新したページ";
	}

	function getContent(){
		SOY2::import("module.plugins.arrival_update_page.page.UpdatePageAreaPage");
		$form = SOY2HTMLFactory::createInstance("UpdatePageAreaPage");
		$form->setConfigObj($this);
		$form->execute();
		return $form->getObject();
	}
}
SOYShopPlugin::extension("soyshop.admin.top", "arrival_update_page", "ArrivalUpdatePageAdminTop");
