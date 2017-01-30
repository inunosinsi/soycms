<?php

class NoticeArrivalAreaPage extends WebPage{
	
	private $configObj;
	
	function __construct(){}
	
	function execute(){
		WebPage::__construct();
		
		$users = array();
		$noticeLogic = SOY2Logic::createInstance("module.plugins.common_notice_arrival.logic.NoticeLogic");
		$users = $noticeLogic->getUsersForNewsPage(SOYShop_NoticeArrival::NOT_SENDED, SOYShop_NoticeArrival::NOT_CHECKED);
		
		DisplayPlugin::toggle("notice_arrival", $isActive);
		DisplayPlugin::toggle("has_notice_arrival", (count($users) > 0));
		DisplayPlugin::toggle("no_notice_arrival", (count($users) === 0));

		$this->createAdd("notice_arrival_list", "_common.Plugin.NoticeArrivalListComponent", array(
			"list" => $users
		));
	}
	
	function setConfigObj($configObj){
		$this->configObj = $configObj;
	}
}
?>