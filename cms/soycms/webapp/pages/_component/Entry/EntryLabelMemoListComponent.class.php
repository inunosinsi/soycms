<?php

class EntryLabelMemoListComponent extends HTMLList{

	private $selectedLabelList = array();

	public function setSelectedLabelList($array){
		if(is_array($array)) $this->selectedLabelList = $array;
	}

	public function populateItem($entity){
		$this->addLabel("entry_label_memo",array(
				"id" => "entry_label_memo_".$entity->getId(),
				"text" => $entity->getCaption(),
				"title" => $entity->getDescription(),
				"style"=> ( in_array($entity->getId(),$this->selectedLabelList) ? "" : "display:none;" )."color:#" . sprintf("%06X",$entity->getColor()).";background-color:#" . sprintf("%06X",$entity->getBackgroundColor()) . ";"
		));
	}
}