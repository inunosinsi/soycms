<?php
SOY2::import("action.admin.Administrator.AdministratorActionForm");

class CreateAction extends SOY2Action{

    protected function execute(SOY2ActionRequest &$request,SOY2ActionForm &$form,SOY2ActionResponse &$response){

		if(!UserInfoUtil::isDefaultUser()) return SOY2Action::FAILED;

		if($form->hasError()){
			foreach($form as $key => $value){
				if($form->isError($key)){
					$this->setErrorMessage($key,$form->getErrorString($key));
				}
			}
			return SOY2Action::FAILED;
		}


		$logic = SOY2Logic::createInstance("logic.admin.Administrator.AdministratorLogic");
		if($logic->checkUserId($form->userId)){
			$result = $logic->createAdministrator($form->userId, $form->password, false, $form->name, $form->email);
		}else{
			return SOY2Action::FAILED;
		}

		if($result === false){
			return SOY2Action::FAILED;
		}

		$this->setAttribute("id",$logic->getId());

		return SOY2Action::SUCCESS;

    }

    function getActionFormName(){
    	return "AdministratorActionForm";
    }

}

?>