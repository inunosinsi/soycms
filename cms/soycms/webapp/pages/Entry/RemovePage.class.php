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

    function RemovePage($args) {
		$this->id = @$args[0];
		
		WebPage::WebPage();
    }
}
?>