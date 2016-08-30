<?php

class AggregateFormPage extends WebPage{
	
	private $configObj;
	
	function __construct(){
		SOY2::import("module.plugins.common_aggregate.util.AggregateUtil");
	}
	
	function execute(){
		WebPage::__construct();

		$this->addCheckBox("type_month", array(
			"name" => "Aggregate[type]",
			"value" => AggregateUtil::MODE_MONTH,
			"selected" => true,
			"label" => AggregateUtil::TYPE_MONTH
		));
		
		$this->addCheckBox("type_day", array(
			"name" => "Aggregate[type]",
			"value" => AggregateUtil::MODE_DAY,
			"selected" => false,
			"label" => AggregateUtil::TYPE_DAY
		));
				
		$this->addCheckBox("type_itemrate", array(
			"name" => "Aggregate[type]",
			"value" => AggregateUtil::MODE_ITEMRATE,
			"selected" => false,
			"label" => AggregateUtil::TYPE_ITEMRATE
		));
		
		
		$this->addLabel("aggregate_label_month", array(
			"text" => AggregateUtil::TYPE_MONTH
		));
				
		$this->addInput("aggregate_period_start", array(
			"name" => "Aggregate[period][start]",
			"value" => "",
			"readonly" => true
		));
		
		$this->addInput("aggregate_period_end", array(
			"name" => "Aggregate[period][end]",
			"value" => "",
			"readonly" => true
		));
		
		$this->addInput("aggregate_limit", array(
			"name" => "Aggregate[limit]",
			"value" => 50
		));
	}
	
	function setConfigObj($configObj){
		$this->configObj = $configObj;
	}
}

?>