<?php

class CSSPage extends CMSWebPageBase{

    function __construct($arg) {
    	$id = @$arg[0];
    	$entry = $this->getEntryInformation($id);

    	$style = $entry->getStyle();
    	header("Content-Type: text/css; charset=utf-8");
    	if(strlen($style)){
	    	echo $style;
    	}else{
    		header("HTTP/1.1 404 Not Found");
    	}
    	exit;
    }

    function getEntryInformation($id){
    	if(is_null($id)){
    		return SOY2DAOFactory::create("cms.Entry");
    	}

    	$action = SOY2ActionFactory::createInstance("Entry.EntryDetailAction",array("id"=>$id));
    	$result = $action->run();
    	if($result->success()){
    		return $result->getAttribute("Entry");
    	}else{
    		return new Entry();
    	}

    }
}
