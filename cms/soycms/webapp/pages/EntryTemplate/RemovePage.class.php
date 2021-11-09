<?php
class RemovePage extends CMSWebPageBase{

	function __construct($args) {
		if(!isset($args[0]) || !is_numeric($args[0])){
			$this->jump("EntryTemplate");
			exit;
		}

		if(soy2_check_token()){
			parent::__construct();
			if(SOY2ActionFactory::createInstance("EntryTemplate.TemplateRemoveAction",array("id"=>(int)$args[0]))->run()->success()){
				$this->addMessage("ENTRY_TEMPLATE_REMOVE_SUCCESS");
			}else{
				$this->addMessage("ENTRY_TEMPLATE_REMOVE_FAILED");
			}
		}
		$this->jump("EntryTemplate");

	}
}
