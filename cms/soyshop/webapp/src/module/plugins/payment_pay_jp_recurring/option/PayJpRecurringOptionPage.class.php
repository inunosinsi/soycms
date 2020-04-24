<?php

class PayJpRecurringOptionPage extends WebPage {

	private $configObj;
	private $errorMessage;

	function __construct(){
		SOY2::import("module.plugins.payment_pay_jp_recurring.util.PayJpRecurringUtil");
		if(isset($_GET["error"])){
			$errorCode = PayJpRecurringUtil::get("errorCode");
			$this->errorMessage = PayJpRecurringUtil::getErrorText($errorCode);
			PayJpRecurringUtil::clear("errorCode");
		}
	}

	function execute(){
		parent::__construct();

		//エラー
		DisplayPlugin::toggle("error", isset($this->errorMessage));
		$this->addLabel("error_message", array(
			"text" => $this->errorMessage
		));

		$values = PayJpRecurringUtil::get("myCard");

		$this->addForm("form");

		for ($i = 0; $i < 4; $i++) {
			$this->addInput("card_" . ($i + 1), array(
				"name" => "card[" . $i . "]",
				"value" => (isset($values["number"])) ? substr($values["number"], (4*$i), 4) : "",
				"style" => "ime-mode:inactive;",
				"attr:id" => "card_" . $i,
				"attr:required" => true
			));
		}

		$this->addSelect("month", array(
			"name" => "month",
			"options" => range(1, 12),
			"selected" => (isset($values["exp_month"])) ? $values["exp_month"] : "",
			"attr:id" => "month"
		));
		$this->addSelect("year", array(
			"name" => "year",
			"options" => self::getYearRange(),
			"selected" => (isset($values["exp_year"])) ? substr($values["exp_year"], 2) : "",
			"attr:id" => "year"
		));

		$this->addInput("cvc", array(
			"name" => "cvc",
			"value" => (isset($values["cvc"])) ? $values["cvc"] : "",
			"attr:id" => "cvc",
			"attr:required" => true
		));

		$this->addInput("name", array(
			"name" => "name",
			"value" => PayJpRecurringUtil::get("name"),
			"attr:id" => "name",
			"attr:required" => true
		));

		//非通過型に対応
		$logic = SOY2Logic::createInstance("module.plugins.payment_pay_jp_recurring.logic.RecurringLogic");
		$config = $logic->getPayJpConfig();
		$this->addLabel("key", array(
			"text" => (isset($config["public_key"])) ? trim($config["public_key"]) : ""
		));

		$this->addLabel("error_message_list_js", array(
			"html" => $logic->getErrorMessageListOnJS()
		));

		$this->addLabel("token_js", array(
			"html" => file_get_contents(dirname(dirname(dirname(__FILE__))) . "/payment_pay_jp/js/token.js")
		));

		$this->addLabel("button_label", array(
			"text" => (SOYSHOP_CART_MODE) ? "支払う" : "更新する"
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

	function setConfigObj($configObj){
		$this->configObj = $configObj;
	}
}
