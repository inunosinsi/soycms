<?php

class PutTrashPage extends CMSWebPageBase{

	protected $pageToGoBack = "Page";

	function __construct($args) {
		$id = $args[0];

		if(soy2_check_token() && $this->revocer($id)){
			$this->addMessage("PAGE_TRASH_SUCCESS");
		}else{
			$this->addMessage("PAGE_TRASH_FAILED");
		}

		$this->jump($this->pageToGoBack);
	}

	private function revocer($id){

		$result = $this->run("Page.DetailAction",array("id"=>$id));
		if(!$result->success()){
			return false;
		}

		if(!$result->getAttribute("Page")->isDeletable()){
			return false;
		}

		$action = SOY2ActionFactory::createInstance("Page.PutTrashAction",array("id"=>$id));
		$result = $action->run();

		if($result->success()){
			return true;
		}else{
			return false;
		}

	}
}
