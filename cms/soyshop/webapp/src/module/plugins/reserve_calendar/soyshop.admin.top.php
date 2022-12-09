<?php
class ReserveCalendarAdminTop extends SOYShopAdminTopBase{

	function getLink(){
		//return SOY2PageController::createLink("Config.ShopConfig");
	}

	function getLinkTitle(){
		return "";
	}

	function getTitle(){
		return "予約状況";
	}

	function getContent(){
		if(SOYShopPluginUtil::checkIsActive("change_order_status_invalid")){
			//古い仮登録注文を無効注文(STATUS_INVALID=0)に変更する
			SOY2::import("module.plugins.change_order_status_invalid.util.ChangeOrderStatusInvalidUtil");
			ChangeOrderStatusInvalidUtil::changeInvalidStatusOlderOrder();
		}

		SOY2::import("module.plugins.reserve_calendar.page.admin.ReserveCalendarInfoPage");
		$form = SOY2HTMLFactory::createInstance("ReserveCalendarInfoPage");
		$form->setConfigObj($this);
		$form->execute();
		return $form->getObject();
	}
}
SOYShopPlugin::extension("soyshop.admin.top", "reserve_calendar", "ReserveCalendarAdminTop");
