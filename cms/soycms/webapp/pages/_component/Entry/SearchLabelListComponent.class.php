<?php

class SearchLabelListComponent extends HTMLList{

	private $selectedIds = array();

	public function setSelectedIds($ids){
		$this->selectedIds = $ids;
		if(!is_array($this->selectedIds)){
			$this->selectedIds = array();
		}
	}
	protected function populateItem($entity){

		$elementID = "label_".$entity->getId();

		$this->addCheckBox("label_check", array(
			"name"=>"label[]",
			"value"=>$entity->getId(),
			"selected"=>in_array($entity->getId(),$this->selectedIds),
			"elementId" => $elementID,
		));

		$this->addModel("label_label", array(
			"for"=>$elementID,
		));

		$this->addLabel("label_name", array(
			"text" => $entity->getCaption(),
			"style"=> "color:#" . sprintf("%06X",$entity->getColor()).";"
					 ."background-color:#" . sprintf("%06X",$entity->getBackgroundColor()).";"
		));

		$this->addImage("label_icon", array(
			"src" => $entity->getIconUrl()
		));
	}
}
