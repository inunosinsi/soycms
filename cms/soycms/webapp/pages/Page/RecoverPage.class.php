<?php

class RecoverPage extends CMSWebPageBase{

	protected $pageToGoBack = "Page";

	function __construct($args) {
		$id = $args[0];

		if(soy2_check_token() && $this->revocer($id)){
			$this->addMessage("PAGE_RECOVER_SUCCESS");
		}else{
			$this->addErrorMessage("PAGE_RECOVER_FAILED");
		}

		$this->jump($this->pageToGoBack);
	}

	private function revocer($id){

		$action = SOY2ActionFactory::createInstance("Page.RecoverAction",array("id"=>$id));
		$result = $action->run();

		if($result->success()){
			return true;
		}else{
			return false;
		}

	}
}
