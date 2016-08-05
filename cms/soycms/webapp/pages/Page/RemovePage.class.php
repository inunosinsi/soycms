<?php

class RemovePage extends CMSWebPageBase{

    function __construct($args) {
    	if(soy2_check_token()){
			$id = $args[0];
			
			$result = $this->run("Page.DetailAction",array("id"=>$id));
			
			if(!$result->success()){
				$this->addErrorMessage("PAGE_REMOVE_FAILED");
				$this->jump("Page");
			}
			
			$page = $result->getAttribute("Page");
			
			
			if($page->isDeletable()){		
				//$dao->delete($id);
				$action = SOY2ActionFactory::createInstance("Page.RemoveAction",array("id"=>$id));
				$result = $action->run();
				$this->addMessage("PAGE_REMOVE_SUCCESS");
			}else{
				$this->addMessage("PAGE_REMOVE_FAILED");
			}
    	}else{
			$this->addMessage("PAGE_REMOVE_FAILED");
    	}
		
		$this->jump("Page");
	}
}
?>