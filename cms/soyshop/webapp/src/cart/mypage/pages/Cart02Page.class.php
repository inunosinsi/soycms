<?php
/**
 * @class Cart02Page
 * @date 2009-10-17
 * @author SOY2HTMLFactory
 */
class Cart02Page extends MainCartPageBase{

	function doPost(){

		if(isset($_POST["login"]) || isset($_POST["login_x"])){

			if($this->login(@$_POST["mail"],@$_POST["password"])){
				//auto login
				if(isset($_POST["login_memory"])) $this->autoLogin();

				$cart = CartLogic::getCart();
				$cart->checkOrderable();
				$cart->setAttribute("page", "Cart03");
				$cart->save();
				soyshop_redirect_cart();
			}

		}

		if(isset($_POST["prev"]) || isset($_POST["prev_x"])){
			$cart = CartLogic::getCart();
			$cart->setAttribute("page", "Cart01");

			soyshop_redirect_cart();
		}

	}

    function Cart02Page() {

    	//ログインチェック
    	$cart = CartLogic::getCart();
//   	$cart->checkOrderable();
    	$mypage = MyPageLogic::getMyPage();
		if($mypage->getIsLoggedin()==true){
			$cart->checkOrderable();
			$cart->setAttribute("page", "Cart03");
			$cart->save();
			soyshop_redirect_cart();
		}

		WebPage::__construct();

		$this->createAdd("login_form","HTMLForm", array(
			"action" => soyshop_get_cart_url(false)
		));

		$this->createAdd("login_mail","HTMLInput", array(
			"name" => "mail",
			"value" => @$_POST["mail"]
		));

		$this->createAdd("login_password","HTMLInput", array(
			"name" => "password",
			"value" => ""
		));

		$this->createAdd("auto_login","HTMLCheckBox", array(
			"name" => "login_memory",
			"elementId" => "login_memory"
		));

		$this->createAdd("remind_link","HTMLLink", array(
			"link" => soyshop_get_mypage_url() . "/remind/input"
		));

		$this->createAdd("register_link","HTMLLink", array(
			"link" => soyshop_get_mypage_url() . "/register"
		));

		//エラー周り
		DisplayPlugin::toggle("has_error",strlen($mypage->getErrorMessage("login_error")));


		$mypage->clearErrorMessage();
		$mypage->save();
    }

	/**
	 * ログイン
	 */
	function login($userId,$password){
		$mypage = MyPageLogic::getMyPage();
		$userDAO = SOY2DAOFactory::create("user.SOYShop_UserDAO");

    	try{
    		$user = $userDAO->getRegisterUserByEmail($userId);
    		if(!$user->checkPassword($password)){
    			$mypage->addErrorMessage("login_error","true");
    			$mypage->save();
    			return false;
    		}
    	}catch(Exception $e){

   			$mypage->addErrorMessage("login_error","true");
   			$mypage->save();
    		return false;
    	}


    	//セッションに追加
		$mypage->setAttribute("loggedin", true);
    	$mypage->setAttribute("userId", $user->getId());

    	$mypage->save();
    	return true;

	}

	/**
	 * 自動ログイン
	 */
	function autoLogin(){
		$mypage = MyPageLogic::getMyPage();
		$mypage->autoLogin();
	}
}
?>