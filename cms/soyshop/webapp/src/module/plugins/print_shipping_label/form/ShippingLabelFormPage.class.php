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
		
		$this->addTextArea("product_name", array(
			"name" => "ShippingProduct",
			"value" => "",
			"style" => "width:300px;height:80px;"
		));
	}
	
	function setConfigObj($configObj){
		$this->configObj = $configObj;
	}
}