<?php

class UploadExtensionListComponent extends HTMLList {

	protected function populateItem($entity, $moduleId) {

		$this->addLink("detail_link", array(
			"link" => SOY2PageController::createLink("Order.Upload." . $moduleId),
			"text" => (isset($entity)) ? $entity : ""
		));
	}
}
