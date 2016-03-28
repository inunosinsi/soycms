<?php

class GetCSSAction extends SOY2Action{

    function execute($request,$form,$response) {
    	try{
    		$this->setAttribute("css",file_get_contents($form->path));
    		return SOY2Action::SUCCESS;
    	}catch(Exception $e){
    		return SOY2Action::FAILED;
    	}
    	
    }
}

class GetCSSActionForm extends SOY2ActionForm{
	
	var $path;
	
	function setPath($path){
		$this->path = $path;
	}
	
}
?>