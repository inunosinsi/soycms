<?php

class CustomPlugin extends PluginBase{
	function executePlugin($soyValue){
		
		$this->setInnerHTML("<?php echo CMSPlugin::callCustomFieldFunctions('".$soyValue."'); ?>");
		
		return;
	}	
}
?>