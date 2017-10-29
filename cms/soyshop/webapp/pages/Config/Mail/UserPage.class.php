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
			$mail["output"] = (isset($mail["output"])) ? 1 : 0;
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
		parent::__construct();

		$this->addForm("form");

		$type = (isset($_GET["type"])) ? $_GET["type"] : "order";
		$this->buildForm($type);

		SOYShopPlugin::load("soyshop.mail.config");
		$delegate = SOYShopPlugin::invoke("soyshop.mail.config",array(
			"mode" => "edit",
			"target" => "user",
			"type" => $type
		));
		$this->addLabel("mail_config_extension_html", array(
			"html" => $delegate->getHtml()
		));

		//置換文字列の拡張
		$this->createAdd("replace_string_list", "_common.Config.ReplaceStringListComponent", array(
			"list" => self::getReplaceStringList()
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
			"selected" => $this->getMailActive(),
			"label" => "送信する",
		));

		$this->addCheckBox("mail_active_no", array(
			"name" => "mail[active]",
			"value" => "0",
			"selected" => ! $this->getMailActive(),
			"label" => "送信しない",
		));

		//メール本文の出力の有無
		$this->addCheckBox("is_mail_content_output", array(
			"name" => "mail[output]",
			"value" => 1,
			"selected" => $this->getMailOutput(),
			"label" => "システムから出力される注文詳細等のメール本文をヘッダーとフッター間に挿入する"
		));
	}

	private function getReplaceStringList(){
		SOYShopPlugin::load("soyshop.order.mail.replace");
		$values = SOYShopPlugin::invoke("soyshop.order.mail.replace",array("mode" => "strings"))->getStrings();
		if(!count($values)) return array();

		$list = array();
		foreach($values as $strings){
			if(!is_array($strings) || !count($strings)) continue;
			foreach($strings as $replace => $v){
				if(!strlen($replace) || !strlen($v)) continue;
				$list[$replace] = $v;
			}
		}

		return $list;
	}

	function getMailActive(){
		return $this->mail["active"];
	}

	function getMailOutput(){
		return (isset($this->mail["output"])) ? $this->mail["output"] : 0;
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

		if(isset($array[$type])) return $array[$type];

		//プラグインから出力したものを調べる
		SOY2::import("util.SOYShopPluginUtil");
		if(!SOYShopPluginUtil::checkIsActive("common_add_mail_type")) return "注文受付メール設定(自動送信)";

		SOY2::import("module.plugins.common_add_mail_type.util.AddMailTypeUtil");
		$configs = AddMailTypeUtil::getConfig();

		return (isset($configs[$type])) ? $configs[$type]["title"] . "雛形設定" : "注文受付メール設定(自動送信)";
	}
}
