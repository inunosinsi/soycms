<?php

class PutTrashPage extends CMSWebPageBase{

	function PutTrashPage($args) {
		
    	if(soy2_check_token()){
			$id = $args[0];
			
			$result = $this->run("Page.DetailAction",array("id"=>$id));
			
			if(!$result->success()){
				$this->addErrorMessage("PAGE_TRASH_FAILED");
				$this->jump("Page");
			}
			
			$page = $result->getAttribute("Page");
			if($page->isDeletable()){		
				$action = SOY2ActionFactory::createInstance("Page.PutTrashAction",array("id"=>$id));
				$result = $action->run();
				$this->addMessage("PAGE_TRASH_SUCCESS");
			}else{
				$this->addMessage("PAGE_TRASH_FAILED");
			}
    	}else{
			$this->addMessage("PAGE_TRASH_FAILED");
    	}
    		
		$this->jump("Page");
		
	}
}
?>