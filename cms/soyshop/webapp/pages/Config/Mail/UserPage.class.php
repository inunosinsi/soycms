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
			$mail["plugin"] = (isset($mail["plugin"])) ? 1 : 0;
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
		self::_buildForm($type);

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
			"list" => self::_getReplaceStringList()
		));
	}

	private function _buildForm($type){

		$this->addLabel("mail_text", array(
			"text" => self::_getMailText($type)
		));

		$this->mail = SOY2Logic::createInstance("logic.mail.MailLogic")->getUserMailConfig($type);

		$this->addInput("mail_title", array(
			"name" => "mail[title]",
			"value" => self::_getMailTitle(),
		));

		$this->addTextArea("mail_header", array(
			"name" => "mail[header]",
			"value" => self::_getHeader(),
		));

		$this->addTextArea("mail_footer", array(
			"name" => "mail[footer]",
			"value" => self::_getFooter(),
		));

		/* 送信設定 */
		DisplayPlugin::toggle("mail_active_config", self::_isActive($type));

		$this->addCheckBox("mail_active_yes", array(
			"name" => "mail[active]",
			"value" => "1",
			"selected" => self::_getMailActive(),
			"label" => "送信する",
		));

		$this->addCheckBox("mail_active_no", array(
			"name" => "mail[active]",
			"value" => "0",
			"selected" => ! self::_getMailActive(),
			"label" => "送信しない",
		));

		//メール本文の出力の有無
		DisplayPlugin::toggle("system_mail_active_config", self::_isSystemMailActive($type));

		$this->addCheckBox("is_mail_content_output", array(
			"name" => "mail[output]",
			"value" => 1,
			"selected" => self::_getMailOutput(),
			"label" => "システム(購入状況等)から出力される注文詳細等のメール本文をヘッダーとフッター間に挿入する"
		));

		$this->addCheckBox("is_mail_content_plugin", array(
			"name" => "mail[plugin]",
			"value" => 1,
			"selected" => self::_getMailPlugin(),
			"label" => "プラグイン(配送方法等)から出力される注文詳細等のメール本文をヘッダーとフッター間に挿入する"
		));
	}

	//自動送信設定の有無
	private function _isActive($type){
		//注文受付メールと支払い確認メールのみ
		if(in_array($type, array("order","payment", "delivery"))) return true;

		//拡張ポイントで追加したメール設定の方でも確認する
		SOYShopPlugin::load("soyshop.order.detail.mail");
		$keys = SOYShopPlugin::invoke("soyshop.order.detail.mail", array("mode" => "key"))->getList();
		if(!count($keys)) return false;

		foreach($keys as $key){
			if($type == $key) return true;
		}

		return false;
	}

	private function _isSystemMailActive($type){
		//顧客(アカウント)宛メールのみfalse
		if(in_array($type, array("user"))) return false;

		//拡張ポイントからも調べる
		SOY2::import("module.plugins.common_add_mail_type.util.AddMailTypeUtil");
		$configs = AddMailTypeUtil::getConfig(AddMailTypeUtil::MAIL_TYPE_USER);
		$keys = array_keys($configs);
		if(in_array($type, $keys)) return false;

		return true;
	}

	private function _getReplaceStringList(){
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

	private function _getMailActive(){
		return $this->mail["active"];
	}

	private function _getMailOutput(){
		return (isset($this->mail["output"])) ? $this->mail["output"] : 0;
	}

	private function _getMailPlugin(){
		return (isset($this->mail["plugin"])) ? $this->mail["plugin"] : 0;
	}

	private function _getMailTitle(){
		return $this->mail["title"];
	}

	private function _getHeader(){
		return $this->mail["header"];
	}

	private function _getFooter(){
		return $this->mail["footer"];
	}

	private function _getMailText($type){
		$array = array(
			"confirm" => "注文確認メール雛型設定",
			"payment" => "支払確認メール雛型設定",
			"delivery" => "配送連絡メール雛型設定",
			"other" => "その他のメール雛形設定",
			"user" => SHOP_USER_LABEL . "宛メール雛形設定"
		);

		if(isset($array[$type])) return $array[$type];

		//プラグインから出力したものを調べる
		SOY2::import("util.SOYShopPluginUtil");
		if(!SOYShopPluginUtil::checkIsActive("common_add_mail_type")) return "注文受付メール設定(自動送信)";

		SOY2::import("module.plugins.common_add_mail_type.util.AddMailTypeUtil");
		$configs = AddMailTypeUtil::getConfig(AddMailTypeUtil::MAIL_TYPE_ORDER);

		if(isset($configs[$type])) return $configs[$type]["title"] . "雛形設定";

		$configs = AddMailTypeUtil::getConfig(AddMailTypeUtil::MAIL_TYPE_USER);
		return (isset($configs[$type])) ? $configs[$type]["title"] . "雛形設定" : "注文受付メール設定(自動送信)";
	}

	function getBreadcrumb(){
		$type = (isset($_GET["type"])) ? $_GET["type"] : "order";
		return BreadcrumbComponent::build(self::_getMailText($type), array("Config" => "設定", "Config.Mail" => "メール設定"));
	}

	function getFooterMenu(){
		try{
			return SOY2HTMLFactory::createInstance("Config.FooterMenu.MailTemplateFooterMenuPage")->getObject();
		}catch(Exception $e){
			//
			return null;
		}
	}

	function getScripts(){
		$root = SOY2PageController::createRelativeLink("./js/");
		return array(
			$root . "main.pack.js",
		);
	}
}
