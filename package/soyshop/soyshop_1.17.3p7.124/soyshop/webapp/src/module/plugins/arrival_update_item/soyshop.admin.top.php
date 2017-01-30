<?php
class ArrivalUpdateItemAdminTop extends SOYShopAdminTopBase{

	function getLink(){
		return SOY2PageController::createLink("Item");
	}
	
	function getLinkTitle(){
		return "商品";
	}

	function getTitle(){
		return "最近更新した商品";
	}

	function getContent(){
		SOY2::import("module.plugins.arrival_update_item.page.UpdateItemAreaPage");
		$form = SOY2HTMLFactory::createInstance("UpdateItemAreaPage");
		$form->setConfigObj($this);
		$form->execute();
		return $form->getObject();
	}
}
SOYShopPlugin::extension("soyshop.admin.top", "arrival_update_item", "ArrivalUpdateItemAdminTop");
?>