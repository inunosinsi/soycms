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
		$id = (is_numeric($entity->getId())) ? (int)$entity->getId() : 0;
		$elementID = "label_".$id;

		$this->addCheckBox("label_check", array(
			"name"=>"label[]",
			"value"=> $id,
			"selected"=>in_array($id, $this->selectedIds),
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
