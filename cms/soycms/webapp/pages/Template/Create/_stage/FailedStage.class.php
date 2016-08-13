<?php

class FailedStage extends StageBase{
	
	function FailedStage(){
		WebPage::__construct();
	}
	
    function getNextString(){
    	return "";
    }
    
    function getBackString(){
    	return "";
    }
}

?>