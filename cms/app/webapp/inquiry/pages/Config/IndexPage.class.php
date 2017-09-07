<?php

class IndexPage extends WebPage{

	var $errorMessage = "";

	function doPost(){

		if(soy2_check_token()){
			if(isset($_POST["test_mail_address"])){
				try{
					$this->testSend($_POST["test_mail_address"]);
				}catch(Exception $e){
					CMSApplication::jump("Config?failed_to_test");
				}
				CMSApplication::jump("Config?success_test");
			}else{
				try{
					$dao = SOY2DAOFactory::create("SOYInquiry_ServerConfigDAO");
					$serverConfig = SOY2::cast("SOYInquiry_ServerConfig",(object)$_POST);
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

    	$this->buildForm();
    	$this->buildTestSendForm();

    	$this->createAdd("success_update","HTMLModel",array("visible" => isset($_GET["success_update"])));
    	$this->createAdd("success_test","HTMLModel",array("visible" => isset($_GET["success_test"])));
    	$this->createAdd("failed_to_test","HTMLModel",array("visible" => isset($_GET["failed_to_test"])));
    	$this->createAdd("failed_to_update","HTMLModel",array("visible" => isset($_GET["failed_to_update"])));

    }

    function buildForm(){

    	$this->createAdd("form","HTMLForm");

    	$serverConfig = SOY2DAOFactory::create("SOYInquiry_ServerConfigDAO")->get();

    	$this->createAdd("send_server_type_sendmail","HTMLCheckBox",array(
    		"elementId" => "send_server_type_sendmail",
    		"name" => "sendServerType",
    		"value" => SOYInquiry_ServerConfig::SERVER_TYPE_SENDMAIL,
    		"selected" => ($serverConfig->getSendServerType() == SOYInquiry_ServerConfig::SERVER_TYPE_SENDMAIL),
    		"onclick" => 'toggleSMTP()'
    	));
    	$this->createAdd("send_server_type_smtp","HTMLCheckBox",array(
    		"elementId" => "send_server_type_smtp",
    		"name" => "sendServerType",
    		"value" => SOYInquiry_ServerConfig::SERVER_TYPE_SMTP,
    		"selected" => ($serverConfig->getSendServerType() == SOYInquiry_ServerConfig::SERVER_TYPE_SMTP),
    		"onclick" => 'toggleSMTP()'
    	));


    	$this->createAdd("is_use_pop_before_smtp","HTMLCheckBox",array(
			"elementId" => "is_use_pop_before_smtp",
			"name" => "isUsePopBeforeSMTP",
    		"value" => 1,
    		"selected" => $serverConfig->getIsUsePopBeforeSMTP(),
    		"disabled" => ($serverConfig->getSendServerType() != SOYInquiry_ServerConfig::SERVER_TYPE_SMTP),
    		"onclick" => 'togglePOPIMAPSetting();'
    	));

    	$this->createAdd("is_use_smtp_auth","HTMLCheckBox",array(
			"elementId" => "is_use_smtp_auth",
			"name" => "isUseSMTPAuth",
    		"value" => 1,
    		"selected" => $serverConfig->getIsUseSMTPAuth(),
    		"disabled" => ($serverConfig->getSendServerType() != SOYInquiry_ServerConfig::SERVER_TYPE_SMTP),
    		"onclick" => 'toggleSMTPAUTHSetting();'
    	));

    	$this->createAdd("send_server_address","HTMLInput",array(
			"id" => "send_server_address",
			"name" => "sendServerAddress",
    		"value" => $serverConfig->getSendServerAddress(),
    		"disabled" => ($serverConfig->getSendServerType() != SOYInquiry_ServerConfig::SERVER_TYPE_SMTP),
    	));
    	$this->createAdd("send_server_port","HTMLInput",array(
			"id" => "send_server_port",
			"name" => "sendServerPort",
    		"value" => $serverConfig->getSendServerPort(),
    		"disabled" => ($serverConfig->getSendServerType() != SOYInquiry_ServerConfig::SERVER_TYPE_SMTP),
    	));


    	$this->createAdd("send_server_user","HTMLInput",array(
			"id" => "send_server_user",
			"name" => "sendServerUser",
    		"value" => $serverConfig->getSendServerUser(),
    		"disabled" => ($serverConfig->getSendServerType() != SOYInquiry_ServerConfig::SERVER_TYPE_SMTP) OR !$serverConfig->getIsUseSMTPAuth(),
    	));
    	$this->createAdd("send_server_password","HTMLInput",array(
			"id" => "send_server_password",
			"name" => "sendServerPassword",
    		"value" => $serverConfig->getSendServerPassword(),
    		"disabled" => ($serverConfig->getSendServerType() != SOYInquiry_ServerConfig::SERVER_TYPE_SMTP) OR !$serverConfig->getIsUseSMTPAuth(),
    		"attr:autocomplete" => "off",
    	));

    	$this->createAdd("is_use_ssl_send_server","HTMLCheckBox",array(
    		"elementId" => "is_use_ssl_send_server",
			"name" => "isUseSSLSendServer",
    		"value" => 1,
    		"selected" => $this->isSSLEnabled() && $serverConfig->getIsUseSSLSendServer(),
    		"disabled" => !$this->isSSLEnabled() OR ($serverConfig->getSendServerType() != SOYInquiry_ServerConfig::SERVER_TYPE_SMTP),
    		"onclick" => 'changeSendPort();'
    	));

    	/* 受信設定 */
    	$this->createAdd("receive_server_type_pop","HTMLCheckBox",array(
    		"elementId" => "receive_server_type_pop",
			"name" => "receiveServerType",
    		"value" => SOYInquiry_ServerConfig::RECEIVE_SERVER_TYPE_POP,
    		"selected" => ($serverConfig->getReceiveServerType() == SOYInquiry_ServerConfig::RECEIVE_SERVER_TYPE_POP),
    		"disabled" => ($serverConfig->getSendServerType() != SOYInquiry_ServerConfig::SERVER_TYPE_SMTP) OR !$serverConfig->getIsUsePopBeforeSMTP(),
    		"onclick" => 'changeReceivePort();'
    	));

    	$this->createAdd("receive_server_type_imap","HTMLCheckBox",array(
    		"elementId" => "receive_server_type_imap",
    		"name" => "receiveServerType",
    		"value" => SOYInquiry_ServerConfig::RECEIVE_SERVER_TYPE_IMAP,
    		"selected" => ($serverConfig->getReceiveServerType() == SOYInquiry_ServerConfig::RECEIVE_SERVER_TYPE_IMAP),
    		"disabled" => ($serverConfig->getSendServerType() != SOYInquiry_ServerConfig::SERVER_TYPE_SMTP) OR !$serverConfig->getIsUsePopBeforeSMTP() OR !$this->isIMAPEnabled(),
    		"onclick" => 'changeReceivePort();'
    	));

    	$this->createAdd("receive_server_address","HTMLInput",array(
			"id" => "receive_server_address",
			"name" => "receiveServerAddress",
    		"value" => $serverConfig->getReceiveServerAddress(),
    		"disabled" => ($serverConfig->getSendServerType() != SOYInquiry_ServerConfig::SERVER_TYPE_SMTP) OR !$serverConfig->getIsUsePopBeforeSMTP(),
    	));

    	$this->createAdd("receive_server_user","HTMLInput",array(
			"id" => "receive_server_user",
			"name" => "receiveServerUser",
    		"value" => $serverConfig->getReceiveServerUser(),
    		"disabled" => ($serverConfig->getSendServerType() != SOYInquiry_ServerConfig::SERVER_TYPE_SMTP) OR !$serverConfig->getIsUsePopBeforeSMTP(),
    	));

    	$this->createAdd("receive_server_password","HTMLInput",array(
			"id" => "receive_server_password",
			"name" => "receiveServerPassword",
    		"value" => $serverConfig->getReceiveServerPassword(),
    		"disabled" => ($serverConfig->getSendServerType() != SOYInquiry_ServerConfig::SERVER_TYPE_SMTP) OR !$serverConfig->getIsUsePopBeforeSMTP(),
    		"attr:autocomplete" => "off",
    	));

    	$this->createAdd("receive_server_port","HTMLInput",array(
			"id" => "receive_server_port",
			"name" => "receiveServerPort",
    		"value" => $serverConfig->getReceiveServerPort(),
    		"disabled" => ($serverConfig->getSendServerType() != SOYInquiry_ServerConfig::SERVER_TYPE_SMTP) OR !$serverConfig->getIsUsePopBeforeSMTP(),
    	));

    	$this->createAdd("is_use_ssl_receive_server","HTMLCheckBox",array(
			"elementId" => "is_use_ssl_receive_server",
			"name" => "isUseSSLReceiveServer",
    		"value" => 1,
    		"selected" => $this->isSSLEnabled() && $serverConfig->getIsUseSSLReceiveServer(),
    		"disabled" => !$this->isSSLEnabled() OR ($serverConfig->getSendServerType() != SOYInquiry_ServerConfig::SERVER_TYPE_SMTP),
    		"onclick" => 'changeReceivePort();'
    	));

    	/* SSL */
    	$this->createAdd("is_ssl_enabled", "HTMLHidden", array(
    		"id"    => "is_ssl_enabled",
    		"value" => (int) $this->isSSLEnabled()
    	));
    	$this->createAdd("ssl_disabled", "HTMLModel", array(
    		"visible" => !$this->isSSLEnabled()
    	));
    	/* IMAP */
    	$this->createAdd("is_imap_enabled", "HTMLHidden", array(
    		"id"    => "is_imap_enabled",
    		"value" => (int) $this->isIMAPEnabled()
    	));
    	$this->createAdd("imap_disabled", "HTMLModel", array(
    		"visible" => !$this->isIMAPEnabled()
    	));

    	/* メール設定 */
    	$this->createAdd("administrator_address","HTMLInput",array(
			"name" => "administratorMailAddress",
    		"value" => $serverConfig->getAdministratorMailAddress()
    	));
    	$this->createAdd("administrator_name","HTMLInput",array(
			"name" => "administratorName",
    		"value" => $serverConfig->getAdministratorName()
    	));
    	$this->createAdd("return_address","HTMLInput",array(
			"name" => "returnMailAddress",
    		"value" => $serverConfig->getReturnMailAddress()
    	));
    	$this->createAdd("return_name","HTMLInput",array(
			"name" => "returnName",
    		"value" => $serverConfig->getReturnName()
    	));
    	$this->createAdd("signature","HTMLTextArea",array(
			"name" => "signature",
    		"text" => $serverConfig->getSignature()
    	));
    	$this->createAdd("mail_encoding","HTMLSelect",array(
    		"name" => "encoding",
    		"selected" => $serverConfig->getEncoding() ,
    		"options" => array(
    			"ISO-2022-JP" => "JIS (ISO-2022-JP)",
    			"UTF-8" => "UTF-8",
    		)
    	));

    	/*ファイル設定*/
    	$this->createAdd("upload_root_dir","HTMLLabel",array(
    		"text" => SOY_INQUIRY_UPLOAD_ROOT_DIR
    	));

    	$this->createAdd("upload_dir","HTMLInput",array(
    		"name" => "uploadDir",
    		"value" => $serverConfig->getUploadDir()
    	));

    	/* 管理側URL */
    	$this->createAdd("admin_url","HTMLInput",array(
    		"name" => "adminUrl",
    		"value" => SOY2PageController::createLink("",true)
    	));
    }

    function buildTestSendForm(){

    	$this->createAdd("test_form","HTMLForm");

		$this->createAdd("test_mail_address","HTMLLabel",array(
			"name" => "test_mail_address",
			"value" => "",
		));

    }

    function testSend($to){

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
?>