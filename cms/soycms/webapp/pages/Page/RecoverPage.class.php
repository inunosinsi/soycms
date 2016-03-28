<?php

class RecoverPage extends CMSWebPageBase{

    function RecoverPage($args) {

    	if(soy2_check_token()){
		
			$id = $args[0];
			
			$result = $this->run("Page.DetailAction",array("id"=>$id));
			
			if(!$result->success()){
				$this->addErrorMessage("PAGE_RECOVER_FAILED");
				$this->jump("Page");
			}
			
			$page = $result->getAttribute("Page");
			
			
			if($page->getPageType() != Page::PAGE_TYPE_ERROR){		
				$action = SOY2ActionFactory::createInstance("Page.RecoverAction",array("id"=>$id));
				$result = $action->run();
			}		
	
			$this->addMessage("PAGE_RECOVER_SUCCESS");
    	}else{
			$this->addErrorMessage("PAGE_RECOVER_FAILED");
    	}
    	
		$this->jump("Page");
	}
}
?>