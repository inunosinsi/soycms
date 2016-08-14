<?php

class AggregateFormPage extends WebPage{
	
	private $configObj;
	
	function __construct(){
		SOY2::import("module.plugins.aggregate.util.AggregateUtil");
	}
	
	function execute(){
		WebPage::__construct();

		$this->addCheckBox("type_month", array(
			"name" => "Aggregate[type]",
			"value" => "month",
			"selected" => true,
			"label" => AggregateUtil::TYPE_MONTH
		));
				
		$this->addCheckBox("type_itemrate", array(
			"name" => "Aggregate[type]",
			"value" => "itemrate",
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