<?php

class LabelListsComponent extends HTMLList{

	private $pageId;

	function populateItem($entity, $key){
		$this->addLabel("category_name", array(
			"text" => $key,
			"visible" => !is_int($key) && strlen($key),
		));
		$this->createAdd("list","_component.Blog.CategoryListComponent",array(
			"list" => $entity,
			"pageId" => $this->pageId
		));

		return ( count($entity) > 0 );
	}

	function setPageId($pageId){
		$this->pageId = $pageId;
	}
}
