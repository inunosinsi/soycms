<?php

class SlipNumberFormPage extends WebPage{

	private $pluginObj;
	private $orderId;

	function __construct(){}

	function execute(){
		parent::__construct();

		$slipNumber = soyshop_get_order_attribute_value($this->orderId, SlipNumberUtil::PLUGIN_ID, "string");
		if(strlen($slipNumber)){
			$placeholder = "伝票番号を追加で登録します。伝票番号が複数登録する場合は、カンマ区切りで登録します。";
		}else{
			$placeholder = "伝票番号を複数登録する場合は、カンマ区切りで登録します。";
		}

		$this->addInput("slip_number", array(
			"name" => "SlipNumber",
			"value" => "",
			"attr:placeholder" => $placeholder
		));

		DisplayPlugin::toggle("has_slip_number", strlen($slipNumber));
	}

	function setPluginObj($pluginObj){
		$this->pluginObj = $pluginObj;
	}

	function setOrderId($orderId){
		$this->orderId = $orderId;
	}
}
