<?php

class LabelListComponent extends HTMLList{

	private $selectedLabelList = array();

	public function setIncludeParentTag($inc){
		$this->setAttribute("includeParentTag",$inc);
	}

	public function setSelectedLabelList($array){
		if(is_array($array)) $this->selectedLabelList = $array;
	}

	public function populateItem($entity){

		$elementID = "label_".$entity->getId();

		$this->addCheckBox("label_check", array(
			"name"	  => "label[]",
			"value"	 => $entity->getId(),
			"selected"  => in_array($entity->getId(),$this->selectedLabelList),
			"elementId" => $elementID,
			"onclick" => 'toggle_labelmemo(this.value,this.checked);'
		));
		$this->addModel("label_label", array(
			"for" => $elementID,
		));
		$this->addLabel("label_caption", array(
			"text" => $entity->getBranchName(),
			"style"=> "color:#" . sprintf("%06X",$entity->getColor()).";"
					 ."background-color:#" . sprintf("%06X",$entity->getBackgroundColor()).";",
			"title" => $entity->getBranchName(),
		));

		$this->addImage("label_icon", array(
			"src" => $entity->getIconUrl(),
			"alt" => $entity->getBranchName(),
			"title" => $entity->getBranchName(),
		));
	}
}