<?php

class CustomPage extends CMSWebPageBase{

	private $html;

    function __construct() {
    	parent::__construct();
    	
    	
    	$result = SOY2ActionFactory::createInstance("Plugin.CustomPageAction")->run();
    	
    	if($result->success()){
    		$this->html = $result->getAttribute("html");
    	}else{
    		$this->html = 'error';
    	}
    	
    	$this->createAdd("custom_menu","HTMLLabel",array("html"=>$this->html));
    	
    	
    }
}
?>