<?php

class MypagePage extends WebPage{

	function doPost(){

		$type = (isset($_GET["type"])) ? $_GET["type"] : "remind";

		if(!soy2_check_token()){
			SOY2PageController::jump("Config.Mail.Mypage?type=" . $type);
		}

		if(isset($_POST["mail"])){
			$mail = $_POST["mail"];

			$logic = SOY2Logic::createInstance("logic.mail.MailLogic");
			$logic->setMyPageMailConfig($mail,$type);
		}

		SOYShopPlugin::load("soyshop.mail.config");
		$delegate = SOYShopPlugin::invoke("soyshop.mail.config",array(
			"mode" => "update",
			"target" => "mypage",
			"type" => $type
		));

		SOY2PageController::jump("Config.Mail.Mypage?type=" . $type . "&updated");
	}

	function __construct(){
		parent::__construct();

		$this->addLabel("user_label", array("text" => SHOP_USER_LABEL));

		//メール文面の初期化
		if(isset($_GET["init"]))	self::_initText();

		$type = (isset($_GET["type"])) ? $_GET["type"] : "remind";
		$this->buildForm($type);

		$this->addForm("form");

		SOYShopPlugin::load("soyshop.mail.config");
		$delegate = SOYShopPlugin::invoke("soyshop.mail.config",array(
			"mode" => "edit",
			"target" => "mypage",
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

		$this->mail = SOY2Logic::createInstance("logic.mail.MailLogic")->getMyPageMailConfig($type);

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
		//今のところ使わないので非表示
		$this->addModel("mail_active_config", array(
			"visible" => false,
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

	/**
	 * 仮登録メール　文面の追加
	 */
	private function _initText(){

		preg_match('/type=(.*)/', $_SERVER["HTTP_REFERER"], $types);
    	if(strpos($types[1], "&") != false){
    		$type = substr($types[1], "0", strpos($types[1], "&"));
    	}else{
    		$type = $types[1];
    	}

		$mail = array(
    		"title" => "[#SHOP_NAME#]" . $this->getMailText($type),
	    		"header" => file_get_contents(SOY2::RootDir() . "logic/init/mail/mypage/" . $type . "/header.txt"),
	    		"footer" => file_get_contents(SOY2::RootDir() . "logic/init/mail/mypage/" . $type . "/footer.txt")
    	);

		SOYShop_DataSets::put("mail.mypage." . $type . ".title", $mail["title"]);
		SOYShop_DataSets::put("mail.mypage." . $type . ".header", $mail["header"]);
    	SOYShop_DataSets::put("mail.mypage." . $type . ".footer", $mail["footer"]);
    	SOY2PageController::jump("Config.Mail.Mypage?type=" . $type . "&updated");
	}


	function getMailText($type){

		$array = array(
			"tmp_register" => "仮登録メール",
			"register" => "登録完了メール",
			"remind" => "パスワード再設定メール",
			"edit" => SHOP_USER_LABEL . "情報の変更の確認メール"
		);

		return (isset($array[$type])) ? $array[$type] : "パスワード再設定メール";
	}

	function getBreadcrumb(){
		$type = (isset($_GET["type"])) ? $_GET["type"] : "order";
		return BreadcrumbComponent::build($this->getMailText($type), array("Config" => "設定", "Config.Mail" => "メール設定"));
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
