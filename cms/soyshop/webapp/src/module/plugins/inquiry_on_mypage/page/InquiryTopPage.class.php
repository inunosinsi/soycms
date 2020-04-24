<?php

class InquiryTopPage extends WebPage {

	private $configObj;

	function __construct(){
		parent::__construct();

		$inqs = self::getNoConfirmInquiries();
		$cnt = count($inqs);
		
		DisplayPlugin::toggle("no_inquiry", !$cnt);
		DisplayPlugin::toggle("has_inquiry", $cnt);

		SOY2::import("module.plugins.inquiry_on_mypage.component.InquiryListComponent");
		$this->createAdd("inquiry_list", "InquiryListComponent", array(
			"list" => $inqs
		));
	}

	private function getNoConfirmInquiries(){
		SOY2::imports("module.plugins.inquiry_on_mypage.domain.*");
		$inqDao = SOY2DAOFactory::create("SOYShop_InquiryDAO");
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
