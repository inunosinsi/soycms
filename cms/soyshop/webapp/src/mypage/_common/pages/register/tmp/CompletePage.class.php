<?php
class CompletePage extends MainMyPagePageBase{

	function __construct(){

		$register = false;

    	if(isset($_GET["q"])){
    		$register = self::_executeRegister($_GET["q"]);
    	}

    	parent::__construct();

		//success
		$this->addModel("register_success", array(
			"visible" => ($register)
		));

		$loginUrl = soyshop_get_mypage_url() . "/login";
		if(isset($_GET["r"])){
			$loginUrl .= "?r=" . $_GET["r"];
		}
		$this->addLink("login_link", array(
			"link" => $loginUrl
		));


		//failure
		$this->addModel("register_failure", array(
			"visible" => (!$register)
		));

		$this->addLink("register_link", array(
			"link" => soyshop_get_mypage_url() . "/register"
		));
	}

	private function _executeRegister($query){
		$tokenDAO = SOY2DAOFactory::create("user.SOYShop_UserTokenDAO");
		$tokenDAO->deleteOldObjects();

		try{
			$token = $tokenDAO->getByToken($query);
			$user = soyshop_get_user_object($token->getUserId());

			//user type
			if($user->getUserType() != SOYShop_User::USERTYPE_TMP){
				throw new Exception(MessageManager::get("NO_PROVISIONAL_REGISTRATION"));
			}

			//time limit
			if($token->getLimit() < time()){
				throw new Exception(MessageManager::get("TERM_OF_VALIDITY"));
			}

			$user->setUserType(SOYShop_User::USERTYPE_REGISTER);
			$user->setRealRegisterDate(time());

			SOY2DAOFactory::create("user.SOYShop_UserDAO")->update($user);
			self::_sendRegisterMail($user);

			$tokenDAO->deleteByUserId($user->getId());

		}catch(Exception $e){
			return false;
		}

		return true;
	}

	private function _sendRegisterMail(SOYShop_User $user){

		$mailLogic = SOY2Logic::createInstance("logic.mail.MailLogic");
		$config = $mailLogic->getMyPageMailConfig("register");

		SOY2::import("domain.order.SOYShop_Order");
		//convert title
		$title = $mailLogic->convertMailContent($config["title"], $user, new SOYShop_Order());

		//パスワードの自動生成
		if(SOYShopPluginUtil::checkIsActive("generate_password")){
			SOY2::import("module.plugins.generate_password.util.GeneratePasswordUtil");
			$config["header"] .= GeneratePasswordUtil::buildPasswordMessage($user->getMailAddress());
		}

		//convert content
		$mailBody = $config["header"] . "\n" . $config["footer"];
		$content  = $mailLogic->convertMailContent($mailBody, $user, new SOYShop_Order());

		try{
			$mailLogic->sendMail($user->getMailAddress(), $title, $content);
		}catch(Exception $e){

		}
	}
}
