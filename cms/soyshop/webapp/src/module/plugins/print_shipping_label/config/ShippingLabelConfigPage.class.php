<?php

class ShippingLabelConfigPage extends WebPage{
	
	private $configObj;
	
	function __construct(){
		SOY2::import("module.plugins.print_shipping_label.util.PrintShippingLabelUtil");
	}
	
	function doPost(){
		if(soy2_check_token()){
			
			PrintShippingLabelUtil::saveConfig($_POST["Config"]);
			$this->configObj->redirect("updated");
		}
	}
	
	function execute(){
		WebPage::__construct();
		
		$config = PrintShippingLabelUtil::getConfig();
		
		$this->addForm("form");
		
		$this->addCheckBox("shipping_date", array(
			"name" => "Config[shipping_date]",
			"value" => 1,
			"selected" => (isset($config["shipping_date"]) && $config["shipping_date"] == 1),
			"label" => "出力ボタンの日付フォームに明日の日付を入れておく"
		));
		
		$this->addTextArea("product_name", array(
			"name" => "Config[product]",
			"value" => (isset($config["product"])) ? $config["product"] : "",
			"style" => "width:300px;height:80px;"
		));
	}
	
	function setConfigObj($configObj){
		$this->configObj = $configObj;
	}
}