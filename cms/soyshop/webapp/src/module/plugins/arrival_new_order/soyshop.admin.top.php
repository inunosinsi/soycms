<?php
class ArrivalNewOrderAdminTop extends SOYShopAdminTopBase{

	function getLink(){
		return SOY2PageController::createLink("Order");
	}

	function getLinkTitle(){
		return "注文一覧";
	}

	function getTitle(){
		return "新着の注文";
	}

	function getContent(){
		SOY2::import("module.plugins.arrival_new_order.page.NewOrderAreaPage");
		$form = SOY2HTMLFactory::createInstance("NewOrderAreaPage");
		$form->setConfigObj($this);
		$form->execute();
		return $form->getObject();
	}
}
SOYShopPlugin::extension("soyshop.admin.top", "arrival_new_order", "ArrivalNewOrderAdminTop");
