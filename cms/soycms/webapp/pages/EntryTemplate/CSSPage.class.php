<?php

class CSSPage extends CMSWebPageBase{
	
    function __construct() {
    	$result = SOY2ActionFactory::createInstance("EntryTemplate.TemplateDetailAction")->run();
    	$template = $result->getAttribute("entity");
		echo $template->getStyle();
    	exit;
    	
    }
}
?>