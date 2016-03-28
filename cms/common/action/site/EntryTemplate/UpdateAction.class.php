<?php
SOY2::import("domain.cms.Template");
class UpdateAction extends SOY2Action{

    protected function execute(SOY2ActionRequest &$request,SOY2ActionForm &$form,SOY2ActionResponse &$response){
		if($form->hasError()){
			return SOY2Action::FAILED;
		}	
		$logic = SOY2Logic::createInstance("logic.site.EntryTemplate.TemplateLogic");
		$entity = SOY2::cast("EntryTemplate",$form);
		$entity->setId($form->template_id);
		
		if(strlen($form->template_id) == 0){
			//新規作成
			$return = $logic->insert($entity);
			$this->setAttribute("mode","create");
		}else{
			//update
			$return = $logic->update($entity);
			$this->setAttribute("mode","update");
		}
		if($return){
			return SOY2Action::SUCCESS;
		}else{
			return SOY2Action::FAILED;
		}
    }
}

class UpdateActionForm extends SOY2ActionForm{
	var $template_id = null;
	var $name;
	var $description;
	var $templates;
	var $labelRestrictionPositive = array();
	
	function setTemplate_id($template_id){
		$this->template_id = $template_id;
	}
	function setName($name) {
		$this->name = $name;
	}
	function setDescription($description) {
		$this->description = $description;
	}
	function setTemplates($template) {
		$this->templates = $template;
	}
	function setLabelRestrictionPositive($labelRestrictionPositive) {
		$this->labelRestrictionPositive = $labelRestrictionPositive;
	}
}
?>