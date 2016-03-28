<?php


class DeleteAction extends SOY2Action{
	
	var $id;

	function setId($id){
		$this->id = $id;
	}
	
    protected function execute(SOY2ActionRequest &$request,SOY2ActionForm &$form,SOY2ActionResponse &$response){
		
		if(!UserInfoUtil::isDefaultUser()) return SOY2Action::FAILED;
		
		$logic = SOY2Logic::createInstance("logic.admin.Administrator.AdministratorLogic");
		//初期ユーザーは削除できない
		if($logic->checkDefaultUser($this->id)){
			return SOY2Action::FAILED;	
		}

		if($logic->deleteAdministrator($this->id)){
			return SOY2Action::SUCCESS;	
		}else{
			return SOY2Action::FAILED;	
		}
		    
    }

    
}

?>