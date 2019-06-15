<?php

class MailPage extends WebPage{

	var $errorMessage = "";

	private $logic;

	function doPost(){

		if(!soy2_check_token()){
			SOY2PageController::jump("Administrator.Mail");
		}

		//テスト送信
		if(isset($_POST["test_mail_address"])){
			try{
				$this->testSend($_POST["test_mail_address"]);
				SOY2PageController::jump("Administrator.Mail?sended");
			}catch(Exception $e){
				SOY2PageController::jump("Administrator.Mail?failed_to_send");
			}

		//設定更新
		}else{
			try{
				$serverConfig = SOY2::cast("SOY2Mail_ServerConfig",(object)$_POST);
				$this->logic->save($serverConfig);
				SOY2PageController::jump("Administrator.Mail?updated");
			}catch(Exception $e){
				SOY2PageController::jump("Administrator.Mail?failed");
			}
		}

	}

	function __construct(){
		//初期管理者のみ
		if(!UserInfoUtil::isDefaultUser()){
			SOY2PageController::jump("");
		}

		$this->logic = SOY2LogicContainer::get("logic.mail.MailConfigLogic");

		parent::__construct();

		$this->buildForm();
		$this->buildTestSendForm();

		$this->addLabel("error_message", array(
			"text" => $this->errorMessage,
			"visible" => (strlen($this->errorMessage)>0)
		));

		DisplayPlugin::toggle("updated", isset($_GET["updated"]));
		DisplayPlugin::toggle("failed", isset($_GET["failed"]));
		DisplayPlugin::toggle("sended", isset($_GET["sended"]));
		DisplayPlugin::toggle("failed_to_send", isset($_GET["failed_to_send"]));

		$serverConfig = $this->logic->get();
		$hasMailConfig = strlen($serverConfig->getFromMailAddress()) > 0;
		$hasMailAddress = SOY2Logic::createInstance("logic.admin.Administrator.AdministratorLogic")->hasMailaddress();

		DisplayPlugin::toggle("no_mail_config", !$hasMailConfig);
		DisplayPlugin::toggle("no_mail_address", !$hasMailAddress);
		DisplayPlugin::toggle("valid", $hasMailConfig && $hasMailAddress);

	}

	function buildForm(){

		$this->addForm("form");

		$serverConfig = $this->logic->get();

		$this->addCheckBox("send_server_type_sendmail", array(
			"elementId" => "send_server_type_sendmail",
			"name" => "sendServerType",
			"value" => SOY2Mail_ServerConfig::SERVER_TYPE_SENDMAIL,
			"selected" => ($serverConfig->getSendServerType() == SOY2Mail_ServerConfig::SERVER_TYPE_SENDMAIL),
			"onclick" => 'toggleSMTP()'
		));
		$this->addCheckBox("send_server_type_smtp", array(
			"elementId" => "send_server_type_smtp",
			"name" => "sendServerType",
			"value" => SOY2Mail_ServerConfig::SERVER_TYPE_SMTP,
			"selected" => ($serverConfig->getSendServerType() == SOY2Mail_ServerConfig::SERVER_TYPE_SMTP),
			"onclick" => 'toggleSMTP()'
		));


		$this->addCheckBox("is_use_pop_before_smtp", array(
			"elementId" => "is_use_pop_before_smtp",
			"name" => "isUsePopBeforeSMTP",
			"value" => 1,
			"selected" => $serverConfig->getIsUsePopBeforeSMTP(),
			"disabled" => ($serverConfig->getSendServerType() != SOY2Mail_ServerConfig::SERVER_TYPE_SMTP),
			"onclick" => 'togglePOPIMAPSetting();'
		));

		$this->addCheckBox("is_use_smtp_auth", array(
			"elementId" => "is_use_smtp_auth",
			"name" => "isUseSMTPAuth",
			"value" => 1,
			"selected" => $serverConfig->getIsUseSMTPAuth(),
			"disabled" => ($serverConfig->getSendServerType() != SOY2Mail_ServerConfig::SERVER_TYPE_SMTP),
			"onclick" => 'toggleSMTPAUTHSetting();'
		));

		$this->addInput("send_server_address", array(
			"id" => "send_server_address",
			"name" => "sendServerAddress",
			"value" => $serverConfig->getSendServerAddress(),
			"disabled" => ($serverConfig->getSendServerType() != SOY2Mail_ServerConfig::SERVER_TYPE_SMTP),
		));
		$this->addInput("send_server_port", array(
			"id" => "send_server_port",
			"name" => "sendServerPort",
			"value" => $serverConfig->getSendServerPort(),
			"disabled" => ($serverConfig->getSendServerType() != SOY2Mail_ServerConfig::SERVER_TYPE_SMTP),
		));

		$this->addInput("send_server_user", array(
			"id" => "send_server_user",
			"name" => "sendServerUser",
			"value" => $serverConfig->getSendServerUser(),
			"disabled" => ($serverConfig->getSendServerType() != SOY2Mail_ServerConfig::SERVER_TYPE_SMTP) || !$serverConfig->getIsUseSMTPAuth(),
		));
		$this->addInput("send_server_password", array(
			"id" => "send_server_password",
			"name" => "sendServerPassword",
			"value" => $serverConfig->getSendServerPassword(),
			"disabled" => ($serverConfig->getSendServerType() != SOY2Mail_ServerConfig::SERVER_TYPE_SMTP) || !$serverConfig->getIsUseSMTPAuth(),
		));

		$this->addCheckBox("is_use_ssl_send_server", array(
			"elementId" => "is_use_ssl_send_server",
			"name" => "isUseSSLSendServer",
			"value" => 1,
			"selected" => $this->isSSLEnabled() && $serverConfig->getIsUseSSLSendServer(),
			"disabled" => !$this->isSSLEnabled() || ($serverConfig->getSendServerType() != SOY2Mail_ServerConfig::SERVER_TYPE_SMTP),
			"onclick" => 'changeSendPort();'
		));

		/* 受信設定 */
		$this->addCheckBox("receive_server_type_pop", array(
			"elementId" => "receive_server_type_pop",
			"name" => "receiveServerType",
			"value" => SOY2Mail_ServerConfig::RECEIVE_SERVER_TYPE_POP,
			"selected" => ($serverConfig->getReceiveServerType() == SOY2Mail_ServerConfig::RECEIVE_SERVER_TYPE_POP),
			"disabled" => ($serverConfig->getSendServerType() != SOY2Mail_ServerConfig::SERVER_TYPE_SMTP) || !$serverConfig->getIsUsePopBeforeSMTP(),
			"onclick" => 'changeReceivePort();'
		));

		$this->addCheckBox("receive_server_type_imap", array(
			"elementId" => "receive_server_type_imap",
			"name" => "receiveServerType",
			"value" => SOY2Mail_ServerConfig::RECEIVE_SERVER_TYPE_IMAP,
			"selected" => ($serverConfig->getReceiveServerType() == SOY2Mail_ServerConfig::RECEIVE_SERVER_TYPE_IMAP),
			"disabled" => ($serverConfig->getSendServerType() != SOY2Mail_ServerConfig::SERVER_TYPE_SMTP) || !$serverConfig->getIsUsePopBeforeSMTP() OR !$this->isIMAPEnabled(),
			"onclick" => 'changeReceivePort();'
		));

		$this->addInput("receive_server_address", array(
			"id" => "receive_server_address",
			"name" => "receiveServerAddress",
			"value" => $serverConfig->getReceiveServerAddress(),
			"disabled" => ($serverConfig->getSendServerType() != SOY2Mail_ServerConfig::SERVER_TYPE_SMTP) || !$serverConfig->getIsUsePopBeforeSMTP(),
		));

		$this->addInput("receive_server_user", array(
			"id" => "receive_server_user",
			"name" => "receiveServerUser",
			"value" => $serverConfig->getReceiveServerUser(),
			"disabled" => ($serverConfig->getSendServerType() != SOY2Mail_ServerConfig::SERVER_TYPE_SMTP) || !$serverConfig->getIsUsePopBeforeSMTP(),
		));

		$this->addInput("receive_server_password", array(
			"id" => "receive_server_password",
			"name" => "receiveServerPassword",
			"value" => $serverConfig->getReceiveServerPassword(),
			"disabled" => ($serverConfig->getSendServerType() != SOY2Mail_ServerConfig::SERVER_TYPE_SMTP) || !$serverConfig->getIsUsePopBeforeSMTP(),
		));

		$this->addInput("receive_server_port", array(
			"id" => "receive_server_port",
			"name" => "receiveServerPort",
			"value" => $serverConfig->getReceiveServerPort(),
			"disabled" => ($serverConfig->getSendServerType() != SOY2Mail_ServerConfig::SERVER_TYPE_SMTP) || !$serverConfig->getIsUsePopBeforeSMTP(),
		));

		$this->addCheckBox("is_use_ssl_receive_server", array(
			"elementId" => "is_use_ssl_receive_server",
			"name" => "isUseSSLReceiveServer",
			"value" => 1,
			"selected" => $this->isSSLEnabled() && $serverConfig->getIsUseSSLReceiveServer(),
			"disabled" => !$this->isSSLEnabled() || ($serverConfig->getSendServerType() != SOY2Mail_ServerConfig::SERVER_TYPE_SMTP),
			"onclick" => 'changeReceivePort();'
		));

		/* SSL */
		$this->createAdd("is_ssl_enabled", "HTMLHidden", array(
			"id"    => "is_ssl_enabled",
			"value" => (int) $this->isSSLEnabled()
		));
		$this->addModel("ssl_disabled", array(
			"visible" => !$this->isSSLEnabled()
		));
		/* IMAP */
		$this->createAdd("is_imap_enabled", "HTMLHidden", array(
			"id"    => "is_imap_enabled",
			"value" => (int) $this->isIMAPEnabled()
		));
		$this->addModel("imap_disabled", array(
			"visible" => !$this->isIMAPEnabled()
		));

		/* 送信者設定 */
		$this->addInput("from_address", array(
			"name" => "fromMailAddress",
			"value" => $serverConfig->getFromMailAddress()
		));
		$this->addInput("from_name", array(
			"name" => "fromMailAddressName",
			"value" => $serverConfig->getFromMailAddressName()
		));
		$this->addInput("return_address", array(
			"name" => "returnMailAddress",
			"value" => $serverConfig->getReturnMailAddress()
		));

		$this->addInput("return_name", array(
			"name" => "returnMailAddressName",
			"value" => $serverConfig->getReturnMailAddressName()
		));

		/* 文字コード設定 */
		$this->addSelect("encoding_select", array(
			"name" => "encoding",
			"options" => array("UTF-8","ISO-2022-JP"),
			"selected" => $serverConfig->getEncoding()
		));
	}

	function buildTestSendForm(){
		$this->addForm("test_form");

		$this->addLabel("test_mail_address", array(
			"name" => "test_mail_address",
			"value" => "",
		));
	}

	function testSend($to){

		$title = CMSUtil::getCMSName() . " テストメール " . date("Y-m-d H:i:s");
		$content = "これは" . CMSUtil::getCMSName() . "から送信したテストメールです。";
		
		$logic = SOY2LogicContainer::get("logic.mail.MailLogic");

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
