<?php

class AdvancedAddressListComponent extends HTMLList {

	function populateItem($entity){

		$this->addLabel("form", array(
			"html" => (isset($entity)) ? $entity : ""
		));
	}
}
