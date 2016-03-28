<?php
class UpdatePasswordAction extends SOY2Action{

    protected function execute(SOY2ActionRequest &$request,SOY2ActionForm &$form,SOY2ActionResponse &$response){

		$this->setAttribute("form",$form);

		if($form->hasError()){
			foreach($form as $key => $value){
				if($form->isError($key)){
					$this->setErrorMessage($key,$form->getErrorString($key));
				}
			}
			return SOY2Action::FAILED;
		}

		//他人のパスワードを変更できるのは初期管理者のみ
		if(!UserInfoUtil::isDefaultUser()){
			return SOY2Action::FAILED;
		}

		//変更
		$logic = SOY2Logic::createInstance("logic.admin.Administrator.AdministratorLogic");
		if($logic->updateAdministratorPassword($form->administratorId,$form->newPassword)){
			return SOY2Action::SUCCESS;
		}

		return SOY2Action::FAILED;
	}
}

class UpdatePasswordActionForm extends SOY2ActionForm{
	var $administratorId;//変更する管理者のAdministrator.id
	var $newPassword; //新しいパスワード


	function getAdministratorId(){
		return $this->administratorId;
	}

	/**
     * @validator string { "require" : true }
	 */
	function setAdministratorId($administratorId){
		$this->administratorId = $administratorId;
	}

	function getNewPassword() {
		return $this->newPassword;
	}

	/**
     * @validator string {"max" : 30, "min" : 6, "require" : true }
     */
	function setNewPassword($newPassword) {
		$this->newPassword = $newPassword;
	}
}

?>