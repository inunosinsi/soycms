<?php

class SOYInquiry_ServerConfig {

	const SERVER_TYPE_SMTP = 0;
	const SERVER_TYPE_SENDMAIL = 2;

	const RECEIVE_SERVER_TYPE_POP  = 0;
	const RECEIVE_SERVER_TYPE_IMAP = 1;

	//送信設定
    private $sendServerType = SOYInquiry_ServerConfig::SERVER_TYPE_SENDMAIL;
    private $isUseSMTPAuth = true;
    private $isUsePopBeforeSMTP = false;
    private $sendServerAddress = "localhost";
    private $sendServerPort = 25;
    private $sendServerUser = "";
    private $sendServerPassword = "";
    private $isUseSSLSendServer = false;

    //受信設定
    private $receiveServerType = SOYInquiry_ServerConfig::RECEIVE_SERVER_TYPE_POP;
    private $receiveServerAddress = "localhost";
    private $receiveServerPort = 110;
    private $receiveServerUser = "";
    private $receiveServerPassword = "";
    private $isUseSSLReceiveServer = false;

    //管理者設定
    private $administratorName = "";
    private $administratorMailAddress = "";
    private $returnMailAddress =  "";
    private $returnName = "";

    private $encoding = "ISO-2022-JP";

    private $signature = "";

    //ファイル設定
    private $uploadDir;

    private $adminUrl;

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
    public function getEncoding() {
    	return $this->encoding;
    }
    public function setEncoding($encoding) {
    	$this->encoding = $encoding;
    }

    /**
     * 設定からSOY2Mailオブジェクトを生成する
     */
    function createReceiveServerObject(){

    	switch($this->receiveServerType){
    		case SOYInquiry_ServerConfig::RECEIVE_SERVER_TYPE_IMAP:

    			$flag = null;
    			if($this->getIsUseSSLReceiveServer())$flag = "ssl";

    			return SOY2Mail::create("imap",array(
    				"imap.host" => $this->getReceiveServerAddress(),
    				"imap.port" => $this->getReceiveServerPort(),
    				"imap.user" => $this->getReceiveServerUser(),
    				"imap.pass" => $this->getReceiveServerPassword(),
    				"imap.flag" => $flag
    			));
    			break;

    		case SOYInquiry_ServerConfig::RECEIVE_SERVER_TYPE_POP:
    		default:

    			$host = $this->getReceiveServerAddress();
    			if($this->getIsUseSSLReceiveServer())$host =  "ssl://" . $host;

    			return SOY2Mail::create("pop",array(
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
    		case SOYInquiry_ServerConfig::SERVER_TYPE_SMTP:
    			$host = $this->getSendServerAddress();
    			if($this->getIsUseSSLSendServer())$host =  "ssl://" . $host;

    			return SOY2Mail::create("smtp",array(
    				"smtp.host" => $host,
    				"smtp.port" => $this->getSendServerPort(),
    				"smtp.user" => $this->getSendServerUser(),
    				"smtp.pass" => $this->getSendServerPassword(),
    				"smtp.auth" => ($this->getIsUseSMTPAuth()) ? true : false
    			));
    			break;
    		case SOYInquiry_ServerConfig::SERVER_TYPE_SENDMAIL:
    		default:
    			return SOY2Mail::create("sendmail",array());
    			break;
    	}

    }

    function getUploadDir() {

    	if(strlen($this->uploadDir)<1){
    		$this->uploadDir = "/";
    	}

    	return $this->uploadDir;
    }
    function setUploadDir($uploadDir) {
    	if(strlen($uploadDir)>0){

    		//ルートと結合 ルートの末尾には/なし
    		if($uploadDir[0] != "/")$uploadDir = "/" . $uploadDir;
    		$uploadDir = SOY_INQUIRY_UPLOAD_ROOT_DIR . $uploadDir;

    		//相対パスを解釈:存在なければ×
    		$uploadDir = realpath($uploadDir);
    		$uploadDir = str_replace("\\","/",$uploadDir);

    		//末尾に/
    		if(strlen($uploadDir)>1 && @$uploadDir[strlen($uploadDir)-1] != "/")$uploadDir .= "/";

    		//ルートを削除：ルートより上位ディレクトリには保存できない
    		$uploadDir = str_replace(SOY_INQUIRY_UPLOAD_ROOT_DIR,"",$uploadDir);
    	}

    	$this->uploadDir = $uploadDir;
    }

    function getAdminUrl() {
    	if(strlen($this->adminUrl)<1){
    		$this->adminUrl = SOY2PageController::createLink("",true);
    	}
    	return $this->adminUrl;
    }
    function setAdminUrl($adminUrl) {
    	$this->adminUrl = $adminUrl;
    }

}
?>
