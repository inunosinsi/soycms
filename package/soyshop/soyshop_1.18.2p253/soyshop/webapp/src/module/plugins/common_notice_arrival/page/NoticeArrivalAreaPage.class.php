<?php

class NoticeArrivalAreaPage extends WebPage{

	private $configObj;

	function __construct(){}

	function execute(){
		parent::__construct();

		$users = array();
		$noticeLogic = SOY2Logic::createInstance("module.plugins.common_notice_arrival.logic.NoticeLogic");
		$users = $noticeLogic->getUsersForNewsPage(SOYShop_NoticeArrival::NOT_SENDED, SOYShop_NoticeArrival::NOT_CHECKED);

		SOY2::import("util.SOYShopPluginUtil");
		DisplayPlugin::toggle("notice_arrival", SOYShopPluginUtil::checkIsActive("common_notice_arrival"));
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
