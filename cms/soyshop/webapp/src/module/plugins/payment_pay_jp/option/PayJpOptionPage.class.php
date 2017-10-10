<?php

class PayJpOptionPage extends WebPage {

	private $cart;
	private $configObj;
	private $errorMessage;

	function __construct(){
		SOY2::import("module.plugins.payment_pay_jp.util.PayJpUtil");
		if(isset($_GET["error"])){
			$errorCode = PayJpUtil::get("errorCode");
			$this->errorMessage = PayJpUtil::getErrorText($errorCode);
			PayJpUtil::clear("errorCode");
		}
	}

	function execute(){
		parent::__construct();

		//エラー
		DisplayPlugin::toggle("error", isset($this->errorMessage));
		$this->addLabel("error_message", array(
			"text" => $this->errorMessage
		));

		$values = PayJpUtil::get("myCard");

		$this->addForm("form");

		for ($i = 0; $i < 4; $i++) {
			$this->addInput("card_" . ($i + 1), array(
				"name" => "card[$i]",
				"value" => (isset($values["number"])) ? substr($values["number"], (4*$i), 4) : "",
				"attr:required" => true
			));
		}

		$this->addSelect("month", array(
			"name" => "month",
			"options" => range(1, 12),
			"selected" => (isset($values["exp_month"])) ? $values["exp_month"] : ""
		));
		$this->addSelect("year", array(
			"name" => "year",
			"options" => self::getYearRange(),
			"selected" => (isset($values["exp_year"])) ? $values["exp_year"] : ""
		));

		$this->addInput("cvc", array(
			"name" => "cvc",
			"value" => (isset($values["cvc"])) ? $values["cvc"] : "",
			"attr:required" => true
		));

		$this->addInput("name", array(
			"name" => "name",
			"value" => PayJpUtil::get("name"),
			"attr:required" => true
		));

		$config = PayJpUtil::getConfig();
		DisplayPlugin::toggle("repeat", (isset($config["repeat"]) && $config["repeat"] == 1));

		$this->addCheckBox("member_register", array(
			"name" => "member",
			"value" => 1,
			"selected" => PayJpUtil::get("member"),
			"label" => "入力したカード情報で会員登録を行う"
		));

		$this->addLink("back_link", array(
			"link" => "?back"
		));
	}

	private function getYearRange(){
		$year = date("y");
		$array = array();
		$end = (int)$year + 10;

		for($i = $year; $i <= $end; $i++){
			$array[$i] = $i;
		}
		return $array;
	}

	function setCart($cart){
		$this->cart = $cart;
	}

	function setConfigObj($configObj){
		$this->configObj = $configObj;
	}
}
