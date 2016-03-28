<?php

class UpdateAction extends SOY2Action{

	private $adminId;

	function setAdminId($adminId){
		$this->adminId = $adminId;
	}

    function execute($request,$form,$response) {

		if($form->hasError()){
			foreach($form as $key => $value){
				if($form->isError($key)){
					$this->setErrorMessage($key,$form->getErrorString($key));
				}
			}
			return SOY2Action::FAILED;
		}

    	try{
	    	$dao = SOY2DAOFactory::create("admin.AdministratorDAO");
	    	$admin = $dao->getById($this->adminId);
	    	SOY2::cast($admin,$form);
	    	$dao->update($admin);

    		//セッション内のユーザー名も更新する
    		if($this->adminId == UserInfoUtil::getUserId()){
    			$name = $admin->getName();
    			if(!$name)$name = $admin->getUserId();
    			$this->getUserSession()->setAttribute('username',$name);
    		}

	    	return SOY2Action::SUCCESS;
    	}catch(Exception $e){
    		return SOY2Action::FAILED;
    	}

    }
}

class UpdateActionForm extends SOY2ActionForm{
	var $userId;
	var $email;
	var $name;

	/**
     * @validator string {"max" : 30, "min" : 4, "require" : true }
     */
	function setUserId($value){
		$this->userId = $value;
	}

	/**
     * @validator string {"max" : 255, "min" : 0}
     */
	function setEmail($email) {
		$this->email = $email;
	}

	/**
     * @validator string {"max" : 255, "min" : 0}
     */
	function setName($name) {
		$this->name = $name;
	}
}
?>