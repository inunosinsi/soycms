<?php

class PluginListAction extends SOY2Action{

	private $state;
	
	function setState($state){
		$this->state = $state;
	}

    function execute() {
    	$dao = SOY2DAOFactory::create("cms.PluginDAO");
    	try{
			if(is_null($this->state)){
				$plugins = $dao->get();
			}else if($this->state){
				$plugins = $dao->getCategorizedPlugins();
    		}else{
    			$plugins = $dao->getNonActives();
    		}
			$this->setAttribute("plugins",$plugins);
			return SOY2Action::SUCCESS;
    	}catch(Exception $e){
	    	return SOY2Action::FAILED;
    	}
    }
}
?>