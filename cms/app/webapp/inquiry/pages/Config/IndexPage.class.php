<?php

class IndexPage extends WebPage{

	var $errorMessage = "";

	function doPost(){

		if(soy2_check_token()){
			if(isset($_POST["test_mail_address"])){
				try{
					self::testSend($_POST["test_mail_address"]);
				}catch(Exception $e){
					CMSApplication::jump("Config?failed_to_test");
				}
				CMSApplication::jump("Config?success_test");
			}else{
				$dao = SOY2DAOFactory::create("SOYInquiry_ServerConfigDAO");
				$serverConfig = SOY2::cast("SOYInquiry_ServerConfig",(object)$_POST);

				// GMail API OAuth2.0認証を利用する場合は文字コードを強制的にUTF-8にする
				if((int)$serverConfig->getIsUseGmailApiAuth() === 1){
					$serverConfig->setEncoding("UTF-8");
				}
				try{
					$dao->update($serverConfig);

					CMSApplication::jump("Config?success_update");
				}catch(Exception $e){
					CMSApplication::jump("Config?failed_to_update");
				}
			}

		}
		CMSApplication::jump("Config");
		exit;

	}

    function __construct() {
    	//SUPER USER以外には表示させない
    	if(CMSApplication::getAppAuthLevel() != 1)CMSApplication::jump("");

    	parent::__construct();

    	self::buildForm();
    	self::buildTestSendForm();

		DisplayPlugin::toggle("success_update", isset($_GET["success_update"]));
		DisplayPlugin::toggle("success_test", isset($_GET["success_test"]));
		DisplayPlugin::toggle("failed_to_update", isset($_GET["failed_to_update"]));
		DisplayPlugin::toggle("failed_to_test", isset($_GET["failed_to_test"]));
    }

    private function buildForm(){

    	$this->addForm("form");

    	$serverConfig = SOY2DAOFactory::create("SOYInquiry_ServerConfigDAO")->get();

    	$this->addCheckBox("send_server_type_sendmail", array(
    		"elementId" => "send_server_type_sendmail",
    		"name" => "sendServerType",
    		"value" => SOYInquiry_ServerConfig::SERVER_TYPE_SENDMAIL,
    		"selected" => ($serverConfig->getSendServerType() == SOYInquiry_ServerConfig::SERVER_TYPE_SENDMAIL),
    		"onclick" => 'toggleSMTP()'
    	));
    	$this->addCheckBox("send_server_type_smtp", array(
    		"elementId" => "send_server_type_smtp",
    		"name" => "sendServerType",
    		"value" => SOYInquiry_ServerConfig::SERVER_TYPE_SMTP,
    		"selected" => ($serverConfig->getSendServerType() == SOYInquiry_ServerConfig::SERVER_TYPE_SMTP),
    		"onclick" => 'toggleSMTP()'
    	));


    	$this->addCheckBox("is_use_pop_before_smtp", array(
			"elementId" => "is_use_pop_before_smtp",
			"name" => "isUsePopBeforeSMTP",
    		"value" => 1,
    		"selected" => $serverConfig->getIsUsePopBeforeSMTP(),
    		"disabled" => ($serverConfig->getSendServerType() != SOYInquiry_ServerConfig::SERVER_TYPE_SMTP),
    		"onclick" => 'togglePOPIMAPSetting();'
    	));

    	$this->addCheckBox("is_use_gmail_api_auth", array(
			"elementId" => "is_use_gmail_api_auth",
			"name" => "isUseGmailApiAuth",
	    	"value" => 1,
	    	"selected" => $serverConfig->getIsUseGmailApiAuth(),
	    	"disabled" => ($serverConfig->getSendServerType() != SOYInquiry_ServerConfig::SERVER_TYPE_SMTP),
	    	"onclick" => 'toggleGmailApiAuthSetting();'
	    ));

		$isAuthenticatedOAuth = file_exists(SOYInquiryUtil::SOYINQUIRY_GMAIL_API_OAUTH_TOKEN_FILEPATH);
		$this->addModel("is_authenticated_oauth", array("visible" => $isAuthenticatedOAuth));
		$this->addModel("no_authenticated_oauth", array("visible" => !$isAuthenticatedOAuth));

    	$this->addCheckBox("is_use_smtp_auth", array(
			"elementId" => "is_use_smtp_auth",
			"name" => "isUseSMTPAuth",
    		"value" => 1,
    		"selected" => $serverConfig->getIsUseSMTPAuth(),
    		"disabled" => ($serverConfig->getSendServerType() != SOYInquiry_ServerConfig::SERVER_TYPE_SMTP),
    		"onclick" => 'toggleSMTPAUTHSetting();'
    	));

    	$this->addInput("send_server_address", array(
			"id" => "send_server_address",
			"name" => "sendServerAddress",
    		"value" => $serverConfig->getSendServerAddress(),
    		"disabled" => ($serverConfig->getSendServerType() != SOYInquiry_ServerConfig::SERVER_TYPE_SMTP),
    	));
    	$this->addInput("send_server_port", array(
			"id" => "send_server_port",
			"name" => "sendServerPort",
    		"value" => $serverConfig->getSendServerPort(),
    		"disabled" => ($serverConfig->getSendServerType() != SOYInquiry_ServerConfig::SERVER_TYPE_SMTP),
    	));


    	$this->addInput("send_server_user", array(
			"id" => "send_server_user",
			"name" => "sendServerUser",
    		"value" => $serverConfig->getSendServerUser(),
    		"disabled" => ($serverConfig->getSendServerType() != SOYInquiry_ServerConfig::SERVER_TYPE_SMTP) OR !$serverConfig->getIsUseSMTPAuth(),
    	));
    	$this->addInput("send_server_password", array(
			"id" => "send_server_password",
			"name" => "sendServerPassword",
    		"value" => $serverConfig->getSendServerPassword(),
    		"disabled" => ($serverConfig->getSendServerType() != SOYInquiry_ServerConfig::SERVER_TYPE_SMTP) OR !$serverConfig->getIsUseSMTPAuth(),
    		"attr:autocomplete" => "off",
    	));

    	$this->addCheckBox("is_use_ssl_send_server", array(
    		"elementId" => "is_use_ssl_send_server",
			"name" => "isUseSSLSendServer",
    		"value" => 1,
    		"selected" => $this->isSSLEnabled() && $serverConfig->getIsUseSSLSendServer(),
    		"disabled" => !$this->isSSLEnabled() OR ($serverConfig->getSendServerType() != SOYInquiry_ServerConfig::SERVER_TYPE_SMTP),
    		"onclick" => 'changeSendPort();'
    	));

    	/* 受信設定 */
    	$this->addCheckBox("receive_server_type_pop", array(
    		"elementId" => "receive_server_type_pop",
			"name" => "receiveServerType",
    		"value" => SOYInquiry_ServerConfig::RECEIVE_SERVER_TYPE_POP,
    		"selected" => ($serverConfig->getReceiveServerType() == SOYInquiry_ServerConfig::RECEIVE_SERVER_TYPE_POP),
    		"disabled" => ($serverConfig->getSendServerType() != SOYInquiry_ServerConfig::SERVER_TYPE_SMTP) OR !$serverConfig->getIsUsePopBeforeSMTP(),
    		"onclick" => 'changeReceivePort();'
    	));

    	$this->addCheckBox("receive_server_type_imap", array(
    		"elementId" => "receive_server_type_imap",
    		"name" => "receiveServerType",
    		"value" => SOYInquiry_ServerConfig::RECEIVE_SERVER_TYPE_IMAP,
    		"selected" => ($serverConfig->getReceiveServerType() == SOYInquiry_ServerConfig::RECEIVE_SERVER_TYPE_IMAP),
    		"disabled" => ($serverConfig->getSendServerType() != SOYInquiry_ServerConfig::SERVER_TYPE_SMTP) OR !$serverConfig->getIsUsePopBeforeSMTP() OR !$this->isIMAPEnabled(),
    		"onclick" => 'changeReceivePort();'
    	));

    	$this->addInput("receive_server_address", array(
			"id" => "receive_server_address",
			"name" => "receiveServerAddress",
    		"value" => $serverConfig->getReceiveServerAddress(),
    		"disabled" => ($serverConfig->getSendServerType() != SOYInquiry_ServerConfig::SERVER_TYPE_SMTP) OR !$serverConfig->getIsUsePopBeforeSMTP(),
    	));

    	$this->addInput("receive_server_user", array(
			"id" => "receive_server_user",
			"name" => "receiveServerUser",
    		"value" => $serverConfig->getReceiveServerUser(),
    		"disabled" => ($serverConfig->getSendServerType() != SOYInquiry_ServerConfig::SERVER_TYPE_SMTP) OR !$serverConfig->getIsUsePopBeforeSMTP(),
    	));

    	$this->addInput("receive_server_password", array(
			"id" => "receive_server_password",
			"name" => "receiveServerPassword",
    		"value" => $serverConfig->getReceiveServerPassword(),
    		"disabled" => ($serverConfig->getSendServerType() != SOYInquiry_ServerConfig::SERVER_TYPE_SMTP) OR !$serverConfig->getIsUsePopBeforeSMTP(),
    		"attr:autocomplete" => "off",
    	));

    	$this->addInput("receive_server_port", array(
			"id" => "receive_server_port",
			"name" => "receiveServerPort",
    		"value" => $serverConfig->getReceiveServerPort(),
    		"disabled" => ($serverConfig->getSendServerType() != SOYInquiry_ServerConfig::SERVER_TYPE_SMTP) OR !$serverConfig->getIsUsePopBeforeSMTP(),
    	));

    	$this->addCheckBox("is_use_ssl_receive_server", array(
			"elementId" => "is_use_ssl_receive_server",
			"name" => "isUseSSLReceiveServer",
    		"value" => 1,
    		"selected" => $this->isSSLEnabled() && $serverConfig->getIsUseSSLReceiveServer(),
    		"disabled" => !$this->isSSLEnabled() OR ($serverConfig->getSendServerType() != SOYInquiry_ServerConfig::SERVER_TYPE_SMTP),
    		"onclick" => 'changeReceivePort();'
    	));

    	/* SSL */
    	$this->addHidden("is_ssl_enabled", array(
    		"id"    => "is_ssl_enabled",
    		"value" => (int) $this->isSSLEnabled()
    	));
    	$this->addModel("ssl_disabled", array(
    		"visible" => !$this->isSSLEnabled()
    	));
    	/* IMAP */
    	$this->addHidden("is_imap_enabled", array(
    		"id"    => "is_imap_enabled",
    		"value" => (int) $this->isIMAPEnabled()
    	));
    	$this->addModel("imap_disabled", array(
    		"visible" => !$this->isIMAPEnabled()
    	));

    	/* メール設定 */
    	$this->addInput("administrator_address", array(
			"name" => "administratorMailAddress",
    		"value" => $serverConfig->getAdministratorMailAddress()
    	));
    	$this->addInput("administrator_name", array(
			"name" => "administratorName",
    		"value" => $serverConfig->getAdministratorName()
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
    	$this->addSelect("mail_encoding", array(
    		"name" => "encoding",
    		"selected" => $serverConfig->getEncoding() ,
    		"options" => array(
    			"ISO-2022-JP" => "JIS (ISO-2022-JP)",
    			"UTF-8" => "UTF-8",
    		)
    	));

    	/*ファイル設定*/
    	$this->addLabel("upload_root_dir", array(
    		"text" => SOY_INQUIRY_UPLOAD_ROOT_DIR
    	));

    	$this->addInput("upload_dir", array(
    		"name" => "uploadDir",
    		"value" => $serverConfig->getUploadDir()
    	));

    	/* 管理側URL */
    	$this->addInput("admin_url", array(
    		"name" => "adminUrl",
    		"value" => SOY2PageController::createLink("",true)
    	));
    }

    private function buildTestSendForm(){

    	$this->addForm("test_form");

		$this->addLabel("test_mail_address", array(
			"name" => "test_mail_address",
			"value" => "",
		));

    }

    private function testSend(string $to){
    	$serverConfig = SOY2DAOFactory::create("SOYInquiry_ServerConfigDAO")->get();

    	$title = "SOY Inquiry テストメール ".date("Y-m-d H:i:s");
    	$body = "これはSOY Inquiryから送信したテストメールです。\n";

		$mailLogic = SOY2Logic::createInstance("logic.MailLogic",array(
			"serverConfig" => $serverConfig,
		));

		$mailLogic->prepareSend();

    	$serverConfig->getReturnMailAddress();
		$mailLogic->sendMail($to,$title,$body,"テストメール送信先");
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
