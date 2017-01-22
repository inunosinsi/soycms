<?php
class ReserveCalendarAdminDetail extends SOYShopAdminDetailBase{

	function getTitle(){
		return "予約カレンダー詳細";
	}

	function getContent(){
		SOY2::import("module.plugins.reserve_calendar.page.admin.ReserveCalendarDetailPage");
		$form = SOY2HTMLFactory::createInstance("ReserveCalendarDetailPage");
		$form->setConfigObj($this);
		$form->setDetailId($this->getDetailId());
		$form->execute();
		return $form->getObject();
	}
}
SOYShopPlugin::extension("soyshop.admin.detail", "reserve_calendar", "ReserveCalendarAdminDetail");
?>