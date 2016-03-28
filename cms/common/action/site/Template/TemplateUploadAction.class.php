<?php

class TemplateUploadAction extends SOY2Action{

    function execute($request,$form,$response){
    	
    	$files = $_FILES["template_pack"];
    	
    	$logic = SOY2Logic::createInstance("logic.site.Template.TemplateLogic");
    	
    	$logic->uploadTemplate($files);
    	
    	if(false){
    		return SOY2Action::SUCCESS;
    	}else{
    		return SOY2Action::FAILED;
    	}
    }
}
?>