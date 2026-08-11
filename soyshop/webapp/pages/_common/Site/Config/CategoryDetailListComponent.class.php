<?php

class CategoryDetailListComponent extends HTMLList{

	private $config;
	private $pages;

	protected function populateItem($entity, $key){
		$id = (is_numeric($entity->getId())) ? (int)$entity->getId() : 0;
		
		$this->addModel("list_row", array(
			"attr:id" => "category_detail_" . $id
		));

		$this->addLabel("category_name", array(
			"text" => $entity->getName()
		));

		$config = (is_array($this->config) && count($this->config) && isset($this->config[$id])) ? $this->config[$id] : array();
		
		$this->addSelect("list_page_select", array(
			"selected" => (isset($config["id"])) ? $config["id"] : "",
			"options" => $this->pages,
			"property" => "name",
			"name" => "Config[".$id."][id]"
		));

		$this->addInput("page_parameter", array(
			"name" => "Config[".$id."][parameter]",
			"value" => (isset($config["parameter"])) ? $config["parameter"] : ""
		));

		$this->addInput("page_keyword", array(
			"name" => "Config[".$id."][keyword]",
			"value" => (isset($config["keyword"])) ? $config["keyword"] : ""
		));

		$this->addTextArea("page_description", array(
			"name" => "Config[".$id."][description]",
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