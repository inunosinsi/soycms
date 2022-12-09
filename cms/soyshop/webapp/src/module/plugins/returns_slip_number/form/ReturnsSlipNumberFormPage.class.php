<?php

class ReturnsSlipNumberFormPage extends WebPage{

	private $pluginObj;
	private $orderId;

	function __construct(){}

	function execute(){
		parent::__construct();

		$attr = soyshop_get_order_attribute_object($this->orderId, ReturnsSlipNumberUtil::PLUGIN_ID);
		if(is_string($attr->getValue1()) && strlen($attr->getValue1())){
			$placeholder = "伝票番号を追加で登録します。伝票番号が複数登録する場合は、カンマ区切りで登録します。";
		}else{
			$placeholder = "伝票番号を複数登録する場合は、カンマ区切りで登録します。";
		}

		$this->addInput("returns_slip_number", array(
			"name" => "ReturnsSlipNumber",
			"value" => "",
			"attr:placeholder" => $placeholder
		));

		DisplayPlugin::toggle("has_slip_number", (is_string($attr->getValue1()) && strlen($attr->getValue1())));
	}

	function setPluginObj($pluginObj){
		$this->pluginObj = $pluginObj;
	}

	function setOrderId($orderId){
		$this->orderId = $orderId;
	}
}
