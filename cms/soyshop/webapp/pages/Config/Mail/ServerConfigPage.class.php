<?php
SOY2::import("domain.config.SOYShop_ServerConfig");
class ServerConfigPage extends WebPage{

	var $errorMessage = "";

	function doPost(){
		if(!soy2_check_token()){
			SOY2PageController::jump("Config.Mail.ServerConfig");
		}

		if(isset($_POST["test_mail_address"])){
			try{
				self::_testSend($_POST["test_mail_address"]);
				SOY2PageController::jump("Config.Mail.ServerConfig?sended");
				exit;
			}catch(Exception $e){
				SOY2PageController::jump("Config.Mail.ServerConfig?failed_to_send");
				exit;
			}
		}else{
			try{
				$serverConfig = SOY2::cast("SOYShop_ServerConfig", (object)$_POST);
				SOYShop_ServerConfig::save($serverConfig);
				SOY2PageController::jump("Config.Mail.ServerConfig?updated");
				exit;
			}catch(Exception $e){
				SOY2PageController::jump("Config.Mail.ServerConfig?failed");
				exit;
			}
		}
	}

    function __construct() {
    	parent::__construct();

    	self::_buildForm();
    	self::_buildTestSendForm();

    	$this->addLabel("error_message", array(
    		"text" => $this->errorMessage,
    		"visible" => (strlen($this->errorMessage) > 0)
    	));

		DisplayPlugin::toggle("sended", isset($_GET["sended"]));
		DisplayPlugin::toggle("failed_to_send", isset($_GET["failed_to_send"]));
    }

    private function _buildForm(){

    	$this->addForm("form");

    	$serverConfig = SOYShop_ServerConfig::load();

    	$this->addCheckBox("send_server_type_sendmail", array(
    		"elementId" => "send_server_type_sendmail",
    		"name" => "sendServerType",
    		"value" => SOYShop_ServerConfig::SERVER_TYPE_SENDMAIL,
    		"selected" => ($serverConfig->getSendServerType() == SOYShop_ServerConfig::SERVER_TYPE_SENDMAIL),
    		"onclick" => 'toggleSMTP()'
    	));
    	$this->addCheckBox("send_server_type_smtp", array(
    		"elementId" => "send_server_type_smtp",
    		"name" => "sendServerType",
    		"value" => SOYShop_ServerConfig::SERVER_TYPE_SMTP,
    		"selected" => ($serverConfig->getSendServerType() == SOYShop_ServerConfig::SERVER_TYPE_SMTP),
    		"onclick" => 'toggleSMTP()'
    	));


    	$this->addCheckBox("is_use_pop_before_smtp", array(
			"elementId" => "is_use_pop_before_smtp",
			"name" => "isUsePopBeforeSMTP",
    		"value" => 1,
    		"selected" => $serverConfig->getIsUsePopBeforeSMTP(),
    		"disabled" => ($serverConfig->getSendServerType() != SOYShop_ServerConfig::SERVER_TYPE_SMTP),
    		"onclick" => 'togglePOPIMAPSetting();'
    	));

    	$this->addCheckBox("is_use_smtp_auth", array(
			"elementId" => "is_use_smtp_auth",
			"name" => "isUseSMTPAuth",
    		"value" => 1,
    		"selected" => $serverConfig->getIsUseSMTPAuth(),
    		"disabled" => ($serverConfig->getSendServerType() != SOYShop_ServerConfig::SERVER_TYPE_SMTP),
    		"onclick" => 'toggleSMTPAUTHSetting();'
    	));

    	$this->addInput("send_server_address", array(
			"id" => "send_server_address",
			"name" => "sendServerAddress",
    		"value" => $serverConfig->getSendServerAddress(),
    		"disabled" => ($serverConfig->getSendServerType() != SOYShop_ServerConfig::SERVER_TYPE_SMTP),
    	));
    	$this->addInput("send_server_port", array(
			"id" => "send_server_port",
			"name" => "sendServerPort",
    		"value" => $serverConfig->getSendServerPort(),
    		"disabled" => ($serverConfig->getSendServerType() != SOYShop_ServerConfig::SERVER_TYPE_SMTP),
    	));


    	$this->addInput("send_server_user", array(
			"id" => "send_server_user",
			"name" => "sendServerUser",
    		"value" => $serverConfig->getSendServerUser(),
    		"disabled" => ($serverConfig->getSendServerType() != SOYShop_ServerConfig::SERVER_TYPE_SMTP) || !$serverConfig->getIsUseSMTPAuth(),
    	));
    	$this->addInput("send_server_password", array(
			"id" => "send_server_password",
			"name" => "sendServerPassword",
    		"value" => $serverConfig->getSendServerPassword(),
    		"disabled" => ($serverConfig->getSendServerType() != SOYShop_ServerConfig::SERVER_TYPE_SMTP) || !$serverConfig->getIsUseSMTPAuth(),
    	));

    	$this->addCheckBox("is_use_ssl_send_server", array(
    		"elementId" => "is_use_ssl_send_server",
			"name" => "isUseSSLSendServer",
    		"value" => 1,
    		"selected" => self::_isSSLEnabled() && $serverConfig->getIsUseSSLSendServer(),
    		"disabled" => !self::_isSSLEnabled() || ($serverConfig->getSendServerType() != SOYShop_ServerConfig::SERVER_TYPE_SMTP),
    		"onclick" => 'changeSendPort();'
    	));

    	/* 受信設定 */
    	$this->addCheckBox("receive_server_type_pop", array(
    		"elementId" => "receive_server_type_pop",
			"name" => "receiveServerType",
    		"value" => SOYShop_ServerConfig::RECEIVE_SERVER_TYPE_POP,
    		"selected" => ($serverConfig->getReceiveServerType() == SOYShop_ServerConfig::RECEIVE_SERVER_TYPE_POP),
    		"disabled" => ($serverConfig->getSendServerType() != SOYShop_ServerConfig::SERVER_TYPE_SMTP) || !$serverConfig->getIsUsePopBeforeSMTP(),
    		"onclick" => 'changeReceivePort();'
    	));

    	$this->addCheckBOx("receive_server_type_imap", array(
    		"elementId" => "receive_server_type_imap",
    		"name" => "receiveServerType",
    		"value" => SOYShop_ServerConfig::RECEIVE_SERVER_TYPE_IMAP,
    		"selected" => ($serverConfig->getReceiveServerType() == SOYShop_ServerConfig::RECEIVE_SERVER_TYPE_IMAP),
    		"disabled" => ($serverConfig->getSendServerType() != SOYShop_ServerConfig::SERVER_TYPE_SMTP) || !$serverConfig->getIsUsePopBeforeSMTP() || !$this->isIMAPEnabled(),
    		"onclick" => 'changeReceivePort();'
    	));

    	$this->addCheckBox("receive_server_address", array(
			"id" => "receive_server_address",
			"name" => "receiveServerAddress",
    		"value" => $serverConfig->getReceiveServerAddress(),
    		"disabled" => ($serverConfig->getSendServerType() != SOYShop_ServerConfig::SERVER_TYPE_SMTP) || !$serverConfig->getIsUsePopBeforeSMTP(),
    	));

    	$this->addInput("receive_server_user", array(
			"id" => "receive_server_user",
			"name" => "receiveServerUser",
    		"value" => $serverConfig->getReceiveServerUser(),
    		"disabled" => ($serverConfig->getSendServerType() != SOYShop_ServerConfig::SERVER_TYPE_SMTP) || !$serverConfig->getIsUsePopBeforeSMTP(),
    	));

    	$this->addInput("receive_server_password", array(
			"id" => "receive_server_password",
			"name" => "receiveServerPassword",
    		"value" => $serverConfig->getReceiveServerPassword(),
    		"disabled" => ($serverConfig->getSendServerType() != SOYShop_ServerConfig::SERVER_TYPE_SMTP) || !$serverConfig->getIsUsePopBeforeSMTP(),
    	));

    	$this->addInput("receive_server_port", array(
			"id" => "receive_server_port",
			"name" => "receiveServerPort",
    		"value" => $serverConfig->getReceiveServerPort(),
    		"disabled" => ($serverConfig->getSendServerType() != SOYShop_ServerConfig::SERVER_TYPE_SMTP) || !$serverConfig->getIsUsePopBeforeSMTP(),
    	));

    	$this->addCheckBox("is_use_ssl_receive_server", array(
			"elementId" => "is_use_ssl_receive_server",
			"name" => "isUseSSLReceiveServer",
    		"value" => 1,
    		"selected" => self::_isSSLEnabled() && $serverConfig->getIsUseSSLReceiveServer(),
    		"disabled" => !self::_isSSLEnabled() || ($serverConfig->getSendServerType() != SOYShop_ServerConfig::SERVER_TYPE_SMTP),
    		"onclick" => 'changeReceivePort();'
    	));

    	/* SSL */
    	$this->createAdd("is_ssl_enabled", "HTMLHidden", array(
    		"id"    => "is_ssl_enabled",
    		"value" => (int)self::_isSSLEnabled()
    	));
		DisplayPlugin::toggle("ssl_disabled", !self::_isSSLEnabled());
		DisplayPlugin::toggle("ssl_disabled_1", !self::_isSSLEnabled());

    	/* IMAP */
    	$this->createAdd("is_imap_enabled", "HTMLHidden", array(
    		"id"    => "is_imap_enabled",
    		"value" => (int)self::_isIMAPEnabled()
    	));
		DisplayPlugin::toggle("imap_disabled", !self::_isIMAPEnabled());

    	/* 管理者設定 */
    	$this->addInput("administrator_address", array(
			"name" => "administratorMailAddress",
    		"value" => $serverConfig->getAdministratorMailAddress()
    	));
    	$this->addInput("administrator_name", array(
			"name" => "administratorName",
    		"value" => $serverConfig->getAdministratorName()
    	));
    	$this->addInput("administrator_mail", array(
			"name" => "senderMailAddress",
    		"value" => $serverConfig->getSenderMailAddress()
    	));
    	$this->addInput("return_address", array(
			"name" => "returnMailAddress",
    		"value" => $serverConfig->getReturnMailAddress()
    	));
    	$this->addInput("return_name", array(
			"name" => "returnName",
    		"value" => $serverConfig->getReturnName()
    	));
    	$this->addTextArea("signature", array(
			"name" => "signature",
    		"text" => $serverConfig->getSignature()
    	));
    	$this->addCheckBox("is_send_with_administrator", array(
    		"name" => "isSendWithAdministrator",
    		"value" => 1,
    		"isBoolean" => true,
    		"elementId" => "is_send_with_administrator",
    		"selected" => $serverConfig->getIsSendWithAdministrator()
    	));
		$this->addTextArea("additional_mail_address_for_user_mail", array(
    		"elementId" => "additional_mail_address_for_user_mail",
    		"name" => "additionalMailAddressForUserMail",
    		"text" => $serverConfig->getAdditionalMailAddressForUserMail()
    	));
		$this->addTextArea("additional_mail_address_for_admin_mail", array(
    		"elementId" => "additional_mail_address_for_admin_mail",
    		"name" => "additionalMailAddressForAdminMail",
    		"text" => $serverConfig->getAdditionalMailAddressForAdminMail()
    	));

    	/* 文字コード設定 */
    	$this->addSelect("encoding_select", array(
    		"name" => "encoding",
    		"options" => array("UTF-8","ISO-2022-JP"),
    		"selected" => $serverConfig->getEncoding()
    	));

		$this->addTextArea("encoding_config", array(
    		"name" => "encodingConfig",
    		"text" => $serverConfig->getEncodingConfig()
    	));
    }

    private function _buildTestSendForm(){
    	$this->addForm("test_form");

		$this->addLabel("test_mail_address", array(
			"name" => "test_mail_address",
			"value" => "",
		));
    }

    private function _testSend($to){

    	$title = "SOY Shop テストメール " . date("Y-m-d H:i:s");
    	$content = "これはSOY Shopから送信したテストメールです。";
		$mailLogic = SOY2Logic::createInstance("logic.mail.MailLogic");

    	$serverConfig = SOYShop_ServerConfig::load();
		$mailLogic->sendMail($to, $title, $content, "テストメール送信先");
    }

    /**
     * SSLが使用可能かを返す
     * @return Boolean
     */
    private function _isSSLEnabled(){
    	return function_exists("openssl_open");
    }

    /**
     * IMAPが使用可能かを返す
     */
    private function _isIMAPEnabled(){
    	return function_exists("imap_open");
    }

	function getBreadcrumb(){
		return BreadcrumbComponent::build("サーバ・アドレス設定", array("Config" => "設定", "Config.Mail" => "メール設定"));
	}
}
