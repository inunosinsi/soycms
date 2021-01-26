<?php
class ArrivalNewOrderAdminTop extends SOYShopAdminTopBase{

	function getLink(){
		return SOY2PageController::createLink("Order");
	}

	function getLinkTitle(){
		if(SOYShopAuthUtil::getAuth() == SOYShopAuthUtil::AUTH_STORE_OWNER) return null;
		return "注文一覧";
	}

	function getTitle(){
		return "新着の注文";
	}

	function getContent(){
		if(SOYShopAuthUtil::getAuth() != SOYShopAuthUtil::AUTH_STORE_OWNER){
			SOY2::import("module.plugins.arrival_new_order.page.NewOrderAreaPage");
			$form = SOY2HTMLFactory::createInstance("NewOrderAreaPage");
			$form->setConfigObj($this);
			$form->execute();
			return $form->getObject();
		}else{	//モール出店者
			return "<div class=\"alert alert-warning\">@ToDo モール出店者用の表示</div>";
		}
	}
}
SOYShopPlugin::extension("soyshop.admin.top", "arrival_new_order", "ArrivalNewOrderAdminTop");
