<?php

class TopPageNoticeListComponent extends HTMLList {

	private $mode;

	protected function populateItem($entity){
		$this->addLabel("wording", array(
			"html" => (isset($entity["wording"])) ? $entity["wording"] : ""
		));

		$class = "alert alert-" . $this->mode;
		if(isset($entity["always"]) && is_bool($entity["always"]) && $entity["always"] == true) $class .= " always";
		$this->addLabel("always", array(
			"text" => $class
		));
	}

	function setMode($mode){
		$this->mode = $mode;
	}
}
