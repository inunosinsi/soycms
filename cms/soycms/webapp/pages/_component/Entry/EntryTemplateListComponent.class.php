<?php

class EntryTemplateListComponent extends HTMLList{

	private $labels;

	protected function populateItem($entity){
		$id = (is_numeric($entity->getId())) ? (int)$entity->getId() : 0;
		$this->addLink("title", array(
			"text" => (strlen($entity->getName()) == 0) ? CMSMessageManager::get("SOYCMS_NO_TITLE") :$entity->getName(),
			"link" => SOY2PageController::createLink("EntryTemplate.Detail.").$id
		));

		$this->addLabel("description", array(
			"text" => strip_tags($entity->getDescription())
		));

		$this->addLink("modify_link", array(
			"link"=>SOY2PageController::createLink("EntryTemplate.Detail.").$id
		));
		$this->addActionLink("remove_link", array(
			"link"=>SOY2PageController::createLink("EntryTemplate.Remove.").$id
		));

		$labelId = (is_numeric($entity->getLabelId())) ? (int)$entity->getLabelId() : 0;
		$this->addLabel("label", array(
			"text" => (isset($this->labels[$labelId])) ? $this->labels[$labelId]->getCaption() : "-"
		));
	}

	function setLabels($labels) {
		$this->labels = $labels;
	}
}
