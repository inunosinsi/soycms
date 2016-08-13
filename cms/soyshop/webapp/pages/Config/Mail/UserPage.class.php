<?php

/**
 * @class Config.MailConfigPage
 * @date 2009-07-29T19:10:03+09:00
 * @author SOY2HTMLFactory
 */
class UserPage extends WebPage{

	function doPost(){
		//ユーザー向けメール設定のタイプ
		$type = (isset($_GET["type"])) ? $_GET["type"] : "order";

		if(!soy2_check_token()){
			SOY2PageController::jump("Config.Mail.User?type=" . $type);
		}

		if(isset($_POST["mail"])){
			$mail = $_POST["mail"];
			$logic = SOY2Logic::createInstance("logic.mail.MailLogic");
			$logic->setUserMailConfig($mail, $type);
		}
		
		SOYShopPlugin::load("soyshop.mail.config");
		$delegate = SOYShopPlugin::invoke("soyshop.mail.config",array(
			"mode" => "update",
			"target" => "user",
			"type" => $type
		));
		
		SOY2PageController::jump("Config.Mail.User?updated&type=" . $type);
	}

	private $mail;

	function __construct(){
		WebPage::__construct();

		$this->addForm("form");

		$type = (isset($_GET["type"])) ? $_GET["type"] : "order";
		$this->buildForm($type);
		
		SOYShopPlugin::load("soyshop.mail.config");
		$delegate = SOYShopPlugin::invoke("soyshop.mail.config",array(
			"mode" => "edit",
			"target" => "user",
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

		$this->mail = SOY2Logic::createInstance("logic.mail.MailLogic")->getUserMailConfig($type);

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

		/* 送信設定 */
		//注文受付メールと支払い確認メールのみ
		$this->addModel("mail_active_config", array(
			"visible" => in_array($type, array("order","payment")),
		));

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
			"confirm" => "注文確認メール雛型設定",
			"payment" => "支払確認メール雛型設定",
			"delivery" => "配送連絡メール雛型設定",
			"other" => "その他のメール雛形設定"
		);

		return (isset($array[$type])) ? $array[$type] : "注文受付メール設定(自動送信)";
	}
}