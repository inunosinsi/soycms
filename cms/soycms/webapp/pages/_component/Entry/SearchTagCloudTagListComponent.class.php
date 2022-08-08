<?php

class SearchTagCloudTagListComponent extends HTMLList{

	private $conditions = array();
	
	protected function populateItem($entity){
		$tagId = (is_numeric($entity->getId())) ? (int)$entity->getId() : 0;
		$cls = "btn tag_cloud_anchor ";
		$cls .= (is_numeric(array_search($tagId, $this->conditions))) ? "btn-primary" : "btn-default";
		$this->addLink("tag", array(
			"link" => "javascript:void(0);",
			"text" => $entity->getWord(),
			"attr:id" => "tag_cloud_" . $tagId,
			"attr:class" => $cls,
			"onclick" => "toggle_on_tag_cloud(" . $tagId . ")"
		));
	}

	function setConditions($conditions){
		$this->conditions = $conditions;
	}
}