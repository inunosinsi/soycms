<?php

class NoticeArrivalConfigFormPage extends WebPage{

	private $configObj;

	function __construct(){
		SOY2::import("module.plugins.common_notice_arrival.util.NoticeArrivalUtil");
	}

	function doPost(){
		if(soy2_check_token()){
			NoticeArrivalUtil::saveConfig($_POST["Config"]);
			$this->configObj->redirect("updated");
		}

		$this->configObj->redirect("error");
	}

	function execute(){
		parent::__construct();

		$this->addForm("form");

		$cnf = NoticeArrivalUtil::getConfig();
		$this->addCheckBox("send_mail", array(
			"name" => "Config[send_mail]",
			"value" => 1,
			"selected" => (isset($cnf["send_mail"]) && $cnf["send_mail"]),
			"label" => "お客様が入荷通知を希望したタイミングでメールを送信する"
		));
	}

	function setConfigObj($configObj){
		$this->configObj = $configObj;
	}
}
