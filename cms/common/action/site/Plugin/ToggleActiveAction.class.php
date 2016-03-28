<?php

class ToggleActiveAction extends SOY2Action{

	private $pluginId;
	
	function setPluginId($pluginId) {
    	$this->pluginId = $pluginId;
    }
    

    function execute() {
    
    	if(!$this->pluginId){
    		return SOY2Action::FAILED;
    	}
    	$dao = SOY2DAOFactory::create("cms.PluginDAO");
    	$result = $dao->toggleActive($this->pluginId);
    	$this->setAttribute("new_state",$result);
    	
    	//キャッシュ再生成
    	CMSUtil::notifyUpdate();
    	
		return SOY2Action::SUCCESS;
    }

    
}
?>