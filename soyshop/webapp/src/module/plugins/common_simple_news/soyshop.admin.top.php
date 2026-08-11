<?php
class SimpleNewsAdminTop extends SOYShopAdminTopBase{

	function getLink(){
		return (AUTH_CONFIG) ? SOY2PageController::createLink("Config.Detail?plugin=common_simple_new") : "";
	}

	function getLinkTitle(){
		return (AUTH_CONFIG) ? "新着情報の編集" : "";
	}

	function getTitle(){
		return "新着情報";
	}

	function getContent(){
		SOY2::import("module.plugins.common_simple_news.page.SimpleNewsAreaPage");
		$form = SOY2HTMLFactory::createInstance("SimpleNewsAreaPage");
		$form->setConfigObj($this);
		$form->execute();
		return $form->getObject();
	}

	function allowDisplay(){
		return (SOYShopAuthUtil::getAuth() != SOYShopAuthUtil::AUTH_STORE_OWNER);
	}
}
SOYShopPlugin::extension("soyshop.admin.top", "common_simple_news", "SimpleNewsAdminTop");
