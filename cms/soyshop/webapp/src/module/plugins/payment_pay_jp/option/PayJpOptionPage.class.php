<?php

class PayJpOptionPage extends WebPage {

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
				"style" => "ime-mode:inactive;",
				"attr:id" => "card_" . $i,
				"attr:required" => true,
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
			"attr:required" => true,
			"attr:id" => "cvc"
		));

		$this->addInput("name", array(
			"name" => "name",
			"value" => PayJpUtil::get("name"),
			"attr:required" => true,
			"attr:id" => "name"
		));

		//一旦停止
		/**
		<dd soy:display="repeat">
			<input type="checkbox" soy:id="member_register">
		</dd>
		**/
		// $config = PayJpUtil::getConfig();
		// DisplayPlugin::toggle("repeat", (isset($config["repeat"]) && $config["repeat"] == 1));
		//
		// $this->addCheckBox("member_register", array(
		// 	"name" => "member",
		// 	"value" => 1,
		// 	"selected" => PayJpUtil::get("member"),
		// 	"label" => "入力したカード情報で会員登録を行う"
		// ));

		//非通過型に対応
		$logic = SOY2Logic::createInstance("module.plugins.payment_pay_jp.logic.PayJpLogic");
		$config = $logic->getPayJpConfig();
		$this->addLabel("key", array(
			"text" => (isset($config["public_key"])) ? trim($config["public_key"]) : ""
		));

		$this->addLabel("error_message_list_js", array(
			"html" => $logic->getErrorMessageListOnJS()
		));

		$this->addLabel("token_js", array(
			"html" => file_get_contents(dirname(dirname(__FILE__)) . "/js/token.js")
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
