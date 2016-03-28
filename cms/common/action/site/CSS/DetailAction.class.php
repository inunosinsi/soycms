<?php

class DetailAction extends SOY2Action{
	
	private $id;
	function setId($id){
		$this->id = $id;
	}
	
    protected function execute(SOY2ActionRequest &$request,SOY2ActionForm &$form,SOY2ActionResponse &$response){
		$logic = SOY2Logic::createInstance("logic.site.CSS.CSSLogic");
		try{
			$this->setAttribute("entity",$logic->getById($this->id));
			return SOY2Action::SUCCESS;
		}catch(Exception $e){
			$this->setAttribute("entity",null);
			return SOY2Action::FAILED;
		}
		
	}
}
?>