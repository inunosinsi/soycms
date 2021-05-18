<?php

class TemplateUploadAction extends SOY2Action{

    function execute($request,$form,$response){
    	$logic = SOY2Logic::createInstance("logic.site.EntryTemplate.TemplateLogic");

    	if($logic->uploadTemplate(@$_FILES["template_xml"])){
    		return SOY2Action::SUCCESS;
    	}else{
    		return SOY2Action::FAILED;
    	}
    }
}
