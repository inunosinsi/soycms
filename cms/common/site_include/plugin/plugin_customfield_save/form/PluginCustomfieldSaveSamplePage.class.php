<?php

class PluginCustomfieldSaveSamplePage extends WebPage {

	private $entryId;

	function __construct(){}

	function execute(){
		parent::__construct();

		$this->createAdd("hoge", "HTMLInput", array(
			"name" => "hoge",
			"value" => soycms_get_entry_attribute_value($this->entryId, "custom_hoge")
		));
	}

	function setEntryId($entryId){
		$this->entryId = $entryId;
	}
}