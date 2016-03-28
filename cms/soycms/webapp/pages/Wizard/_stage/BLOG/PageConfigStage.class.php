<?php

class PageConfigStage extends StageBase{

    function PageConfigStage() {
    }
    
        
    function execute(){
    	WebPage::WebPage();
    	
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
    	
    	if(isset($_POST["name"]) && strlen($_POST["name"]) != 0){
    		$this->wizardObj->name = isset($_POST["name"])? $_POST["name"] : "";
    		return true;
    	}else{
    		$this->addErrorMessage("WIZARD_BLOG_NAME_EMPTY");
    		return false;
    	}
    	
    }
    
    function checkBack(){
    	return true;
    }
    
    function getNextObject(){
    	return "BLOG.CreateStage";
    }
    
    function getBackObject(){
    	return "BLOG.TemplateSelectStage";
    }
}
?>