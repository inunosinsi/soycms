<?php

class SocialLoginListComponent extends HTMLList {

	function populateItem($entity, $key, $index){

		$this->addLabel("button", array(
			"html" => (isset($entity)) ? $entity : ""
		));
	}
}
