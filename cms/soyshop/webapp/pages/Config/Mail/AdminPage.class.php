<?php

/**
 * @class Config.MailConfigPage
 * @date 2009-07-29T19:10:03+09:00
 * @author SOY2HTMLFactory
 */
class AdminPage extends WebPage{

	function doPost(){
		//管理者向けメール設定のタイプ
		$type = (isset($_GET["type"])) ? $_GET["type"] : "order";

		if(!soy2_check_token()){
			SOY2PageController::jump("Config.Mail.Admin?type=" . $type);
		}


		if(isset($_POST["mail"])){
			$mail = $_POST["mail"];

			$logic = SOY2Logic::createInstance("logic.mail.MailLogic");
			$logic->setAdminMailConfig($mail,$type);

		}
		
		SOYShopPlugin::load("soyshop.mail.config");
		$delegate = SOYShopPlugin::invoke("soyshop.mail.config",array(
			"mode" => "update",
			"target" => "admin",
			"type" => $type
		));
		
		SOY2PageController::jump("Config.Mail.Admin?updated&type=" . $type);
	}

	function __construct(){
		WebPage::__construct();

		$this->addForm("form");

		//管理者向けメール設定のタイプ
		$type = (isset($_GET["type"])) ? $_GET["type"] : "order";
		$this->buildForm($type);
		
		
		SOYShopPlugin::load("soyshop.mail.config");
		$delegate = SOYShopPlugin::invoke("soyshop.mail.config",array(
			"mode" => "edit",
			"target" => "admin",
			"type" => $type
		));
		$html = $delegate->getHtml();
		$this->addLabel("mail_config_extension_html", array(
			"html" => $html
		));
	}

	function buildForm($type){

		$this->addLabel("mail_text", array(
			"text" => $this->getMailText($type)
		));

		$this->mail = SOY2Logic::createInstance("logic.mail.MailLogic")->getAdminMailConfig($type);

		$this->addCheckBox("mail_active_yes", array(
			"name" => "mail[active]",
			"value" => "1",
			"selected" => $this->getMailActive($type),
			"label" => "送信する",
		));

		$this->addCheckBox("mail_active_no", array(
			"name" => "mail[active]",
			"value" => "0",
			"selected" => ! $this->getMailActive($type),
			"label" => "送信しない",
		));

		$this->addInput("mail_title", array(
			"name" => "mail[title]",
			"value" => $this->getMailTitle(),
		));

		$this->addTextArea("mail_header", array(
			"name" => "mail[header]",
			"value" => $this->getHeader(),
		));

		$this->addTextArea("mail_footer", array(
			"name" => "mail[footer]",
			"value" => $this->getFooter(),
		));
	}

	function getMailActive(){
		return $this->mail["active"];
	}

	function getMailTitle(){
		return $this->mail["title"];
	}

	function getHeader(){
		return $this->mail["header"];
	}

	function getFooter(){
		return $this->mail["footer"];
	}

	function getMailText($type){
		$array = array(
			"confirm"  => "注文確定メール雛型（管理者向け）",//未実装
			"payment"  => "支払通知メール雛型（管理者向け）",
			"delivery" => "配送連絡メール雛型（管理者向け）",//未実装
			"other"    => "その他のメール雛形（管理者向け）"  //未実装
		);

		return (isset($array[$type])) ? $array[$type] : "注文受付メール雛型（管理者向け）";
	}
}