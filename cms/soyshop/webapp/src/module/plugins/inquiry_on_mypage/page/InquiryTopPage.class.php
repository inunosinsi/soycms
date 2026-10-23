<?php

class InquiryTopPage extends WebPage {

	private $configObj;

	function __construct(){
		parent::__construct();
		SOY2::import("module.plugins.arrival_new_order.fn.fn", ".php");

		$inqs = self::_getNoConfirmInquiries();
		$cnt = count($inqs);

		$cfgs = soyshop_get_arrival_config("inquiry_on_mypage");

		DisplayPlugin::toggle("more_inquiry", $cnt > $cfgs["count"]);
		DisplayPlugin::toggle("no_inquiry", !$cnt);
		DisplayPlugin::toggle("has_inquiry", $cnt);

		if($cnt > $cfgs["count"]) $inqs = array_slice($inqs, 0, $cfgs["count"]);

		SOY2::import("module.plugins.inquiry_on_mypage.component.InquiryListComponent");
		$this->createAdd("inquiry_list", "InquiryListComponent", array(
			"list" => $inqs,
			"userNameList" => SOY2Logic::createInstance("logic.user.UserLogic")->getUserNameListByUserIds(self::_getUserIds($inqs))
		));

		$this->addLabel("display_count", array(
			"text" => $cfgs["count"]	
		));
	}

	private function _getUserIds(array $inqs){
		if(!count($inqs)) return array();

		$ids = array();
		foreach($inqs as $inq){
			$ids[] = $inq->getUserId();
		}
		return $ids;
	}

	private function _getNoConfirmInquiries(){
		SOY2::imports("module.plugins.inquiry_on_mypage.domain.*");
		$inqDao = SOY2DAOFactory::create("SOYShop_InquiryDAO");
		$cfgs = soyshop_get_arrival_config("inquiry_on_mypage");
		
		try{
			$res = $inqDao->executeQuery(
				"SELECT tracking_number, create_date, user_id, mail_log_id FROM soyshop_inquiry ".
				"WHERE is_confirm = :confirm ".
				"AND create_date > :date ".
				"ORDER BY create_date DESC ".
				"LIMIT ".($cfgs["count"]+1),
				array(
					":confirm" => SOYShop_Inquiry::NO_CONFIRM,
					":date" => strtotime("-".$cfgs["period"]."month")
				)
			);
		}catch(Exception $e){
			return array();
		}
		if(!count($res)) return array();

		$_arr = array();
		foreach($res as $v){
			$_arr[] = $inqDao->getObject($v);
		}
		return $_arr;
	}

	function setConfigObj($configObj){
		$this->configObj = $configObj;
	}
}
