<?php

class SignInConfigPage extends WebPage {

	private $cnfObj;

	function __construct(){
		SOY2::import("module.plugins.google_sign_in.util.GoogleSignInUtil");
	}

	function doPost(){
		if(soy2_check_token()){
			GoogleSignInUtil::saveConfig($_POST["Config"]);
			GoogleSignInUtil::saveButtonHTML($_POST["Template"]);
			$this->configObj->redirect("updated");
		}
	}

	function execute(){
		parent::__construct();

		$cnf = GoogleSignInUtil::getConfig();

		if(isset($_GET["return"])){
			GoogleSignInUtil::returnButtonHtml();	//テンプレートを標準のものに戻す
			$cnf["render_function"] = "";
			GoogleSignInUtil::saveConfig($cnf);
		}

		if(isset($_GET["sample"])){
			GoogleSignInUtil::setSampleButtonHtml();
			$cnf["render_function"] = "renderButton";
			GoogleSignInUtil::saveConfig($cnf);
		}

		$this->addForm("form");

		$this->addInput("client_id", array(
			"name" => "Config[client_id]",
			"value" => (isset($cnf["client_id"])) ? $cnf["client_id"] : ""
		));

		$this->addCheckBox("pre_register_mode", array(
			"name" => "Config[pre_register_mode]",
			"value" => 1,
			"selected" => (isset($cnf["pre_register_mode"]) && $cnf["pre_register_mode"] == 1),
			"label" => "仮登録モード"
		));

		$this->addLink("return_link", array(
			"link" => SOY2PageController::createLink("Config.Detail") . "?plugin=google_sign_in&return"
		));

		$this->addLink("sample_link", array(
			"link" => SOY2PageController::createLink("Config.Detail") . "?plugin=google_sign_in&sample"
		));

		$this->addTextArea("button_html", array(
			"name" => "Template",
			"value" => GoogleSignInUtil::getButtonHTML(),
			"style" => "height:200px;"
		));

		$this->addInput("render_function", array(
			"name" => "Config[render_function]",
			"value" => (isset($cnf["render_function"])) ? $cnf["render_function"] : ""
		));

		$this->addLabel("create_js_url_sample", array(
			"text" => str_replace("/" . SOYSHOP_ID . "/", "", soyshop_get_site_url(true))
		));

		//説明用
		$this->addLabel("redirect_url_sample", array(
			"text" => soyshop_get_mypage_url(true)
		));

		$this->addLink("mypage_login_link", array(
			"text" => soyshop_get_mypage_url(true) . "/login",
			"link" => soyshop_get_mypage_url(true) . "/login"
		));
	}

	function setConfigObj($cnfObj){
		$this->configObj = $cnfObj;
	}
}
