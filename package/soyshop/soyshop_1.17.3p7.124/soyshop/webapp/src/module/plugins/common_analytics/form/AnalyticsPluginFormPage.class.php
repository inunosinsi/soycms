<?php

class AnalyticsPluginFormPage extends WebPage{
	
	private $configObj;
	
	function __construct(){
		SOY2::import("module.plugins.common_analytics.util.AnalyticsPluginUtil");
		SOY2::import("util.SOYShopPluginUtil");
	}
	
	function execute(){
		WebPage::__construct();
		
		$this->addCheckBox("type_month", array(
			"name" => "AnalyticsPlugin[type]",
			"value" => "month",
			"selected" => true,
			"label" => AnalyticsPluginUtil::TYPE_MONTH
		));
		
		$this->addCheckBox("type_newcustomer", array(
			"name" => "AnalyticsPlugin[type]",
			"value" => "newcustomer",
			"selected" => false,
			"label" => AnalyticsPluginUtil::TYPE_NEWCUSTOMER
		));
		
		$this->addCheckBox("type_area", array(
			"name" => "AnalyticsPlugin[type]",
			"value" => "area",
			"selected" => false,
			"label" => AnalyticsPluginUtil::TYPE_AREA
		));
		
		$this->addCheckBox("type_repeat", array(
			"name" => "AnalyticsPlugin[type]",
			"value" => "repeat",
			"selected" => false,
			"label" => AnalyticsPluginUtil::TYPE_REPEAT
		));
		
		$this->addCheckBox("type_repeatmonth", array(
			"name" => "AnalyticsPlugin[type]",
			"value" => "repeatmonth",
			"selected" => false,
			"label" => AnalyticsPluginUtil::TYPE_REPEATMONTH
		));
		
		$this->addCheckBox("type_ordercount", array(
			"name" => "AnalyticsPlugin[type]",
			"value" => "ordercount",
			"selected" => false,
			"label" => AnalyticsPluginUtil::TYPE_ORDERCOUNT
		));
		
		$this->addCheckBox("type_itemrate", array(
			"name" => "AnalyticsPlugin[type]",
			"value" => "itemrate",
			"selected" => false,
			"label" => AnalyticsPluginUtil::TYPE_ITEMRATE
		));
		
		$this->addCheckBox("type_carrier", array(
			"name" => "AnalyticsPlugin[type]",
			"value" => "carrier",
			"selected" => false,
			"label" => AnalyticsPluginUtil::TYPE_CARRIER
		));
		
		$this->addCheckBox("type_language", array(
			"name" => "AnalyticsPlugin[type]",
			"value" => "language",
			"selected" => false,
			"label" => AnalyticsPluginUtil::TYPE_LANGUAGE,
			"visible" => SOYShopPluginUtil::checkIsActive("util_multi_language")
		));
		
		$this->addLabel("analytics_label_month", array(
			"text" => AnalyticsPluginUtil::TYPE_MONTH
		));
		
		$this->addLabel("analytics_label_newcustomer", array(
			"text" => AnalyticsPluginUtil::TYPE_NEWCUSTOMER
		));
		
		$this->addInput("analytics_period_start", array(
			"name" => "AnalyticsPlugin[period][start]",
			"value" => "",
			"readonly" => true
		));
		
		$this->addInput("analytics_period_end", array(
			"name" => "AnalyticsPlugin[period][end]",
			"value" => "",
			"readonly" => true
		));
		
		$this->addInput("analytics_limit", array(
			"name" => "AnalyticsPlugin[limit]",
			"value" => ""
		));
	}
	
	function setConfigObj($configObj){
		$this->configObj = $configObj;
	}
}

?>