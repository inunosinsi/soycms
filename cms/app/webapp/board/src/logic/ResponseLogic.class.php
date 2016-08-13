<?php

class ResponseLogic extends SOY2LogicBase{

    function __construct() {}
    
    function insert($threadId,$arg){
    	
    	$dao = SOY2DAOFactory::create("SOYBoard_ResponseDAO");
    	$last = $dao->getLastResponseId($threadId);
    	$resId = $last["last"] + 1;

    	$obj = new SOYBoard_Response();
    	$obj->setThreadId($threadId);
    	$obj->setName($arg["name"]);
    	$obj->setEmail($arg["email"]);
    	$obj->setSubmitdate(date("Y-m-d H:i:s"));
    	$obj->setResponseId($resId);
    	
    	mt_srand(date("Ymd"));
    	
    	$salt = mt_rand();
    	
    	$hash = crypt($salt,md5($_SERVER["REMOTE_ADDR"]));
    	$obj->setHash($hash);
    	$obj->setBody($arg["body"]);
    	$obj->setHost(gethostbyaddr($_SERVER["REMOTE_ADDR"]));
    	
    	return $dao->insert($obj);
    	
    	
    }
    
    function getByThreadId($threadId,$offset = 1 , $viewcount = 100){
    	$dao = SOY2DAOFactory::create("SOYBoard_ResponseDAO");
    	return $dao->getByThreadId($threadId,$offset,$viewcount);
    }
    
    function delete($threadId,$responseId){
    	$dao = SOY2DAOFactory::create("SOYBoard_ResponseDAO");
    	return $dao->delete($threadId,$responseId);
    }
}
?>