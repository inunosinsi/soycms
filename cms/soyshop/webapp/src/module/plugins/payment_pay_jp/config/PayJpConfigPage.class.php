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
