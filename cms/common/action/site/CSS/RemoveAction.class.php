<?php

class RemoveAction extends SOY2Action{

	private $id;
	function setId($id){
		$this->id = $id;
	}
	
    protected function execute(SOY2ActionRequest &$request,SOY2ActionForm &$form,SOY2ActionResponse &$response){
		if(is_null($this->id)){
			return SOY2Action::FAILED;
		}
		
		$logic = SOY2Logic::createInstance("logic.site.CSS.CSSLogic");
		try{
			$logic->delete($this->id);
			return SOY2Action::SUCCESS;
		}catch(Exception $e){
			return SOY2Action::FAILED;
		}
		
		
	}
}
?>