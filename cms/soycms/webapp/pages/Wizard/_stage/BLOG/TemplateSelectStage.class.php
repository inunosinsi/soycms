<?php
class TemplateSelectStage extends StageBase{

    function TemplateSelectStage() {
    	parent::__construct();
    }
    
    function execute(){
    	$list = $this->run("Template.TemplateListAction")->getAttribute("list");
    	foreach($list as $key => $tmp){
    		if($tmp->getPageType() != $this->wizardObj->pageType){
    			unset($list[$key]);
    		}
    	}
    	
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
    	return "BLOG.PageConfigStage";
    	
    }
    
    function getBackObject(){
    	return "SelectTopStage";
    }
}

class TemplateList extends HTMLList{
	var $selected;
	function setSelected($selected){
		$this->selected = $selected;
	}
	function populateItem($entity){
		$this->createAdd("tempalte_radio","HTMLCheckBox",array(
			"value"=>$entity->getId(),
			"label"=>$entity->getName(),
			"selected"=>($entity->getId() == $this->selected)
		));
		
		$this->createAdd("description","HTMLLabel",array(
			"text"=>$entity->getDescription()
		));
	}
}
?>