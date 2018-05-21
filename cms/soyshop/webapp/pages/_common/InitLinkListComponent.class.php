<?php

class InitLinkListComponent extends HTMLList {

	protected function populateItem($entity){
		$this->addLink("init_link", array(
			"link" => (isset($entity["link"])) ? $entity["link"] : "",
			"text" => (isset($entity["label"])) ? $entity["label"] : ""
		));
	}
}
