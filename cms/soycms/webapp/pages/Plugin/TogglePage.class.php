<?php

class TogglePage extends CMSWebPageBase{

    function TogglePage() {
    	if(soy2_check_token()){
    	
	    	$this->id = implode("",array_keys($_GET));
	    	$result = $this->run("Plugin.ToggleActiveAction",array("pluginId"=>$this->id));
	    	if($result->success()){
	    		$new_state = $result->getAttribute("new_state");
	    		if($new_state){
	    			$this->addMessage("PLUGIN_ACTIVATION_SUCCESS");
	    		}else{
	    			$this->addMessage("PLUGIN_INACTIVATION_SUCCESS");
	    		}
	    	}else{
	    		$this->addErrorMessage("PLUGIN_ACTIVATION_FAILED");
	    	}
    	}
    	$this->jump("Plugin");
    	
    }
}
?>