<?php

class OrderInvoiceConfigFormPage extends WebPage{
	
	private $configObj;
	
	function OrderInvoiceConfigFormPage(){
		SOY2::import("module.plugins.order_invoice.common.OrderInvoiceCommon");
	}
	
	function doPost(){
		
		if(soy2_check_token()){
			
			OrderInvoiceCommon::saveTemplateName($_POST["Template"]);
			OrderInvoiceCommon::saveConfig($_POST["Config"]);
			
			$this->configObj->redirect("updated");
		}
		
	}
	
	function execute(){
		WebPage::WebPage();
		
		$this->addModel("updated", array(
			"visible" => (isset($_GET["updated"]))
		));
		
		$this->addForm("form");
		
		$this->addSelect("template", array(
			"name" => "Template",
			"options" => OrderInvoiceCommon::getTemplateList(),
			"selected" => OrderInvoiceCommon::getTemplateName()
		));
		
		$config = OrderInvoiceCommon::getConfig();
		
		$this->addCheckBox("payment", array(
			"name" => "Config[payment]",
			"value" => 1,
			"selected" => (isset($config["payment"]) && $config["payment"] == 1),
			"label" => " 表示する"
		));
		
		$this->addInput("title", array(
			"name" => "Config[title]",
			"value" => (isset($config["title"])) ? $config["title"] : ""
		));
		
		$this->addTextArea("content", array(
			"name" => "Config[content]",
			"value" => (isset($config["content"])) ? $config["content"] : "",
			"style" => "height:150px;"
		));
	}
	
	function setConfigObj($configObj){
		$this->configObj = $configObj;
	}
}
?>