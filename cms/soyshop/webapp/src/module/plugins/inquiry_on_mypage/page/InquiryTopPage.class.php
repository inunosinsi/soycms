<?php

class InquiryTopPage extends WebPage {

	private $configObj;

	function __construct(){
		parent::__construct();

		$inqs = self::_getNoConfirmInquiries();
		$cnt = count($inqs);

		DisplayPlugin::toggle("more_inquiry", $cnt > 15);
		DisplayPlugin::toggle("no_inquiry", !$cnt);
		DisplayPlugin::toggle("has_inquiry", $cnt);

		SOY2::import("module.plugins.inquiry_on_mypage.component.InquiryListComponent");
		$this->createAdd("inquiry_list", "InquiryListComponent", array(
			"list" => array_slice($inqs, 0, 15),
			"userNameList" => SOY2Logic::createInstance("logic.user.UserLogic")->getUserNameListByUserIds(self::_getUserIds($inqs))
		));
	}

	private function _getUserIds($inqs){
		if(!is_array($inqs) || !count($inqs)) return array();

		$ids = array();
		foreach($inqs as $inq){
			$ids[] = $inq->getUserId();
		}
		return $ids;
	}

	private function _getNoConfirmInquiries(){
		SOY2::imports("module.plugins.inquiry_on_mypage.domain.*");
		$inqDao = SOY2DAOFactory::create("SOYShop_InquiryDAO");
		$inqDao->setLimit(16);
		$inqDao->setOrder("create_date DESC");
		try{
			return $inqDao->getByIsConfirm(SOYShop_Inquiry::NO_CONFIRM);
		}catch(Exception $e){
			return array();
		}
	}

	function setConfigObj($configObj){
		$this->configObj = $configObj;
	}
}
