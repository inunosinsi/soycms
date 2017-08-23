<?php

class CreatePage extends CMSWebPageBase{
	
	var $labels;

    function CreatePage($arg) {
    	
    	$this->labels = $arg;
    	
    	$this->jump("Entry.Detail",array(
    		"initLabelList" => $arg
    	));
    	
    	exit;
    }
    
}
?>