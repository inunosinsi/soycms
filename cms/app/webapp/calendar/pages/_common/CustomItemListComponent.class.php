<?php

class CustomItemListComponent extends HTMLList{

	protected function populateItem($entity){
		$id = (is_numeric($entity->getId())) ? $entity->getId() : 0;

		$this->addLabel("id", array(
			"text" => $id
		));
		$this->addLabel("label", array(
			"text" => $entity->getLabel()
		));
		$this->addLabel("alias", array(
			"text" => $entity->getAlias()
		));

		$this->addLink("edit_link", array(
			"link" => SOY2PageController::createLink(APPLICATION_ID.".Schedule.Custom.Detail.".$id)
		));

		$this->addActionLink("remove_link", array(
			"link" => SOY2PageController::createLink(APPLICATION_ID.".Schedule.Custom.Remove.".$id),
			"onclick" => "return confirm('削除してもよろしいですか？');"
		));
	}
}
