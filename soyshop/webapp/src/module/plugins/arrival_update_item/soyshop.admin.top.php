<?php
class ArrivalUpdateItemAdminTop extends SOYShopAdminTopBase{

	function getLink(){
		return SOY2PageController::createLink("Item");
	}

	function getLinkTitle(){
		if(SOYShopAuthUtil::getAuth() == SOYShopAuthUtil::AUTH_STORE_OWNER) return null;
		return "商品";
	}

	function getTitle(){
		return "最近更新した商品";
	}

	function getContent(){
		if(SOYShopAuthUtil::getAuth() != SOYShopAuthUtil::AUTH_STORE_OWNER){
			SOY2::import("module.plugins.arrival_update_item.page.UpdateItemAreaPage");
			$form = SOY2HTMLFactory::createInstance("UpdateItemAreaPage");
			$form->setConfigObj($this);
			$form->execute();
			return $form->getObject();
		}else{	//モール出店者
			return "<div class=\"alert alert-warning\">@ToDo モール出店者用の表示</div>";
		}
	}
}
SOYShopPlugin::extension("soyshop.admin.top", "arrival_update_item", "ArrivalUpdateItemAdminTop");
