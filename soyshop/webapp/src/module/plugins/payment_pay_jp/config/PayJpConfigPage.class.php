<?php

class PayJpConfigPage extends WebPage {

	private $configObj;

	function __construct(){
		SOY2::import("module.plugins.payment_pay_jp.util.PayJpUtil");
	}

	function doPost(){
		if(soy2_check_token()){
			PayJpUtil::saveConfig($_POST["Config"]);
			$this->configObj->redirect("updated");
		}
	}

	function execute(){
		parent::__construct();

		$config = PayJpUtil::getConfig();

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

		$this->addCheckBox("capture", array(
			"name" => "Config[capture]",
			"value" => 1,
			"selected" => (isset($config["capture"]) && $config["capture"] == 1),
			"label" => "支払い時に売上として扱う(チェックがない場合は仮売上：支払状況が支払待ちで登録されます)"
		));

		$this->addCheckBox("3d_secure", array(
			"name" => "Config[secure]",
			"value" => 1,
			"selected" => (isset($config["secure"]) && $config["secure"] == 1),
			"label" => "3Dセキュアを利用する"
		));

		$this->addCheckBox("3d_secure_type_redirect", array(
			"name" => "Config[secure_type]",
			"value" => PayJpUtil::SECURE_TYPE_REDIRECT,
			"selected" => (isset($config["secure_type"]) && $config["secure_type"] == PayJpUtil::SECURE_TYPE_REDIRECT),
			"label" => "リダイレクト型",
			"attr:onclick" => "select_3d_secure_type(0)"
		));

		$this->addCheckBox("3d_secure_type_subwindow", array(
			"name" => "Config[secure_type]",
			"value" => PayJpUtil::SECURE_TYPE_SUBWINDOW,
			"selected" => (!isset($config["secure_type"]) || $config["secure_type"] == PayJpUtil::SECURE_TYPE_SUBWINDOW),
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
			"text" => soyshop_get_cart_url(false, true) . "?soyshop_notification=payment_pay_jp"	
		));

		$this->addLabel("3d_config_js", array(
			"html" => file_get_contents(dirname(__DIR__)."/js/3d_config.js")
		));

		$this->addCheckBox("repeat", array(
			"name" => "Config[repeat]",
			"value" => 1,
			"selected" => (isset($config["repeat"]) && $config["repeat"] == 1),
			"label" => "カード番号入力画面で二回目の購入時は入力を省略のチェックボックスを出力する(カード情報で会員登録を行う)"
		));

		$this->addCheckBox("select", array(
			"name" => "Config[select]",
			"value" => 1,
			"selected" => (isset($config["select"]) && $config["select"] == 1),
			"label" => "会員登録した顧客に対して、支払い方法選択時にカード情報の入力の省略のチェックボックスを出力する"
		));

		$this->addLabel("base_html_path", array(
			"html" => dirname(dirname(__FILE__)) . "/option/<strong>PayJpOptionPage.html</strong>"
		));

		$this->addLabel("change_html_path", array(
			"html" => dirname(dirname(__FILE__)) . "/option/<strong>_PayJpOptionPage.html</strong>"
		));

		$this->addLabel("Integrate_html_path", array(
			"html" => dirname(dirname(__FILE__)) . "/option/v2/<strong>PayJpOptionPage.html</strong>"
		));
	}

	function setConfigObj($configObj){
		$this->configObj = $configObj;
	}
}
