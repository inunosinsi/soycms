<?php

class ThreadLogic extends SOY2LogicBase{

    function __construct() {}
    
    function get(){
    	$trdao = SOY2DAOFactory::create("SOYBoard_ThreadDAO");
    	$redao = SOY2DAOFactory::create("SOYBoard_ResponseDAO");
    	
    	$list = $trdao->get();
    	foreach($list as $key => $data){
    		$row = $redao->getResponseNum($data->getId());
    		$list[$key]->setResponse($row["count"]);
    	}
    	
    	return $list;
    }
    
    function insert($arg,$pageId = null){
    	$trdao = SOY2DAOFactory::create("SOYBoard_ThreadDAO");
    	
    	$obj = new SOYBoard_Thread();
    	
    	$obj->setTitle($arg["title"]);
    	$obj->setOwner($arg["name"]);
    	$obj->setLastSubmitDate(date("Y-m-d H:i:s"));
    	$obj->setSortdate(date("Y-m-d H:i:s"));
    	$obj->setCdate(date("Y-m-d H:i:s"));
    	$obj->setPageId($pageId);
    	$obj->setReadonly(0);
    	return $trdao->insert($obj);
    	
    }
    
    function update($entity){
    	$dao = SOY2DAOFactory::create("SOYBoard_ThreadDAO");
    	return $dao->update($entity);
    }
    
    function getById($id){
    	$trdao = SOY2DAOFactory::create("SOYBoard_ThreadDAO");
    	$redao = SOY2DAOFactory::create("SOYBoard_ResponseDAO");
    	
    	try{
    		$ent = $trdao->getById($id);
    	}catch(Exception $e){
    		$ent = new SOYBoard_Thread();
    	}
    	
    	try{
    		$row = $redao->getResponseNum($id);
    	}catch(Exception $e){
    		$row["count"] = 0;
    	}
    	
    	$ent->setResponse($row["count"]);
    	return $ent;
    	
    }
    
    function getByPageId($pageId){
    	$trdao = SOY2DAOFactory::create("SOYBoard_ThreadDAO");
    	$redao = SOY2DAOFactory::create("SOYBoard_ResponseDAO");
    	
    	$ent = $trdao->getByPageId($pageId);
    	$row = $redao->getResponseNum($ent->getId());
    	$ent->setResponse($row["count"]);
    	return $ent;
    	
    }
    
    
    function updateLastUpdateDate($threadId){
    	$trdao = SOY2DAOFactory::create("SOYBoard_ThreadDAO");
    	$trdao->updateLastUpdateDate($threadId,date("Y-m-d H:i:s"));
    }
    
    function deleteById($id){
    	$trdao = SOY2DAOFactory::create("SOYBoard_ThreadDAO");
    	$redao = SOY2DAOFactory::create("SOYBoard_ResponseDAO");
    	$codao = SOY2DAOFactory::create("SOYBoard_ConfigDAO");
    	
    	$redao->deleteByThreadId($id);
    	$trdao->deleteById($id);
    	$codao->deleteByThreadId($id);
    	
    	
    	return;
    }
    
}
?>