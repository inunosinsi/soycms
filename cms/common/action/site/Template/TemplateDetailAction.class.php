<?php

class TemplateDetailAction extends SOY2Action{

	private $id;
	

    protected function execute(SOY2ActionRequest &$request,SOY2ActionForm &$form,SOY2ActionResponse &$response){
		
		if(is_null($this->id)){
			SOY2::import("domain.cms.Template");
			$this->setAttribute("entity",new Template());	
		}else{
			$logic = SOY2Logic::createInstance("logic.site.Template.TemplateLogic");
			$this->setAttribute("entity",$logic->getById($this->id));
		}
		return SOY2Action::SUCCESS;
    }

    function getId() {
    	return $this->id;
    }
    function setId($id) {
    	$this->id = $id;
    }
}

?>