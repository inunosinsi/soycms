<?php

class TemplateDetailAction extends SOY2Action{

	private $id;
	
	function setId($id){
		$this->id = $id;
	}

    protected function execute(SOY2ActionRequest &$request,SOY2ActionForm &$form,SOY2ActionResponse &$response){
		
		if($request->getParameter("id")){
			$this->id = $request->getParameter("id");
		}
		
		if(is_null($this->id)){
			SOY2::import("domain.cms.EntryTemplate");
			$this->setAttribute("entity",new EntryTemplate());	
		}else{
			$logic = SOY2Logic::createInstance("logic.site.EntryTemplate.TemplateLogic");
			$this->setAttribute("entity",$logic->getById($this->id));
		}
		return SOY2Action::SUCCESS;
    }
}

?>