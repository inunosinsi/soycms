<?php

class ToggleActiveAction extends SOY2Action{

	private $pluginId;
	
	function setPluginId($pluginId) {
    	$this->pluginId = $pluginId;
    }
    

    function execute() {
    	if(!$this->pluginId) return SOY2Action::FAILED;
    	$this->setAttribute("new_state", soycms_get_hash_table_dao("plugin")->toggleActive($this->pluginId));
    	
    	//キャッシュ再生成
    	CMSUtil::notifyUpdate();
    	
		return SOY2Action::SUCCESS;
    }    
}