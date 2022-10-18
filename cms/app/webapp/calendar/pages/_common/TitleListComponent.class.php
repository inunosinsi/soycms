<?php

class TitleListComponent extends HTMLList{

	protected function populateItem($entity){
		$id = (is_numeric($entity->getId())) ? $entity->getId() : 0;

		$this->addLabel("id", array(
			"text" => $id
		));
		$this->addLabel("title", array(
			"text" => $entity->getTitle()
		));
		$this->addLabel("attribute", array(
			"text" => $entity->getAttribute()
		));

		$this->addLink("edit_link", array(
			"link" => SOY2PageController::createLink(APPLICATION_ID.".Title.Detail.".$id)
		));

		$this->addActionLink("remove_link", array(
			"link" => SOY2PageController::createLink(APPLICATION_ID.".Title.Remove.".$id),
			"onclick" => "return confirm('削除してもよろしいですか？');"
		));
	}
}
