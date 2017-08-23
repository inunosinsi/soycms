<?php

class IndexPage extends CMSWebPageBase{
	
	private $logic;// logic.site.mail
	
	function doPost() {
    	if(soy2_check_token()){
			
			//テスト送信
			if(isset($_POST["test"])){
				try{
					$this->testSend($_POST["test_mail_address"]);
					$this->addMessage("TESTMAIL_SEND_SUCCESS");
					$this->jump("MailConfig");

				}catch(Exception $e){
					$this->addErrorMessage("TESTMAIL_SEND_FAILED");
					$this->jump("MailConfig");

				}
				
			}
			
			//設定フォームの更新
			if(isset($_POST["update"])){
				try{
					$serverConfig = SOY2::cast("SOY2Mail_ServerConfig",(object)$_POST);
					$this->logic->save($serverConfig);

					$this->addMessage("MAILCONFIG_UPDATE_SUCCESS");
					$this->jump("MailConfig");
					
				}catch(Exception $e){
					$this->addErrorMessage("MAILCONFIG_UPDATE_FAILED");
					$this->jump("MailConfig");
					
				}
			}
			
    	}		
	}
	
	function __construct(){
		$this->logic = SOY2LogicContainer::get("logic.site.MailConfig.MailConfigLogic");
		
		parent::__construct();
		
		$this->buildForm();
		$this->buildTestForm();
	}
	
	/**
	 * 設定フォームの生成
	 */
	function buildForm(){
		$this->addForm("config_form");
		
		$serverConfig = $this->logic->get();
		
		
		/* SMTP設定 */
		
		
		//送信方法 sendmail (PHPのmail関数) 
		$this->createAdd("send_server_type_sendmail","HTMLCheckBox",array(
			"elementId" => "send_server_type_sendmail",
			"name" => "sendServerType",
			"value" => SOY2Mail_ServerConfig::SERVER_TYPE_SENDMAIL,
			"selected" => ($serverConfig->getSendServerType() == SOY2Mail_ServerConfig::SERVER_TYPE_SENDMAIL),
			"onclick" => 'toggleSMTP()'
		));
		
		//送信方法 SMTP
		$this->createAdd("send_server_type_smtp","HTMLCheckBox",array(
			"elementId" => "send_server_type_smtp",
			"name" => "sendServerType",
			"value" => SOY2Mail_ServerConfig::SERVER_TYPE_SMTP,
			"selected" => ($serverConfig->getSendServerType() == SOY2Mail_ServerConfig::SERVER_TYPE_SMTP),
			"onclick" => 'toggleSMTP()'
		));

		//SMTP サーバ
		$this->createAdd("send_server_address","HTMLInput",array(
			"id" => "send_server_address",
			"name" => "sendServerAddress",
			"value" => $serverConfig->getSendServerAddress(),
			"disabled" => ($serverConfig->getSendServerType() != SOY2Mail_ServerConfig::SERVER_TYPE_SMTP),
		));

		//SMPT ポート
		$this->createAdd("send_server_port","HTMLInput",array(
			"id" => "send_server_port",
			"name" => "sendServerPort",
			"value" => $serverConfig->getSendServerPort(),
			"disabled" => ($serverConfig->getSendServerType() != SOY2Mail_ServerConfig::SERVER_TYPE_SMTP),
		));

		//SMTP SSL(暗号化)を使用する
		$this->createAdd("is_use_ssl_send_server","HTMLCheckBox",array(
			"elementId" => "is_use_ssl_send_server",
			"name" => "isUseSSLSendServer",
			"value" => 1,
			"selected" => $this->isSSLEnabled() && $serverConfig->getIsUseSSLSendServer(),
			"disabled" => !$this->isSSLEnabled() OR ($serverConfig->getSendServerType() != SOY2Mail_ServerConfig::SERVER_TYPE_SMTP),
			"onclick" => 'changeSendPort();'
		));

		//認証 SMTP認証(SMTP-AUTH)を使用する
		$this->createAdd("is_use_smtp_auth","HTMLCheckBox",array(
			"elementId" => "is_use_smtp_auth",
			"name" => "isUseSMTPAuth",
			"value" => 1,
			"selected" => $serverConfig->getIsUseSMTPAuth(),
			"disabled" => ($serverConfig->getSendServerType() != SOY2Mail_ServerConfig::SERVER_TYPE_SMTP),
			"onclick" => 'toggleSMTPAUTHSetting();'
		));

		//認証 ユーザ名
		$this->createAdd("send_server_user","HTMLInput",array(
			"id" => "send_server_user",
			"name" => "sendServerUser",
			"value" => $serverConfig->getSendServerUser(),
			"disabled" => ($serverConfig->getSendServerType() != SOY2Mail_ServerConfig::SERVER_TYPE_SMTP) OR !$serverConfig->getIsUseSMTPAuth(),
		));

		//認証 パスワード
		$this->createAdd("send_server_password","HTMLInput",array(
			"id" => "send_server_password",
			"name" => "sendServerPassword",
			"value" => $serverConfig->getSendServerPassword(),
			"disabled" => ($serverConfig->getSendServerType() != SOY2Mail_ServerConfig::SERVER_TYPE_SMTP) OR !$serverConfig->getIsUseSMTPAuth(),
		));

		//認証 「POP/IMAP before SMTP」を使用する
		$this->createAdd("is_use_pop_before_smtp","HTMLCheckBox",array(
			"elementId" => "is_use_pop_before_smtp",
			"name" => "isUsePopBeforeSMTP",
			"value" => 1,
			"selected" => $serverConfig->getIsUsePopBeforeSMTP(),
			"disabled" => ($serverConfig->getSendServerType() != SOY2Mail_ServerConfig::SERVER_TYPE_SMTP),
			"onclick" => 'togglePOPIMAPSetting();'
		));


		/* 送信者設定 */
		
		//送信元アドレス（From）
		$this->createAdd("from_address","HTMLInput",array(
			"name" => "fromMailAddress",
			"value" => $serverConfig->getFromMailAddress()
		));
		
		//送信元名称
		$this->createAdd("from_name","HTMLInput",array(
			"name" => "fromMailAddressName",
			"value" => $serverConfig->getFromMailAddressName()
		));
		
		//返信先メールアドレス（Reply-To）
		$this->createAdd("return_address","HTMLInput",array(
			"name" => "returnMailAddress",
			"value" => $serverConfig->getReturnMailAddress()
		));
		
		//返信先名称
		$this->createAdd("return_name","HTMLInput",array(
			"name" => "returnMailAddressName",
			"value" => $serverConfig->getReturnMailAddressName()
		));

		/* 文字コード設定 */
		$this->createAdd("encoding_select","HTMLSelect",array(
			"name" => "encoding",
			"options" => array("UTF-8","ISO-2022-JP"),
			"selected" => $serverConfig->getEncoding()
		));
		
		
	}
	
	/**
	 * テストメールフォームの生成
	 */
	function buildTestForm(){
		$this->addForm("test_form");

		$this->addInput("test_mail_address",array(
			"name" => "test_mail_address",
		));

	}
	
	/**
	 * テストメールの送信
	 * @param string $to テストメールの送信先
	 */
	function testSend($to){
		$title = "SOY CMS テストメール ".date("Y-m-d H:i:s");
		$content = "これはSOY CMSから送信したテストメールです。";

		$logic = SOY2LogicContainer::get("logic.site.MailConfig.MailLogic");

		$logic->sendTestMail($to);
		
	}
	
	
	
	/**
	 * SSLが使用可能かを返す
	 * @return Boolean
	 */
	function isSSLEnabled(){
		return function_exists("openssl_open");
	}

	/**
	 * IMAPが使用可能かを返す
	 */
	function isIMAPEnabled(){
		return function_exists("imap_open");
	}
	
	
}
?>