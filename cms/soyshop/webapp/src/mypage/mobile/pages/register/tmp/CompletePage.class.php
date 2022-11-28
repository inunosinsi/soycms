<?php
class CompletePage extends MobileMyPagePageBase{

	function __construct(){


		parent::__construct();

		$register = false;

    	if(isset($_GET["q"])){
    		$register = $this->executeRegister($_GET["q"]);
    	}

		//success
		$this->createAdd("register_success","HTMLModel", array(
			"visible" => $register
		));

		$this->createAdd("login_link","HTMLLink", array(
			"link" => soyshop_get_mypage_url() . "/login"
		));


		//failure
		$this->createAdd("register_failure","HTMLModel", array(
			"visible" => !$register
		));

		$this->createAdd("register_link","HTMLLink", array(
			"link" => soyshop_get_mypage_url() . "/register"
		));


	}

	function executeRegister($query,$mail){

		$userDAO = SOY2DAOFactory::create("user.SOYShop_UserDAO");
		$tokenDAO = SOY2DAOFactory::create("user.SOYShop_UserTokenDAO");


		try{
			$token = $tokenDAO->getByToken($query);
			try{
				$user = $userDAO->getById($token->getUserId());
			}catch(Exception $e){
				return false;
			}

			//user type
			if($user->getUserType() != SOYShop_User::USERTYPE_TMP)return false;

			//time limit
			if($token->getLimit() < time())return false;

			$user->setUserType(SOYShop_User::USERTYPE_REGISTER);
			$user->setRealRegisterDate(time());
			try{
				$userDAO->update($user);
				$this->sendRegisterMail($user,$token);
			}catch(Exception $e){
				return false;
			}

			$tokenDAO->deleteByUserId($user->getId());

		}catch(Exception $e){
			return false;
		}

		return true;

	}

	function sendRegisterMail($user,$token){

		try{
			$mailLogic = SOY2Logic::createInstance("logic.mail.MailLogic");
			$config = $mailLogic->getMyPageMailConfig("register");

			SOY2::import("domain.order.SOYShop_Order");
			//convert title
			$title = $mailLogic->convertMailContent($config["title"],$user, new SOYShop_Order());

			//convert content
			$mailBody = $config["header"] . "\n" . $config["footer"];

			$content  = $mailLogic->convertMailContent($mailBody,$user, new SOYShop_Order());

			$mailLogic->sendMail($user->getMailAddress(),$title,$content);

		}catch(Exception $e){

		}
	}
}
?>
