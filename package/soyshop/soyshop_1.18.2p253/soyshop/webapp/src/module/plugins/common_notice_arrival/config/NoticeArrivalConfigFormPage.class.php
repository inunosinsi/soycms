<?php

class NoticeArrivalConfigFormPage extends WebPage{

	private $configObj;

	function __construct(){
		SOY2::import("module.plugins.common_notice_arrival.util.NoticeArrivalUtil");
	}

	function doPost(){

		if(soy2_check_token()){

			NoticeArrivalUtil::saveConfig($_POST["Config"]);

			if(isset($_POST["Mail"])){
				NoticeArrivalUtil::saveMailTitle($_POST["Mail"]["title"]);
				NoticeArrivalUtil::saveMailContent($_POST["Mail"]["content"]);
			}

			$this->configObj->redirect("updated");
		}

		$this->configObj->redirect("error");
	}

	function execute(){
		parent::__construct();

		$this->addForm("form");

		$config = NoticeArrivalUtil::getConfig();
		$this->addCheckBox("send_mail", array(
			"name" => "Config[send_mail]",
			"value" => 1,
			"selected" => (isset($config["send_mail"]) && $config["send_mail"]),
			"label" => "お客様が入荷通知を希望したタイミングで下記のメールを送信する"
		));

		$this->addInput("mail_title", array(
			"name" => "Mail[title]",
			"value" => NoticeArrivalUtil::getMailTitle()
		));

		$this->addTextArea("mail_content", array(
			"name" => "Mail[content]",
			"value" => NoticeArrivalUtil::getMailContent()
		));
	}

	function setConfigObj($configObj){
		$this->configObj = $configObj;
	}
}
