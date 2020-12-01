<?php

class SimplePagerComponent extends HTMLList{

	private $url;
	private $current;

	protected function populateItem($bean){

		$this->addLink("target_link", array(
			"text" => $bean,
			"link" => $this->url . "/" . $bean,
			"class" => ($this->current == $bean) ? "pager_current" : ""
		));
	}

	function getUrl() {
		return $this->url;
	}
	function setUrl($url) {
		$this->url = $url;
	}
	function getCurrent() {
		return $this->current;
	}
	function setCurrent($cuttent) {
		$this->current = $cuttent;
	}
}
