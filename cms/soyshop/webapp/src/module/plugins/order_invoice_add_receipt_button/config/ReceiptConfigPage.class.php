<?php

class ReceiptConfigPage extends WebPage {

	private $configObj;

	function __construct(){
		SOY2::import("module.plugins.order_invoice_add_receipt_button.util.ReceiptUtil");
	}

	function doPost(){
		if(soy2_check_token()){
			$cnf = ReceiptUtil::getConfig();
			$cnf["mypage"] = (isset($_POST["Config"]["mypage"])) ? (int)$_POST["Config"]["mypage"] : 0;
			ReceiptUtil::saveConfig($cnf);

			$this->configObj->redirect("updated");
		}
	}

	function execute(){
		parent::__construct();

		$this->addForm("form");

		$this->addCheckBox("mypage", array(
			"name" => "Config[mypage]",
			"value" => 1,
			"selected" => ReceiptUtil::isMyPageSetting(),
			"label" => "マイページに領収書(HTML)の出力ボタンを設置する(β版)"
		));
	}

	function setConfigObj($configObj){
		$this->configObj = $configObj;
	}
}