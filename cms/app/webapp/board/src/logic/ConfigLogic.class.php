<?php

class ConfigLogic extends SOY2LogicBase{

    function __construct() {}
    
    function init($threadId){
    	$dao = SOY2DAOFactory::create("SOYBoard_ConfigDAO");
    	$entity = new SOYBoard_Config();
    	$entity->setThreadId($threadId);
    	$entity->setDefaultName("名無し");
    	$entity->setIsStopped(0);
    	$entity->setMaxResponse(1000);
    	$entity->setShowHost(0);
    	$entity->setSageAccept(1);

		try{
	   		$threadId = $dao->insert($entity);
    	}catch(EXception $e){
					
    	}
    	
    	return $entity;
    }
    
    function getByThreadId($threadId){
    	$dao = SOY2DAOFactory::create("SOYBoard_ConfigDAO");
    	try{
    		$config = $dao->getByThreadId($threadId);
    		return $config;
    	}catch(Exception $e){
     		$this->init($threadId);
    		try{
    			$config = $dao->getByThreadId($threadId);
    			return $config;
    		}catch(Exception $e){
    			throw $e;
    		}
    	}
    }
    
    function update($entity){
    	$dao = SOY2DAOFactory::create("SOYBoard_ConfigDAO");
    	$entity->setShowHost(0);
    	$entity->setSageAccept(1);
    	$threadId = $entity->getThreadId();
    	
    	return $dao->updateByThreadId($threadId, $entity);
    }
}
?>