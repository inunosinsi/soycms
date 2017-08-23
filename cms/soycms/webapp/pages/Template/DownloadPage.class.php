<?php

class DownloadPage extends CMSWebPageBase{

    function __construct($arg) {
//    	if(soy2_check_token()){
	    	parent::__construct();
	    	$id = @$arg[0];
	    	if(is_null($id)){
	    		$this->jump("Template");
	    		exit;
	    	}else{
		    	$result = SOY2ActionFactory::createInstance("Template.TemplateDownloadAction",array("id"=>$id))->run();
	    	}
//    	}else{
//	    		$this->jump("Template");
//    	}    	
    	exit;
    	
    }
}
?>