<?php

class EntryCSSAction extends SOY2Action{

    function execute() {
    	$dao = SOY2DAOFactory::create("cms.EntryTemplateDAO");
    	try{
    		$templates = $dao->get();
    	}catch(Exception $e){
    		return SOY2Action::FAILED;
    	}
    	
    	
    	
    	$result = array();
    	foreach($templates as $key => $template){
    		$result["entry_template_".$key] = array(
    			"id" => "entry_template_".$key,
    			"title" => $template->getName(),
				"filePath"=> SOY2PageController::createLink("EntryTemplate.CSS") . "?file=".rawurlencode($template->getFileName())
    		);
    	}
    	
    	$this->setAttribute("EntryCSS",$result);
    	return SOY2Action::SUCCESS;
    }
}
?>