<?php

class NotCompatibleFileListComponent extends HTMLList {

	protected function populateItem($entity, $key){

		$this->addLabel("filepath", array(
			"text" => (is_string($entity)) ? SOYSHOP_SITE_DIRECTORY . ".page/" . $entity . ".php" : ""
		));
	}
}
