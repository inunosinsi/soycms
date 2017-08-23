<?php
SOY2::import("action.register.ChangeAccountAction");

class ChangeUserInfoPage extends CMSWebPageBase{
	
	var $account;
	
	function doPost(){
    	if(soy2_check_token()){
			$result = SOY2ActionFactory::createInstance("ChangeAccountAction")->run();
			
			$account = $result->getAttribute("ChangeAccount");
			
			if($result->success()){
				try{
					$dao = SOY2DAOFactory::create("asp.ASPUserDAO");
					$user = $dao->getById(UserInfoUtil::getUserId());
					
					SOY2::cast($user,$account);
					$dao->update($user);
					
					 SOY2ActionSession::getUserSession()->setAttribute("username",$user->getNickname());
					
					$this->jump("Login.UserInfo",array(
						"userinfoChanged" => true
					));
				}catch(Exception $e){
					$this->jump("Login.UserInfo",array(
						"userinfoChanged" => false
					));
				}
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
    		$this->account = new ChangeAccountActionForm();
    	}  	
    	
    	$dao = SOY2DAOFactory::create("asp.ASPUserDAO");
    	$user = $dao->getById(UserInfoUtil::getUserId());
    	
    	SOY2::cast($this->account,$user);
    	
    	$this->createAdd("changeform","HTMLForm");
    	$this->buildForm($this->account);    	
    }
    
    function buildForm($account){
    	
    	$this->createAdd("nickname","HTMLInput",array(
    		"name" => "nickname",
    		"value" => $account->getNickname()
    	));
    	
    	$this->createAdd("nickname_error","HTMLLabel",array(
    		"text" => $account->getErrorString("nickname")
    	));
    	
    	
    	$this->createAdd("firstName","HTMLInput",array(
    		"name" => "firstName",
    		"value" => $account->getFirstName()
    	));
    	
    	$this->createAdd("firstNameReading","HTMLInput",array(
    		"name" => "firstNameReading",
    		"value" => $account->getFirstNameReading()
    	));
    	
		$this->createAdd("lastName","HTMLInput",array(
    		"name" => "lastName",
    		"value" => $account->getLastName()
    	));
    	
    	$this->createAdd("lastNameReading","HTMLInput",array(
    		"name" => "lastNameReading",
    		"value" => $account->getLastNameReading()
    	));
    	
    	$this->createAdd("name_error","HTMLLabel",array(
    		"text" => ( $account->getErrorString("firstName") ) ? $account->getErrorString("firstName") : $account->getErrorString("lastName")
    	));
    	
    	$this->createAdd("nameReading_error","HTMLLabel",array(
    		"text" => ( $account->getErrorString("firstNameReading") ) ? $account->getErrorString("firstNameReading") : $account->getErrorString("lastNameReading")
    	));   	
    	
    	$this->createAdd("birthday_year","HTMLSelect",array(
    		"name" => "birthDate[year]",
			"options" => range(1902,2008),
    		"selected" => ($account->getBirthDate()) ? date("Y",$account->getBirthDate()) : ""
    	));
    	
    	$this->createAdd("birthday_month","HTMLSelect",array(
    		"name" => "birthDate[month]",
			"options" => range(1,12),
			"selected" => ($account->getBirthDate()) ? date("m",$account->getBirthDate()) : ""
    	));
    	
    	$this->createAdd("birthday_day","HTMLSelect",array(
    		"name" => "birthDate[day]",
			"options" => range(1,31),
    		"selected" => ($account->getBirthDate()) ? date("d",$account->getBirthDate()) : ""
    	));

    }
}
?>