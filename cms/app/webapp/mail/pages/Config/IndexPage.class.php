<?php

class IndexPage extends WebPage{

	function doPost(){

		if(isset($_POST["test"])){
			try{
				$this->testSend();
			}catch(Exception $e){
				SOY2PageController::jump("mail.Config?failed");
				exit;
			}
			SOY2PageController::jump("mail.Config?sended");
			exit;
		}

		$dao = SOY2DAOFactory::create("ServerConfigDAO");
		$serverConfig = SOY2::cast("ServerConfig",(object)$_POST);
		$dao->update($serverConfig);

		SOY2PageController::jump("mail.Config?saved");
		exit;

	}

    function IndexPage() {
    	//SUPER USER以外には表示させない
    	if(CMSApplication::getAppAuthLevel() != 1)CMSApplication::jump("");
    	
    	WebPage::WebPage();

    	$this->buildForm();

    	$this->createAdd("saved_message","HTMLModel",array(
    		"visible" => (isset($_GET["saved"]))
    	));

    	$this->createAdd("sended_message","HTMLModel",array(
    		"visible" => (isset($_GET["sended"]))
    	));

    	$this->createAdd("failed_message","HTMLModel",array(
    		"visible" => (isset($_GET["failed"]))
    	));
    }

    function buildForm(){

    	$this->createAdd("form","HTMLForm");

    	$serverConfig = SOY2DAOFactory::create("ServerConfigDAO")->get();

		/* 送信設定 */
    	$this->createAdd("send_server_type_smtp","HTMLCheckBox",array(
    		"elementId" => "send_server_type_smtp",
    		"name" => "sendServerType",
    		"value" => ServerConfig::SERVER_TYPE_SMTP,
    		"selected" => (ServerConfig::SERVER_TYPE_SMTP == $serverConfig->getSendServerType()),
    		"onclick" => 'changeSendPort();'
    	));

    	$this->createAdd("send_server_type_sendmail","HTMLCheckBox",array(
    		"name" => "sendServerType",
    		"value" => ServerConfig::SERVER_TYPE_SENDMAIL,
    		"selected" => (ServerConfig::SERVER_TYPE_SENDMAIL == $serverConfig->getSendServerType()),
    	));

    	$this->createAdd("is_use_smtp_auth_hidden","HTMLInput",array(
			"name" => "isUseSMTPAuth",
    		"value" => 0,
    		"type" => "hidden"
    	));
    	$this->createAdd("is_use_smtp_auth","HTMLCheckBox",array(
			"elementId" => "is_use_smtp_auth",
			"name" => "isUseSMTPAuth",
    		"value" => 1,
    		"selected" => $serverConfig->getIsUseSMTPAuth()
    	));

    	$this->createAdd("send_server_address","HTMLInput",array(
			"name" => "sendServerAddress",
    		"value" => $serverConfig->getSendServerAddress()
    	));

    	$this->createAdd("send_server_user","HTMLInput",array(
			"name" => "sendServerUser",
    		"value" => $serverConfig->getSendServerUser()
    	));

    	$this->createAdd("send_server_password","HTMLInput",array(
			"name" => "sendServerPassword",
    		"value" => $serverConfig->getSendServerPassword()
    	));

    	$this->createAdd("send_server_port","HTMLInput",array(
			"id" => "sendServerPort",
			"name" => "sendServerPort",
    		"value" => $serverConfig->getSendServerPort()
    	));

    	$this->createAdd("is_use_ssl_send_server_hidden","HTMLInput",array(
			"name" => "isUseSSLSendServer",
    		"value" => 0,
    		"type" => "hidden"
    	));
    	$this->createAdd("is_use_ssl_send_server","HTMLCheckBox",array(
    		"elementId" => "is_use_ssl_send_server",
			"name" => "isUseSSLSendServer",
    		"value" => 1,
    		"selected" => $serverConfig->getIsUseSSLSendServer(),
    		"onclick" => 'changeSendPort();'
    	));


    	/* 受信設定 */
    	$this->createAdd("receive_server_type_pop","HTMLCheckBox",array(
    		"elementId" => "receive_server_type_pop",
			"name" => "receiveServerType",
    		"value" => ServerConfig::SERVER_TYPE_SMTP,
    		"selected" => (ServerConfig::SERVER_TYPE_SMTP == $serverConfig->getReceiveServerType()),
    		"onclick" => 'changeReceivePort();'
    	));

    	$this->createAdd("receive_server_type_imap","HTMLCheckBox",array(
    		"elementId" => "receive_server_type_imap",
    		"name" => "receiveServerType",
    		"value" => ServerConfig::SERVER_TYPE_IMAP,
    		"selected" => (ServerConfig::SERVER_TYPE_IMAP == $serverConfig->getReceiveServerType()),
    		"onclick" => 'changeReceivePort();'
    	));

    	$this->createAdd("receive_server_type_sendmail","HTMLCheckBox",array(
    		"name" => "receiveServerType",
    		"value" => ServerConfig::SERVER_TYPE_SENDMAIL,
    		"selected" => (ServerConfig::SERVER_TYPE_SENDMAIL == $serverConfig->getReceiveServerType())
    	));

    	$this->createAdd("is_use_smtp_auth_hidden","HTMLInput",array(
			"name" => "isUseSMTPAuth",
    		"value" => 0,
    		"type" => "hidden"
    	));
    	$this->createAdd("is_use_smtp_auth","HTMLCheckBox",array(
			"name" => "isUseSMTPAuth",
    		"value" => 1,
    		"selected" => $serverConfig->getIsUseSMTPAuth()
    	));

    	$this->createAdd("receive_server_address","HTMLInput",array(
			"name" => "receiveServerAddress",
    		"value" => $serverConfig->getReceiveServerAddress()
    	));

    	$this->createAdd("receive_server_user","HTMLInput",array(
			"name" => "receiveServerUser",
    		"value" => $serverConfig->getReceiveServerUser()
    	));

    	$this->createAdd("receive_server_password","HTMLInput",array(
			"name" => "receiveServerPassword",
    		"value" => $serverConfig->getReceiveServerPassword()
    	));

    	$this->createAdd("receive_server_port","HTMLInput",array(
			"id" => "receiveServerPort",
			"name" => "receiveServerPort",
    		"value" => $serverConfig->getReceiveServerPort()
    	));

    	$this->createAdd("is_use_ssl_receive_server_hidden","HTMLInput",array(
			"name" => "isUseSSLReceiveServer",
    		"value" => 0,
    		"type" => "hidden"
    	));
    	$this->createAdd("is_use_ssl_receive_server","HTMLCheckBox",array(
			"elementId" => "is_use_ssl_receive_server",
			"name" => "isUseSSLReceiveServer",
    		"value" => 1,
    		"selected" => $serverConfig->getIsUseSSLReceiveServer(),
    		"onclick" => 'changeReceivePort();'
    	));

    	$this->createAdd("is_use_pop_before_smtp_hidden","HTMLInput",array(
			"name" => "isUsePopBeforeSMTP",
    		"value" => 0,
    		"type" => "hidden"
    	));
    	$this->createAdd("is_use_pop_before_smtp","HTMLCheckBox",array(
			"elementId" => "is_use_pop_before_smtp",
			"name" => "isUsePopBeforeSMTP",
    		"value" => 1,
    		"selected" => $serverConfig->getIsUsePopBeforeSMTP()
    	));

    	/* 管理者設定 */
    	$this->createAdd("administrator_address","HTMLInput",array(
			"name" => "administratorMailAddress",
    		"value" => $serverConfig->getAdministratorMailAddress()
    	));
    	$this->createAdd("administrator_name","HTMLInput",array(
			"name" => "administratorName",
    		"value" => $serverConfig->getAdministratorName()
    	));
    	$this->createAdd("administrator_mail","HTMLInput",array(
			"name" => "senderMailAddress",
    		"value" => $serverConfig->getSenderMailAddress()
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

    	/* メール配信設定 */
    	
    	//送信処理の実行 exec
    	$this->createAdd("job_type_exec","HTMLCheckbox",array(
    		"name" => "jobType",
    		"label" => "execで実行",
    		"value" => ServerConfig::JOB_TYPE_EXEC,
    		"selected" => ($serverConfig->getJobType() == ServerConfig::JOB_TYPE_EXEC),
    		"onclick" => "toggle_exec_wrapper(this.checked);"
    	));
    	
    	//送信処理の実行 php
    	$this->createAdd("job_type_php","HTMLCheckbox",array(
    		"name" => "jobType",
    		"label" => "PHPで実行",
    		"value" => ServerConfig::JOB_TYPE_PHP,
    		"selected" => ($serverConfig->getJobType() == ServerConfig::JOB_TYPE_PHP),
    		"onclick" => "toggle_exec_wrapper(!this.checked);"
    	));


    	//配信の種類 一括
    	$this->createAdd("send_type_single","HTMLCheckbox",array(
    		"name" => "sendType",
    		"label" => "一括配信",
    		"value" => ServerConfig::SEND_TYPE_SINGLE,
    		"selected" => ($serverConfig->getSendType() == ServerConfig::SEND_TYPE_SINGLE),
    		"onclick" => "toggle_send_type(!this.checked);"
    	));
    	
    	//配信の種類 分割
    	$this->createAdd("send_type_split","HTMLCheckbox",array(
    		"name" => "sendType",
    		"label" => "分割配信(cronを利用)",
    		"value" => ServerConfig::SEND_TYPE_SPLIT,
    		"selected" => ($serverConfig->getSendType() == ServerConfig::SEND_TYPE_SPLIT),
    		"onclick" => "toggle_send_type(this.checked);"
    	));
    	
    	//分割配信用項目の表示/非表示
    	$this->createAdd("send_type_restriction","HTMLModel",array(
    		"attr:id" => "send_type_restriction",
    		"style" => ($serverConfig->getSendType() == ServerConfig::SEND_TYPE_SPLIT) ? "" : "display:none",
    	));
    	
    	//分割配信用項目の表示/非表示
    	$this->createAdd("send_type_interval","HTMLModel",array(
    		"attr:id" => "send_type_interval",
    		"style" => ($serverConfig->getSendType() == ServerConfig::SEND_TYPE_SPLIT) ? "" : "display:none",
    	));
    	
    	
    	//送信数制限
    	$this->createAdd("send_restriction", "HTMLInput", array(
    		"name" => "sendRestriction",
    		"value" => $serverConfig->getSendRestriction(),
    		"style" => "width:40px;ime-mode:inactive;"
    	));
    	
    	//次回送信までの感覚
    	$this->createAdd("send_restriction_interval", "HTMLInput", array(
    		"name" => "sendRestrictionInterval",
    		"value" => $serverConfig->getSendRestrictionInterval(),
    		"style" => "width:40px;ime-mode:inactive;"
    	));
    	
    	$this->createAdd("php_path_wrapper","HTMLModel",array(
    		"attr:id" => "php_path_wrapper",
    		"style" => ($serverConfig->getJobType() == ServerConfig::JOB_TYPE_EXEC) ? "" : "display:none",
    		"visible" => (strpos(PHP_OS,"WIN") == false)	//windowsは非表示
    	));
    	
    	$this->createAdd("php_path","HTMLInput",array(
    		"name" => "phpPath",
    		"value" => $serverConfig->getPhpPath()
    	));
    }

    function testSend(){
		$serverConfig = SOY2DAOFactory::create("ServerConfigDAO")->get();
    	SOY2::import("domain.Mail");
    	$mail = new Mail();

    	$mail->setTitle("これはSOYMAILから送信したテストメールです。");
    	$mail->setMailContent("これはSOYMAILから送信したテストメールです。");
    	$mail->setSenderAddress($serverConfig->getAdministratorMailAddress());
    	$mail->setSenderName($serverConfig->getAdministratorName());
    	$mail->setReturnAddress($serverConfig->getReturnMailAddress());
    	$mail->setReturnName($serverConfig->getReturnName());
		//timeリミットを増やす
		set_time_limit(0);

		//サーバ設定の取得

		//popBeforeSMTP
		if($serverConfig->getIsUsePopBeforeSMTP()){
			if($serverConfig->getReceiveServerType() != ServerConfig::SERVER_TYPE_SMTP
			&& $serverConfig->getReceiveServerType() != ServerConfig::SERVER_TYPE_IMAP
			){
				throw new Exception("invalid receive server type");
			}

			//before smtp
			$receive = $serverConfig->createReceiveServerObject();
			$receive->open();
			$receive->close();
		}

		//送信側の設定
 		$from = $serverConfig->getSenderMailAddress();

	   	$label = $mail->getSenderName();
		$send = $serverConfig->createSendServerObject();
		$send->setFrom($from,$label);
		$send->setHeader("Return-Address", $serverConfig->getReturnMailAddress());	//$serverConfig->getReturnName()

		$send->setHeader("X-SOYMAIL",$mail->getId() ."_". md5($mail->getTitle()));
		$send->setHeader("Date",date("r"));

		$send->setSubject($mail->getTitle());
		$send->setText($mail->getMailContent());




		$send->setRecipients(array());
		$send->addRecipient($serverConfig->getAdministratorMailAddress());
		$send->send();



   }
}
?>