<?php

class ShippingLabelFormPage extends WebPage{
	
	private $configObj;
	
	function __construct(){
		SOY2::import("module.plugins.print_shipping_label.util.ShippingLabelUtil");
	}
	
	function execute(){
		parent::__construct();


		$companies = array(
			ShippingLabelUtil::COMPANY_KURONEKO
		);
		
		foreach($companies as $comp){
			$this->addCheckBox("company_" . $comp, array(
				"name" => "ShippingLabel[company]",
				"value" => $comp,
				"label" => ShippingLabelUtil::getCompanyText($comp),
				"selected" => ($comp == ShippingLabelUtil::COMPANY_KURONEKO)
			));
		}
/**		
		$kuroneko = array(
			ShippingLabelUtil::TYPE_CORECT,
			ShippingLabelUtil::TYPE_HATSUBARAI,
			ShippingLabelUtil::TYPE_TYAKUBARAI
		);
		
		//クロネコ分のsoy:idタグ
		foreach($kuroneko as $label){
			$this->addCheckBox("kuroneko_" . $label, array(
				"name" => "ShippingLabel[kuroneko]",
				"value" => $label,
				"label" => ShippingLabelUtil::getText($label),
				"selected" => ($label == ShippingLabelUtil::TYPE_CORECT)
			));
		}
**/
		
		$config = ShippingLabelUtil::getConfig();

/**		
		$this->addInput("shipping_date", array(
			"name" => "ShippingDate",
			"value" => (isset($config["shipping_date"]) && $config["shipping_date"] == 1) ? date("Y-m-d", strtotime("+1 day")) : "",
			"class" => "date_picker_start",
			"id" => "shipping_label_date",
			"readonly" => true
		));
**/		
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