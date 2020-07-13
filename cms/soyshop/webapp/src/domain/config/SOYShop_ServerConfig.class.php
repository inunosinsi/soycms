<?php

class SOYShop_ServerConfig {

	const SERVER_TYPE_SMTP = 0;
	const SERVER_TYPE_SENDMAIL = 2;

	const RECEIVE_SERVER_TYPE_POP  = 0;
	const RECEIVE_SERVER_TYPE_IMAP = 1;

	//送信設定
    private $sendServerType = SOYShop_ServerConfig::SERVER_TYPE_SENDMAIL;
    private $isUseSMTPAuth = true;
    private $isUsePopBeforeSMTP = false;
    private $sendServerAddress = "localhost";
    private $sendServerPort = 25;
    private $sendServerUser = "";
    private $sendServerPassword = "";
    private $isUseSSLSendServer = false;

    //受信設定
    private $receiveServerType = SOYShop_ServerConfig::RECEIVE_SERVER_TYPE_POP;
    private $receiveServerAddress = "localhost";
    private $receiveServerPort = 110;
    private $receiveServerUser = "";
    private $receiveServerPassword = "";
    private $isUseSSLReceiveServer = false;

    //管理者設定
    private $administratorName = "";
    private $administratorMailAddress = "";
    private $senderMailAddress  = "";
    private $returnMailAddress =  "";
    private $returnName = "";

    private $signature = "";

	//追加の送信先
    private $isSendWithAdministrator = true;
    private $additionalMailAddressForUserMail = "";
    private $additionalMailAddressForAdminMail = "";

    private $encoding = "UTF-8";
    private $encodingConfig = "";

    function getSendServerType() {
    	return $this->sendServerType;
    }
    function setSendServerType($sendServerType) {
    	$this->sendServerType = $sendServerType;
    }
    function getIsUseSMTPAuth() {
    	return $this->isUseSMTPAuth;
    }
    function setIsUseSMTPAuth($isUseSMTPAuth) {
    	$this->isUseSMTPAuth = $isUseSMTPAuth;
    }
    function getSendServerAddress() {
    	return $this->sendServerAddress;
    }
    function setSendServerAddress($sendServerAddress) {
    	$this->sendServerAddress = $sendServerAddress;
    }
    function getSendServerPort() {
    	return $this->sendServerPort;
    }
    function setSendServerPort($sendServerPort) {
    	$this->sendServerPort = $sendServerPort;
    }
    function getSendServerUser() {
    	return $this->sendServerUser;
    }
    function setSendServerUser($sendServerUser) {
    	$this->sendServerUser = $sendServerUser;
    }
    function getSendServerPassword() {
    	return $this->sendServerPassword;
    }
    function setSendServerPassword($sendServerPassword) {
    	$this->sendServerPassword = $sendServerPassword;
    }
    function getIsUseSSLSendServer() {
    	return $this->isUseSSLSendServer;
    }
    function setIsUseSSLSendServer($isUseSSLSendServer) {
    	$this->isUseSSLSendServer = $isUseSSLSendServer;
    }
    function getReceiveServerType() {
    	return $this->receiveServerType;
    }
    function setReceiveServerType($recieveServerType) {
    	$this->receiveServerType = $recieveServerType;
    }
    function getReceiveServerAddress() {
    	return $this->receiveServerAddress;
    }
    function setReceiveServerAddress($receiveServerAddress) {
    	$this->receiveServerAddress = $receiveServerAddress;
    }
    function getReceiveServerPort() {
    	return $this->receiveServerPort;
    }
    function setReceiveServerPort($receiveServerPort) {
    	$this->receiveServerPort = $receiveServerPort;
    }
    function getReceiveServerUser() {
    	return $this->receiveServerUser;
    }
    function setReceiveServerUser($receiveServerUser) {
    	$this->receiveServerUser = $receiveServerUser;
    }
    function getReceiveServerPassword() {
    	return $this->receiveServerPassword;
    }
    function setReceiveServerPassword($receiveServerPassword) {
    	$this->receiveServerPassword = $receiveServerPassword;
    }
    function getIsUsePopBeforeSMTP() {
    	return $this->isUsePopBeforeSMTP;
    }
    function setIsUsePopBeforeSMTP($isUsePopBeforeSMTP) {
    	$this->isUsePopBeforeSMTP = $isUsePopBeforeSMTP;
    }
    function getIsUseSSLReceiveServer() {
    	return $this->isUseSSLReceiveServer;
    }
    function setIsUseSSLReceiveServer($isUseSSLReceiveServer) {
    	$this->isUseSSLReceiveServer = $isUseSSLReceiveServer;
    }
    function getAdministratorName() {
    	return $this->administratorName;
    }
    function setAdministratorName($administratorName) {
    	$this->administratorName = $administratorName;
    }
    function getAdministratorMailAddress() {
    	return $this->administratorMailAddress;
    }
    function setAdministratorMailAddress($administratorMailAddress) {
    	$this->administratorMailAddress = $administratorMailAddress;
    }
    function getSenderMailAddress() {
    	return $this->senderMailAddress;
    }
    function setSenderMailAddress($senderMailAddress) {
    	$this->senderMailAddress = $senderMailAddress;
    }
    function getReturnMailAddress() {
    	return $this->returnMailAddress;
    }
    function setReturnMailAddress($returnMailAddress) {
    	$this->returnMailAddress = $returnMailAddress;
    }
    function getReturnName() {
    	return $this->returnName;
    }
    function setReturnName($returnName) {
    	$this->returnName = $returnName;
    }
    function getSignature() {
    	return $this->signature;
    }
    function setSignature($signature) {
    	$this->signature = $signature;
    }

    /**
     * 設定からSOY2Mailオブジェクトを生成する
     */
    function createReceiveServerObject(){

    	switch($this->receiveServerType){
    		case SOYShop_ServerConfig::RECEIVE_SERVER_TYPE_IMAP:

    			$flag = null;
    			if($this->getIsUseSSLReceiveServer()) $flag = "ssl";

    			return SOY2Mail::create("imap", array(
    				"imap.host" => $this->getReceiveServerAddress(),
    				"imap.port" => $this->getReceiveServerPort(),
    				"imap.user" => $this->getReceiveServerUser(),
    				"imap.pass" => $this->getReceiveServerPassword(),
    				"imap.flag" => $flag
    			));
    			break;

    		case SOYShop_ServerConfig::RECEIVE_SERVER_TYPE_POP:
    		default:

    			$host = $this->getReceiveServerAddress();
    			if($this->getIsUseSSLReceiveServer()) $host =  "ssl://" . $host;

    			return SOY2Mail::create("pop", array(
    				"pop.host" => $host,
    				"pop.port" => $this->getReceiveServerPort(),
    				"pop.user" => $this->getReceiveServerUser(),
    				"pop.pass" => $this->getReceiveServerPassword()
    			));
    			break;
    	}
    }

    /**
     * 設定からSOY2Mailオブジェクトを生成する
     */
    function createSendServerObject(){

    	switch($this->sendServerType){
    		case SOYShop_ServerConfig::SERVER_TYPE_SMTP:
    			$host = $this->getSendServerAddress();
    			if($this->getIsUseSSLSendServer()) $host =  "ssl://" . $host;

    			return SOY2Mail::create("smtp", array(
    				"smtp.host" => $host,
    				"smtp.port" => $this->getSendServerPort(),
    				"smtp.user" => $this->getSendServerUser(),
    				"smtp.pass" => $this->getSendServerPassword(),
    				"smtp.auth" => ($this->getIsUseSMTPAuth()) ? "PLAIN" : false
    			));
    			break;
    		case SOYShop_ServerConfig::SERVER_TYPE_SENDMAIL:
    		default:
    			return SOY2Mail::create("sendmail", array());
    			break;
    	}
    }

    public static function load(){
    	try{
    		$obj = SOYShop_DataSets::get("soyshop_serverconfig");

    		if($obj instanceof SOYShop_ServerConfig){
    			return $obj;
    		}else{
    			return new SOYShop_ServerConfig();
    		}
    	}catch(Exception $e){
    		return new SOYShop_ServerConfig();
    	}
    }

    public static function save(SOYShop_ServerConfig $obj){
    	SOYShop_DataSets::put("soyshop_serverconfig", $obj);
    }

    function isSendWithAdministrator() {
    	return (boolean)$this->isSendWithAdministrator;
    }
    function getIsSendWithAdministrator() {
    	return $this->isSendWithAdministrator;
    }
    function setIsSendWithAdministrator($isSendWithAdministrator) {
    	$this->isSendWithAdministrator = $isSendWithAdministrator;
    }

    function getEncoding() {
    	return $this->encoding;
    }
    function setEncoding($encoding) {
    	$this->encoding = $encoding;
    }

    function getEncodingConfig() {
    	if(strlen($this->encodingConfig) < 1){

    		//for mobile
    		return implode("\n", array(
    			"@docomo.ne.jp,ISO-2022-JP",
    			"@ezweb.ne.jp,ISO-2022-JP",
    			"@softbank.ne.jp,ISO-2022-JP",
    			"@pdx.ne.jp,ISO-2022-JP",
    			"@*.pdx.ne.jp,ISO-2022-JP",
    			"@willcom.com,ISO-2022-JP",
    			"@*.vodafone.ne.jp,ISO-2022-JP",
    		));
    	}

    	return $this->encodingConfig;
    }
    function setEncodingConfig($encodingConfig) {
    	$this->encodingConfig = $encodingConfig;
    }
    function getEncodingByEmailAddress($address){
    	$targetDomain = substr($address,strpos($address, "@"));
    	$config = $this->getEncodingConfig();
    	$config = explode("\n", $config);

    	foreach($config as $line){
    		$line = trim($line);
    		if(empty($line))continue;
    		$array = explode(",",$line);
    		$domain = $array[0];

    		//. -> \.
    		$domain = str_replace(".", "\\.", $domain);

    		//* -> .*
    		$domain = str_replace("*", ".*", $domain);

    		if(preg_match("/$domain/", $targetDomain)){
    			return $array[1];
    		}
    	}

    	return $this->getEncoding();
    }

    function getAdditionalMailAddressForUserMail() {
    	return $this->additionalMailAddressForUserMail;
    }
    function getAdditionalMailAddressForUserMailArray() {
    	return explode("\n",strtr(trim($this->additionalMailAddressForUserMail), array("\r\n" => "\n", "\r" => "\n")));
    }
    function setAdditionalMailAddressForUserMail($additionalMailAddressForUserMail) {
    	$this->additionalMailAddressForUserMail = $additionalMailAddressForUserMail;
    }
    function getAdditionalMailAddressForAdminMail() {
    	return $this->additionalMailAddressForAdminMail;
    }
    function getAdditionalMailAddressForAdminMailArray() {
    	return explode("\n",strtr(trim($this->additionalMailAddressForAdminMail), array("\r\n" => "\n", "\r" => "\n")));
    }
    function setAdditionalMailAddressForAdminMail($additionalMailAddressForAdminMail) {
    	$this->additionalMailAddressForAdminMail = $additionalMailAddressForAdminMail;
    }
}
