<?php

class RemovePage extends CMSWebPageBase{

	protected $pageToGoBack = "Page";

	function __construct($args) {
		$id = $args[0];

		if(soy2_check_token() && $this->remove($id)){
			$this->addMessage("PAGE_REMOVE_SUCCESS");
		}else{
			$this->addErrorMessage("PAGE_REMOVE_FAILED");
		}

		$this->jump($this->pageToGoBack);
	}

	private function remove($id){

		$result = $this->run("Page.DetailAction",array("id"=>$id));
		if(!$result->success()){
			return false;
		}

		if(!$result->getAttribute("Page")->isDeletable()){
			return false;
		}

		$action = SOY2ActionFactory::createInstance("Page.RemoveAction",array("id"=>$id));
		$result = $action->run();

		if($result->success()){
			return true;
		}else{
			return false;
		}

	}

}
