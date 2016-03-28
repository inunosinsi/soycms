<?php

class TemplateRemoveAction extends SOY2Action{

	private $id;
	
	function setId($id){
		$this->id = $id;
	}

    function execute(SOY2ActionRequest &$request,SOY2ActionForm &$form,SOY2ActionResponse &$response) {
    	if(is_null($this->id)){
			return SOY2Action::FAILED;	
		}else{
			$logic = SOY2Logic::createInstance("logic.site.EntryTemplate.TemplateLogic");
			if($logic->deleteById($this->id)){
				return SOY2Action::SUCCESS;
			}else{
				return SOY2Action::FAILED;
			}
		}
    }
}
?>