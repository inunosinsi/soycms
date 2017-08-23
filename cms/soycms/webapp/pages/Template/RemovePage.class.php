<?php
class RemovePage extends CMSWebPageBase{

	function __construct($arg) {

    	if(soy2_check_token()){
	    	parent::__construct();
	    	$id = @$arg[0];
	    	if(is_null($id)){
	    		$this->jump("Template");
	    		exit;
	    	}
	    	$result = SOY2ActionFactory::createInstance("Template.TemplateRemoveAction",array("id"=>$id))->run();
	    	if($result->success()){
	    		$this->addMessage("PAGE_TEMPLATE_REMOVE_SUCCESS");
	    	}else{
	    		$this->addMessage("PAGE_TEMPLATE_REMOVE_FAILED");
	    	}
    	}else{
    		$this->addMessage("PAGE_TEMPLATE_REMOVE_FAILED");
    	}

    	$this->jump("Template");
    	
    }
}
?>