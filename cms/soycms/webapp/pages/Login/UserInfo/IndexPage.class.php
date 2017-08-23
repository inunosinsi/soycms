<?php

class IndexPage extends CMSWebPageBase{
	
	var $userinfoChanged;
	var $passwordChanged;
	
	function setUserInfoChanged($flag){
		$this->userinfoChanged = $flag;
	}
	
	function setPasswordChanged($flag){
		$this->passwordChanged = $flag;	
	}
	
	function doPost(){
		
		if(isset($_POST["changeuser"])){
			SOY2PageController::jump("Login.UserInfo.ChangeUserInfo");
		}
		
		if(isset($_POST["changepassword"])){
			SOY2PageController::jump("Login.UserInfo.ChangePassword");
		}
		
		if(isset($_POST["changemail"])){
			SOY2PageController::jump("Login.UserInfo.ChangeEmail");
		}
		
		if(isset($_POST["withdraw"])){
			SOY2PageController::jump("Login.UserInfo.Withdraw");
		}
			
	}
	
    function __construct() {
    	exit;
    	parent::__construct();
    	$dao = SOY2DAOFactory::create("asp.ASPUserDAO");
    	$user = $dao->getById(UserInfoUtil::getUserId());
    	
    	if(!$user->getIsEnableWithdraw()){
    		DisplayPlugin::hide("only_enable_withdrow_user");
    	}
    	
    	$name =  $user->getLastName() ." ". $user->getFirstName();
    	$reading = $user->getLastNameReading() ." ". $user->getFirstNameReading();
    	if(strlen($user->getLastNameReading() . $user->getFirstNameReading())){
    		$name .= "(". $reading .")";
    	}
    	
    	$this->createAdd("name","HTMLLabel",array(
    		"text" => $name						
    	));
    	
    	$this->createAdd("nickname","HTMLLabel",array(
    		"text" => $user->getNickName()
    	));
    	
    	$this->createAdd("birthday","HTMLLabel",array(
    		"text" => ($user->getBirthDate()) ? $user->getBirthDate() : CMSMessageManager::get("SOYCMS_NO_SETTING")
    	));
    	
    	$this->createAdd("email","HTMLLabel",array(
    		"text" => $user->getEmail()
    	));
    	
    	//フォームの追加
    	$this->createAdd("changeuser_form","HTMLForm");
    	$this->createAdd("changepassword_form","HTMLForm");
    	$this->createAdd("changemail_form","HTMLForm");
    	$this->createAdd("withdraw_form","HTMLForm");
    	
    	//メッセージ
    	$this->createAdd("changepassword_message","HTMLModel",array("visible"=>$this->passwordChanged));
    	$this->createAdd("changeuserinfo_message","HTMLModel",array("visible"=>$this->userinfoChanged));
    	
    	$this->createAdd("password_submit","HTMLInput",array(
    		"visible"=>$user->getIsEnableWithdraw(),
    		"value"=>CMSMessageManager::get("SOYCMS_CHANGE_PASSWORD")
    	));
    	
    	$this->createAdd("chang_info_submit","HTMLInput",array(
    		"visible"=>$user->getIsEnableWithdraw(),
    		"value"=>CMSMessageManager::get("SOYCMS_CHANGE_USER_INFORMATION")
    	));
    	
    	
    }
}
?>