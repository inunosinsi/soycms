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
    	}catch(Exception $e){
    		return SOY2Action::FAILED;
    	}

		if(!isset($_POST["custom_field"])) return SOY2Action::SUCCESS;
		SOY2::import("domain.admin.AdministratorAttribute");
		$configs = AdministratorAttributeConfig::load();
		if(!isset($configs)) return SOY2Action::SUCCESS;

		$attrDao = SOY2DAOFactory::create("admin.AdministratorAttributeDAO");
		foreach($configs as $config){
			try{
				$attr = $attrDao->get($admin->getId(), $config->getFieldId());
			}catch(Exception $e){
				$attr = new AdministratorAttribute();
				$attr->setAdminId($admin->getId());
				$attr->setFieldId($config->getFieldId());
			}

			$attr->setValue($_POST["custom_field"][$config->getFieldId()]);
			try{
				$attrDao->insert($attr);
			}catch(Exception $e){
				try{
					$attrDao->update($attr);
				}catch(Exception $e){
					//
				}
			}
		}

		return SOY2Action::SUCCESS;
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
