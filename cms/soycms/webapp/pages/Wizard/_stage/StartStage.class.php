<?php
class StartStage extends StageBase{

    function StartStage() {
    	parent::__construct();
    }
    
    function execute(){
    	
    }
    
    function checkNext(){
    	return true;
    }
    
    function checkBack(){
    	return true;
    }
    
    function getNextObject(){
    	return "SelectTopStage";
    }
    
    function getBackObject(){
    	return null;
    }
    
    function getBackString(){
    	return "";
    }
}
?>