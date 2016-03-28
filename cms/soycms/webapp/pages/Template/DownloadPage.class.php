<?php

class DownloadPage extends CMSWebPageBase{

    function DownloadPage($arg) {
//    	if(soy2_check_token()){
	    	WebPage::WebPage();
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