<?php

class PluginCustomfieldSamplePage extends WebPage {

	private $entryId;

	function __construct(){}

	function execute(){
		parent::__construct();
	}

	function setEntryId($entryId){
		$this->entryId = $entryId;
	}
}