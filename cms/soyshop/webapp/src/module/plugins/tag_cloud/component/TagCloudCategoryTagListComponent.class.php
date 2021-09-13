<?php

class TagCloudCategoryTagListComponent extends HTMLList {

	protected function populateItem($entity, $key){
		$this->addLink("tag", array(
			"link" => "javascript:void(0)",
			"text" => $entity->getWord(),
			"id" => (is_numeric($entity->getId())) ? "tag_" . $entity->getId() : ""
		));
	}
}
