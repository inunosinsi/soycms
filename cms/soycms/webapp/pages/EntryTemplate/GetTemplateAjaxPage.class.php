<?php

class GetTemplateAjaxPage extends CMSWebPageBase{

	function GetTemplateAjaxPage() {
    	$result = SOY2ActionFactory::createInstance("EntryTemplate.TemplateDetailAction")->run();
    	$template = $result->getAttribute("entity");
    	if(is_null($template)){
    		echo "";
    	}else{
    		echo json_encode(
    				array(
						"style_path"=>SOY2PageController::createLink("EntryTemplate.GetStyleAjax") . "/".$template->getId(),
						"templates"=>$template->getTemplates())
					);
    	}
    	
    	exit;
    }
}
?>