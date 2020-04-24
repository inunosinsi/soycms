<?php

class ReturnsSlipNumberFormPage extends WebPage{

	private $pluginObj;
	private $orderId;

	function __construct(){}

	function execute(){
		parent::__construct();

		$attr = self::getLogic()->getAttribute($this->orderId);
		if(strlen($attr->getValue1())){
			$placeholder = "伝票番号を追加で登録します。伝票番号が複数登録する場合は、カンマ区切りで登録します。";
		}else{
			$placeholder = "伝票番号を複数登録する場合は、カンマ区切りで登録します。";
		}

		$this->addInput("returns_slip_number", array(
			"name" => "ReturnsSlipNumber",
			"value" => "",
			"attr:placeholder" => $placeholder
		));

		DisplayPlugin::toggle("has_slip_number", strlen($attr->getValue1()));
	}

	private function getLogic(){
		static $logic;
		if(is_null($logic)) $logic = SOY2Logic::createInstance("module.plugins.returns_slip_number.logic.ReturnsSlipNumberLogic");
		return $logic;
	}

	function setPluginObj($pluginObj){
		$this->pluginObj = $pluginObj;
	}

	function setOrderId($orderId){
		$this->orderId = $orderId;
	}
}
