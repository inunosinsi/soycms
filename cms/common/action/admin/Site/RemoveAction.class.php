<?php

class RemoveAction extends SOY2Action{

	var $id;
	var $deleteDatabase = false;
	var $deleteDir = false;

	function setId($id){
		$this->id = $id;
	}
	function setDeleteDatabase($val){
		$this->deleteDatabase = $val;
	}
	function setDeleteDir($val){
		$this->deleteDir = $val;
	}

    protected function execute(SOY2ActionRequest &$request,SOY2ActionForm &$form,SOY2ActionResponse &$response){
		//一般管理者はサイト作れない
		if(!UserInfoUtil::isDefaultUser()) return SOY2Action::FAILED;


		$logic = SOY2Logic::createInstance("logic.admin.Site.SiteLogic");

		$site = $logic->getById($this->id);
		if($site->getIsDomainRoot()){
			return SOY2Action::FAILED;
		}

		if(!$logic->removeSite($this->id,$this->deleteDatabase,$this->deleteDir)){
			return SOY2Action::FAILED;
		}

		return SOY2Action::SUCCESS;
    }

}


?>