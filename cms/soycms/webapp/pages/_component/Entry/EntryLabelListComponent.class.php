<?php

class EntryLabelListComponent extends HTMLList{

	private $entryLabelIds = array();

	public function setEntryLabelIds($list){
		if(is_array($list)){
			$this->entryLabelIds = $list;
		}
	}

	protected function populateItem($label){
		$id = (is_numeric($label->getId())) ? (int)$label->getId() : 0;
		$this->addLink("entry_list_link", array(
			"link" => SOY2PageController::createLink("Entry.List.".$id),
			"text" => $label->getCaption(),
			"visible" => in_array($label->getId(), $this->entryLabelIds),
			"style"=> "color:#" . sprintf("%06X",$label->getColor()).";"
					 ."background-color:#" . sprintf("%06X",$label->getBackgroundColor()).";",
		));
	}
}
