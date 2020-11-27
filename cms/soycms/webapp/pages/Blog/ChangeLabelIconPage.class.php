<?php

class ChangeLabelIconPage extends CMSWebPageBase{

    function __construct($args) {

    	$action = SOY2ActionFactory::createInstance("Label.ChangeLabelIconAction");
    	$result = $action->run();

    	if($result->success()){
    		$this->addMessage("LABEL_ICON_UPDATE_SUCCESS");
    	}else{
    		$this->addErrorMessage("LABEL_ICON_UPDATE_FAILED");
    	}

    	$this->jump("Blog.Category." . $args[0]);    	
    }
}
