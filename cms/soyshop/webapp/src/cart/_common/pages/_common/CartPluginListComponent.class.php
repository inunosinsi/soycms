<?php
class CartPluginListComponent extends HTMLList{

	protected function populateItem($entity){

		$this->addLabel("content", array(
			"html" => (isset($entity["html"]) && strlen($entity["html"]) > 0) ? $entity["html"] : ""
		));

		$this->addLabel("error", array(
			"html" => (isset($entity["error"]) && strlen($entity["error"]) > 0) ? $entity["error"] : "",
			"visible" => (isset($entity["error"]) && strlen($entity["error"]) > 0)
		));
	}
}
