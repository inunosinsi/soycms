<?php

class FailedStage extends StageBase{
	
	function FailedStage(){
		WebPage::WebPage();
	}
	
    function getNextString(){
    	return "";
    }
    
    function getBackString(){
    	return "";
    }
}

?>