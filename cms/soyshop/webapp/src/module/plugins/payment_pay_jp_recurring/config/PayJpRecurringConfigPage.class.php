<?php

class PayJpRecurringConfigPage extends WebPage {

	private $configObj;

	function __construct(){
		SOY2::import("module.plugins.payment_pay_jp_recurring.util.PayJpRecurringUtil");
	}

	function doPost(){
		if(soy2_check_token()){
			PayJpRecurringUtil::saveConfig($_POST["Config"]);
			$this->configObj->redirect("updated");
		}
	}

	function execute(){
		parent::__construct();

		$config = PayJpRecurringUtil::getConfig();

		$this->addForm("form");

		$this->addCheckBox("sandbox", array(
			"name" => "Config[sandbox]",
			"value" => 1,
			"selected" => (isset($config["sandbox"]) && $config["sandbox"] == 1),
			"label" => "テストモート"
		));

		foreach(array("test", "production") as $t){
			foreach(array("secret", "public") as $tt){
				$this->addInput($t . "_" . $tt . "_key", array(
					"name" => "Config[" . $t . "][" . $tt . "_key]",
					"value" => (isset($config[$t][$tt . "_key"])) ? $config[$t][$tt . "_key"] : ""
				));
			}
		}

		$this->addCheckBox("3d_secure", array(
			"name" => "Config[secure]",
			"value" => 1,
			"selected" => (isset($config["secure"]) && $config["secure"] == 1),
			"label" => "3Dセキュアを利用する"
		));

		$this->addCheckBox("3d_secure_type_redirect", array(
			"name" => "Config[secure_type]",
			"value" => PayJpRecurringUtil::SECURE_TYPE_REDIRECT,
			"selected" => (isset($config["secure_type"]) && $config["secure_type"] == PayJpRecurringUtil::SECURE_TYPE_REDIRECT),
			"label" => "リダイレクト型(未対応)",
			"attr:onclick" => "select_3d_secure_type(0)",
			"disabled" => true
		));

		$this->addCheckBox("3d_secure_type_subwindow", array(
			"name" => "Config[secure_type]",
			"value" => PayJpRecurringUtil::SECURE_TYPE_SUBWINDOW,
			"selected" => (!isset($config["secure_type"]) || $config["secure_type"] == PayJpRecurringUtil::SECURE_TYPE_SUBWINDOW),
			"label" => "サブウィンドウ型",
			"attr:onclick" => "select_3d_secure_type(1)"
		));

		$this->addCheckBox("3d_secure_attempt", array(
			"name" => "Config[attempt]",
			"value" => 1,	
			"selected" => (isset($config["attempt"]) && $config["attempt"] == 1),
			"label" => "3Dセキュアでアテンプト取引を有効にする"
		));

		$this->addLabel("redirect_url", array(
			"text" => soyshop_get_cart_url(false, true) . "?soyshop_notification=payment_pay_jp_recurring"	
		));

		$this->addLabel("3d_config_js", array(
			"html" => file_get_contents(dirname(dirname(__DIR__))."/payment_pay_jp/js/3d_config.js")
		));

		$this->addLabel("base_html_path", array(
			"html" => dirname(dirname(__FILE__)) . "/option/<strong>PayJpRecurringOptionPage.html</strong>"
		));

		$this->addLabel("change_html_path", array(
			"html" => dirname(dirname(__FILE__)) . "/option/<strong>_PayJpRecurringOptionPage.html</strong>"
		));
	}

	function setConfigObj($configObj){
		$this->configObj = $configObj;
	}
}
