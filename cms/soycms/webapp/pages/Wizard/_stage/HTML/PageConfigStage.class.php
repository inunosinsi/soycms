<?php

class PageConfigStage extends StageBase{

    function PageConfigStage() {
    }
    
        
    function execute(){
    	parent::__construct();
    	
    	$this->createAdd("name","HTMLInput",array(
    		"name"=>"name",
    		"value"=>@$this->wizardObj->name
    	));	
    	
    	$this->createAdd("url_prefix","HTMLLabel",array(
    		"text"=>UserInfoUtil::getSiteUrl()
    	));
    	
    	$this->createAdd("url","HTMLInput",array(
    		"name"=>"url",
    		"value"=>@$this->wizardObj->url
    	));
    }
    
    function checkNext(){
    	$this->wizardObj->url = isset($_POST["url"])? $_POST["url"] : "";
    	$this->wizardObj->name = isset($_POST["name"])? $_POST["name"] : "";
    	return true;
    }
    
    function checkBack(){
    	return true;
    }
    
    function getNextObject(){
    	return "HTML.CreateStage";
    }
    
    function getBackObject(){
    	return "HTML.TemplateSelectStage";
    }
}
?>