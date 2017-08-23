<?php
SOY2::import("action.register.ChangePasswordAction");
class ChangePasswordPage extends CMSWebPageBase{
	var $account;
	
	function doPost(){
    	if(soy2_check_token()){
			$result = SOY2ActionFactory::createInstance("ChangePasswordAction")->run();
			
			$account = $result->getAttribute("ChangePassword");
			
			if($result->success()){
				
				$dao = SOY2DAOFactory::create("asp.ASPUserDAO");
				$user = $dao->getById(UserInfoUtil::getUserId());
				
				SOY2::cast($user,$account);
				
				$user->setPassword(crypt($account->getPassword(),$user->getEmail()));
				
				$dao->update($user);
				
				
				$this->jump("Login.UserInfo",array(
					"passwordChanged" => true
				));
			}
			
			$this->account = $account;
    	}
	}
	
    function __construct(){
    	$dao = SOY2DAOFactory::create("asp.ASPUserDAO");
		$user = $dao->getById(UserInfoUtil::getUserId());
		
		if(!$user->getIsEnableWithdraw()){
			$this->jump("Login.UserInfo");
		}
		
    	parent::__construct();
    	
    	if(!$this->account){
    		$this->account = new ChangePasswordActionForm();
    	}  	
    	
    	$this->createAdd("changeform","HTMLForm");
    	$this->buildForm($this->account);    	
    }
    
    function buildForm($account){
    	
    	$this->createAdd("password","HTMLInput",array(
    		"name" => "password",
    		"value" => $account->getPassword(),
    		"type" => "password"    	
    	));
    	
    	$this->createAdd("password_error","HTMLLabel",array(
    		"text" => $account->getErrorString("password")    	
    	));
    	
    	$this->createAdd("password_confirm","HTMLInput",array(
    		"name" => "passwordConfirm",
    		"value" => $account->getPasswordConfirm(),
    		"type" => "password"    	
    	));
    	
    	$this->createAdd("password_confirm_error","HTMLLabel",array(
    		"text" => $account->getErrorString("passwordConfirm")    	
    	));
    	
    }
}
?>