<?php

/**
 * 使わない?
 */
class RemovePage extends CMSEntryEditorPageBase{

	private $id;

	function doPost(){
    	SOY2ActionFactory::createInstance("Entry.RemoveAction",array(
    		"id" => $this->id
    	))->run();
		$this->jump("Entry");
	}

    function __construct($args) {
		$this->id = @$args[0];

		parent::__construct();
    }
}
