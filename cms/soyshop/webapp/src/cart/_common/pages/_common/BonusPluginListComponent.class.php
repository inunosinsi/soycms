<?php
class BonusPluginListComponent extends HTMLList{

	protected function populateItem($entity){

		$this->addLabel("content", array(
			"html" => (isset($entity["html"]) && strlen($entity["html"]) > 0) ? $entity["html"] : "",
			"visible" => (isset($entity["hasBonus"]) && $entity["hasBonus"])
		));
	}
}
