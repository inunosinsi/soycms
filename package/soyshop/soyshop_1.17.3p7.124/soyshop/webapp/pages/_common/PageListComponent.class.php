<?php

class PageListComponent extends HTMLList{

	protected function populateItem($entity){

		$this->addLabel("update_date", array(
			"text" => print_update_date($entity->getUpdateDate())
		));

		$this->addLabel("name", array(
			"text" => $entity->getName()
		));

		$this->addLink("uri", array(
			"text" => "/" . $entity->getUri(),
			"link" => SOYSHOP_SITE_URL . $entity->getUri(),
			"target" => "_blank"
		));

		$this->addLabel("type_text", array(
			"text" => $entity->getTypeText()
		));

		$this->addLink("detail_link", array(
			"link" => SOY2PageController::createLink("Site.Pages.Detail." . $entity->getId())
		));

		$this->addActionLink("remove_link", array(
			"link" => SOY2PageController::createLink("Site.Pages.Remove." . $entity->getId())
		));
	}
}

?>