<?php

class DeleteCategoryAction extends SOY2Action{

    function execute($request,$form,$response) {
    	if($form->category_name == CMSMessageManager::get("SOYCMS_NO_CATEGORY")){
    		return SOY2Action::FAILED;
    	}
    	$dao = SOY2DAOFactory::create("cms.PluginDAO");

    	$result = $dao->deletePluginCategory($form->category_name);
    	
    	if($result){
    		return SOY2Action::SUCCESS;
    	}else{
    		return SOY2Action::FAILED;	
    	}
    }
}

class DeleteCategoryActionForm extends SOY2ActionForm{
	var $category_name;
	
	function setCategory_name($name){
		$this->category_name = $name;
	}
}
