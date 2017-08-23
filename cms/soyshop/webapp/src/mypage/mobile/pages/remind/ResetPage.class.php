<?php

class ResetPage extends MobileMyPagePageBase{
	
	function doPost(){
		
		if(!isset($_POST["password"]) || !isset($_POST["confirm"]))$this->jump();

		if(!$this->checkPassword()){
			$userDAO = SOY2DAOFactory::create("user.SOYShop_UserDAO");

			try{
				$this->user->setPassword($this->user->hashPassword($_POST["password"]));
				$this->user->clearAttribute("remind_limit");
				$this->user->clearAttribute("remind_query");
				
				$userDAO->update($this->user);
				
				$session = false;				
				if(defined("SOYSHOP_IS_MOBILE")&&SOYSHOP_COOKIE){
					if(defined("SOYSHOP_MOBILE_CARRIER")&&SOYSHOP_MOBILE_CARRIER== "DoCoMo"){
						$session = true;
					}
				}
				$this->jump("remind/complete",$session);
			}catch(Exception $e){

			}
			
		}

		
	}
	
	private $mypage;
	private $user;
	private $query;
	private $mail;
	
    function __construct() {
		$this->mypage = MyPageLogic::getMyPage();
		$this->mypage->clearErrorMessage();
		
		if($this->mypage->getIsLoggedin())$this->jumpToTop();//ログイン済み
		if(!isset($_GET["q"]) || !isset($_GET["f"]))$this->jumpToTop();
		$this->query = $_GET["q"];
		$this->mail = $_GET["f"];
		
		$form_visible = true;

    	$userDAO = SOY2DAOFactory::create("user.SOYShop_UserDAO");
		try{
			$this->user = $userDAO->getByMailAddress($this->mail);
			$form_visible = !$this->checkQuery($this->user);
			
		}catch(Exception $e){
			$this->user = new SOYShop_User();
			
			//mailaddress
			$this->mypage->addErrorMessage("remind_url","URLが正しくありません。");
			$form_visible = false;
		}

		
    	parent::__construct();
		
		//display error message
		DisplayPlugin::toggle("has_error",$this->mypage->hasError());
		$this->createAdd("error_list","MobileMyPageErrorList", array(
			"list" => $this->mypage->getErrorMessages()
		));

		$this->createAdd("remind_link","HTMLLink", array(
			"link" => soyshop_get_mypage_url() . "/remind/input"
		));
		
		$this->addForm("form", array(
			"visible" => $form_visible
		));
		
		$this->addLabel("address", array(
			"text" => $this->mail
		));

		$this->addInput("password", array(
			"name" => "password"
		));
		
		$this->addInput("confirm", array(
			"name" => "confirm"
		));
		
		$this->addLink("return_link", array(
			"link" => soyshop_get_mypage_url() . "/top"
		));
    }
    
    /**
     * check for GET params
     */
    function checkQuery($user){
    	
    	$limit = $user->getAttribute("remind_limit");
    	$query = $user->getAttribute("remind_query");
    	
    	//time limit
    	if(!is_null($limit) && time() > $limit)$this->mypage->addErrorMessage("remind_limit","有効期間外です。");
    	
    	//query
    	if($query !== $this->query)$this->mypage->addErrorMessage("remind_url","URLが正しくありません。");
    	
    	return $this->mypage->hasError();
    }
    
    /**
     * check for POST params
     */
    function checkPassword(){
    	
    	$password = $_POST["password"];
    	$confirm = $_POST["confirm"];
    	
    	// no input password
    	if(strlen($password) == 0)$this->mypage->addErrorMessage("remind_password_no_password","パスワードが入力されていません。");

    	//different
    	if($password !== $confirm)$this->mypage->addErrorMessage("remind_password_different","再入力されたパスワードが同じではありません。");

    	return $this->mypage->hasError();
    }
}
?>