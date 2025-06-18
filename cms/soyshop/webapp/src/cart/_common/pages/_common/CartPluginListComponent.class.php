<?php
class CartPluginListComponent extends HTMLList{

	protected function populateItem($entity){
		$html = (isset($entity["html"]) && strlen((string)$entity["html"]) > 0) ? $entity["html"] : "";
		$err = (isset($entity["error"]) && strlen((string)$entity["error"]) > 0) ? $entity["error"] : "";

		$this->addModel("is_content", array(
			"visible" => (strlen($html))	
		));
	
		$this->addLabel("content", array(
			"html" => $html
		));

		$this->addLabel("error", array(
			"html" => $err,
			"visible" => (strlen($err) > 0)
		));

		if(!strlen($html) && !strlen($err)) return false;
	}
}
