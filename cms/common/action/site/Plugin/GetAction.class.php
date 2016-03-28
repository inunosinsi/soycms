<?php

class GetAction extends SOY2Action{

	private $pluginId;
	
	function setPluginId($pluginId){
		$this->pluginId = $pluginId;
	}

    function execute() {
    	if(!$this->pluginId){
    		return SOY2Action::FAILED;
    	}
    	
    	$dao = SOY2DAOFactory::create("cms.PluginDAO");
    	$plugin = $dao->getById($this->pluginId);
    	$this->setAttribute("plugin",$plugin);
    	
    	return SOY2Action::SUCCESS;
    }
}
?>