<?php
class TemplateSelectStage extends StageBase{

    function TemplateSelectStage() {
    	parent::__construct();
    }
    
    function execute(){
    	$dao = SOY2DAOFactory::create("cms.TemplateDAO");
    	
    	$list = $dao->get($this->wizardObj->pageType,true);
    	
    	$this->createAdd("template_list","TemplateList",array(
    		"list"=>$list,
    		"selected"=>@$this->wizardObj->template_id
    	));
    	
    }
    
    function checkNext(){
    	if(isset($_POST["template_id"])){
    		$this->wizardObj->template_id = $_POST["template_id"];
    		return true;
    	}else{
    		$this->addErrorMessage("WIZARD_NO_SELECT_TEMPLATE");
    		return false;
    	}
    }
    
    function checkBack(){
    	return true;
    }
    
    function getNextObject(){
    	return "HTML.PageConfigStage";
    }
    
    function getBackObject(){
    	return "SelectTopStage";
    }
}

class TemplateDetailList extends HTMLList{
	var $selected;
	var $templateId;
	
	function setTemplateId($id){
		$this->templateId = $id;
	}
	
	function setSelected($selected){
		$this->selected = $selected;
	}
	function populateItem($entity){
		$this->createAdd("tempalte_radio","HTMLCheckBox",array(
			"value"=> $this->templateId . "/" . $entity["id"],
			"label"=>$entity["name"],
			"selected"=>(($this->templateId . "/" . $entity["id"]) == $this->selected)
		));
		
		$this->createAdd("description","HTMLLabel",array(
			"text"=>$entity["description"]
		));
	}
}

class TemplateList extends HTMLList{
	private $selected;
	
	function getSelected() {
		return $this->selected;
	}
	function setSelected($selected) {
		$this->selected = $selected;
	}
	
	function populateItem($entity){
		$this->createAdd("template_name","HTMLLabel",array(
			"text"=>$entity->getName()
		));
		$this->createAdd("template_description","HTMLLabel",array(
			"text"=>$entity->getDescription()
		));
		
		$this->createAdd("template_details","TemplateDetailList",array(
			"list"=>$entity->getTemplate(),
			"selected"=>$this->selected,
			"templateId"=>$entity->getId()		
		));
	}
}
?>