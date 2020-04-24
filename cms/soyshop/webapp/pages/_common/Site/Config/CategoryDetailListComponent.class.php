<?php

class CategoryDetailListComponent extends HTMLList{

	private $config;
	private $pages;

	protected function populateItem($entity, $key){
		
		$this->addModel("list_row", array(
			"attr:id" => "category_detail_" . $entity->getId()
		));

		$this->addLabel("category_name", array(
			"text" => $entity->getName()
		));

		$config = @$this->config[$entity->getId()];
		if(!$config || !is_array($config)) $config = array();

		$this->addSelect("list_page_select", array(
			"selected" => @$config["id"],
			"options" => $this->pages,
			"property" => "name",
			"name" => "Config[" . $entity->getId() . "][id]"
		));

		$this->addInput("page_parameter", array(
			"name" => "Config[" . $entity->getId() . "][parameter]",
			"value" => (isset($config["parameter"])) ? $config["parameter"] : ""
		));

		$this->addInput("page_keyword", array(
			"name" => "Config[" . $entity->getId() . "][keyword]",
			"value" => (isset($config["keyword"])) ? $config["keyword"] : ""
		));

		$this->addTextArea("page_description", array(
			"name" => "Config[" . $entity->getId() . "][description]",
			"value" => (isset($config["description"])) ? $config["description"] : ""
		));
	}
	
	function getConfig() {
		return $this->config;
	}
	function setConfig($config) {
		$this->config = $config;
	}

	function getPages() {
		return $this->pages;
	}
	function setPages($pages) {
		$this->pages = $pages;
	}
}
?>