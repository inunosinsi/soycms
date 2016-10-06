<?php

class ServerConfig {

	const SERVER_TYPE_SMTP = 0;
	const SERVER_TYPE_IMAP = 1;
	const SERVER_TYPE_SENDMAIL = 2;
	
	/* 実行方法 */
	const JOB_TYPE_EXEC = 1;//exec
	const JOB_TYPE_PHP = 2;//php
	
	/* 配信の種類 */
	const SEND_TYPE_SINGLE = 1;//一括
	const SEND_TYPE_SPLIT = 2;//分割
	
	//送信設定
    private $sendServerType = ServerConfig::SERVER_TYPE_SMTP;
    private $isUseSMTPAuth = true;
    private $sendServerAddress = "localhost";
    private $sendServerPort = 25;
    private $sendServerUser = "";
    private $sendServerPassword = "";
    private $sendType = ServerConfig::SEND_TYPE_SINGLE;//配信の種類
    private $sendRestriction = 200;//送信数制限
    private $sendRestrictionInterval = 20;//送信間隔
    private $isUseSSLSendServer = false;

    //受信設定
    private $receiveServerType = ServerConfig::SERVER_TYPE_SMTP;
    private $receiveServerAddress = "localhost";
    private $receiveServerPort = 110;
    private $receiveServerUser = "";
    private $receiveServerPassword = "";
    private $isUsePopBeforeSMTP = false;
    private $isUseSSLReceiveServer = false;

    //ジョブ設定
    private $jobInterval = 10;	//10 minute
    private $jobLastExecuteTime;
    private $jobNextExecuteTime;
    private $jobIsActived = 0;
    private $jobCurrentId = null;
    private $jobType = ServerConfig::JOB_TYPE_EXEC;
    private $phpPath;

    //管理者設定
    private $administratorName = "";
    private $administratorMailAddress = "";
    private $senderMailAddress  = "";
    private $returnMailAddress =  "";
    private $returnName = "";

    private $signature = "";
    
    //下書きの自動保存
    private $isAutoSave = false;


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
 
    public function getSendType() {
    	return $this->sendType;
    }
    public function setSendType($sendType) {
    	$this->sendType = $sendType;
    }
 
    function getSendRestriction(){
    	return $this->sendRestriction;
    }
    function setSendRestriction($sendRestriction){
    	$this->sendRestriction = $sendRestriction;
    }
    function getSendRestrictionInterval(){
    	return $this->sendRestrictionInterval;
    }
    function setSendRestrictionInterval($sendRestrictionInterval){
    	$this->sendRestrictionInterval = $sendRestrictionInterval;
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
    
    function getIsAutoSave(){
    	return $this->isAutoSave;
    }
    function setIsAutoSave($isAutoSave){
    	$this->isAutoSave = $isAutoSave;
    }

    /**#@+
     *
     * @access public
     */
    function getJobInterval() {
    	return $this->jobInterval;
    }
    function setJobInterval($jobInterval) {
    	$this->jobInterval = $jobInterval;
    }
    function getJobLastExecuteTime() {
    	return $this->jobLastExecuteTime;
    }
    function setJobLastExecuteTime($jobLastExecuteTime) {
    	$this->jobLastExecuteTime = $jobLastExecuteTime;
    }
    function getJobNextExecuteTime() {
    	return $this->jobNextExecuteTime;
    }
    function setJobNextExecuteTime($jobNextExecuteTime) {
    	$this->jobNextExecuteTime = $jobNextExecuteTime;
    }
    function getJobIsActived() {
    	return $this->jobIsActived;
    }
    function setJobIsActived($jobIsActived) {
    	$this->jobIsActived = $jobIsActived;
    }

    function getJobStatusText(){
    	$jobActiveText = ($this->getJobIsActived() == -1) ?	"実行中" : (
						 ($this->getJobIsActived() ==  1) ?	"有効"   :
						 									"無効"
						);
		return $jobActiveText;
    }
    /**#@-*/
    /**
     * 設定からSOY2Mailオブジェクトを生成する
     */
    function createReceiveServerObject(){

    	switch($this->receiveServerType){
    		case ServerConfig::SERVER_TYPE_SMTP:

    			$host = $this->getReceiveServerAddress();
    			if($this->getIsUseSSLReceiveServer())$host =  "ssl://" . $host . "/";

    			return SOY2Mail::create("pop",array(
    				"pop.host" => $host,
    				"pop.port" => $this->getReceiveServerPort(),
    				"pop.user" => $this->getReceiveServerUser(),
    				"pop.pass" => $this->getReceiveServerPassword()
    			));
    			break;
    		case ServerConfig::SERVER_TYPE_IMAP:
    		default:

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
    	}

    }

    /**
     * 設定からSOY2Mailオブジェクトを生成する
     */
    function createSendServerObject(){

    	switch($this->sendServerType){
    		case ServerConfig::SERVER_TYPE_SMTP:
    			$host = $this->getSendServerAddress();
    			if($this->getIsUseSSLSendServer())$host =  "ssl://" . $host . "/";

    			return SOY2Mail::create("smtp",array(
    				"smtp.host" => $host,
    				"smtp.port" => $this->getSendServerPort(),
    				"smtp.user" => $this->getSendServerUser(),
    				"smtp.pass" => $this->getSendServerPassword(),
    				"smtp.auth" => $this->getIsUseSMTPAuth()
    			));
    			break;
    		case ServerConfig::SERVER_TYPE_SENDMAIL:
    		default:
    			return SOY2Mail::create("sendmail",array());
    			break;
    	}

    }


    function getJobType() {
    	return $this->jobType;
    }
    function setJobType($jobType) {
    	$this->jobType = $jobType;
    }

    function getPhpPath() {
    	if(strlen($this->phpPath)<1){
    		
    		if(file_exists("/usr/bin/php")){
    			$this->phpPath = "/usr/bin/php";
    		}
    		
    		else if(file_exists("/usr/local/bin/php")){
    			$this->phpPath = "/usr/local/bin/php";
    		}
    		
    		else if(file_exists("/usr/local/php/bin/php")){
    			$this->phpPath = "/usr/local/php/bin/php";
    		}
    		
    		else{
    			$this->phpPath = "php";
    		}
    	}
    	
    	return $this->phpPath;
    }
    function setPhpPath($phpPath) {
    	$this->phpPath = $phpPath;
    }

}
?>