<?php

class ShippingLabelFormPage extends WebPage{
	
	private $configObj;
	
	function __construct(){
		SOY2::import("module.plugins.print_shipping_label.util.PrintShippingLabelUtil");
	}
	
	function execute(){
		WebPage::__construct();
		
		$kuroneko = array(
			PrintShippingLabelUtil::TYPE_CONNECT,
			PrintShippingLabelUtil::TYPE_HATSUBARAI,
			PrintShippingLabelUtil::TYPE_TYAKUBARAI
		);
		
		//クロネコ分のsoy:idタグ
		foreach($kuroneko as $label){
			$this->addCheckBox("kuroneko_" . $label, array(
				"name" => "ShippingLabel[kuroneko]",
				"value" => $label,
				"label" => PrintShippingLabelUtil::getText($label),
				"selected" => ($label == PrintShippingLabelUtil::TYPE_CONNECT)
			));
		}
		
		$config = PrintShippingLabelUtil::getConfig();
		
		$this->addInput("shipping_date", array(
			"name" => "ShippingDate",
			"value" => (isset($config["shipping_date"]) && $config["shipping_date"] == 1) ? date("Y-m-d", strtotime("+1 day")) : "",
			"class" => "date_picker_start",
			"id" => "shipping_label_date",
			"readonly" => true
		));
		
		$this->addTextArea("product_name", array(
			"name" => "ShippingProduct",
			"value" => (isset($config["product"])) ? $config["product"] : "",
			"style" => "width:300px;height:80px;"
		));
	}
	
	function setConfigObj($configObj){
		$this->configObj = $configObj;
	}
}