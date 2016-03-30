<?php
SOY2HTMLFactory::importWebPage("register.IndexPage");
class ConfirmPage extends IndexPage{
	
	function doPost(){
		
		if(soy2_check_token()){
			if(isset($_POST["register"]) || isset($_POST["register_x"])){
		
				$mypage = MyPageLogic::getMyPage();
				$userDAO = SOY2DAOFactory::create("user.SOYShop_UserDAO");
				$user = $mypage->getUserInfo();
					
				try{
					$tmpUser = $userDAO->getTmpUserByEmail($user->getMailAddress());
					$user->setId($tmpUser->getId());
					$user->setPassword($user->hashPassword($user->getPassword()));
					$tmpUser = true;
	
				}catch(Exception $e){
					$tmpUse = false;
	
				}
					
				try{
					$tmpUserMode = SOYShop_DataSets::get("config.mypage.tmp_user_register", 1);
					if($tmpUserMode){
						//仮登録あり
						$user->setUserType(SOYShop_User::USERTYPE_TMP);
						
					}else{
						//仮登録なし
						$user->setUserType(SOYShop_User::USERTYPE_REGISTER);
						$user->setRealRegisterDate(time());
					}
						
					if($tmpUser){
						$userDAO->update($user);
					}else{
						$userDAO->insert($user);
					}
					
					$session = false;				
					if(defined("SOYSHOP_IS_MOBILE")&&SOYSHOP_COOKIE){
						if(defined("SOYSHOP_MOBILE_CARRIER")&&SOYSHOP_MOBILE_CARRIER== "DoCoMo"){
							$session = true;
						}
					}
						
					if($tmpUserMode){
						//仮登録あり
						list($token,$limit) = $mypage->createToken($user->getMailAddress());
						$this->sendTmpRegisterMail($user,$token,$limit);
						$this->jump("register/tmp",$session);
							
					}else{
						//仮登録なし
						$this->sendRegisterMail($user);
						$this->jump("register/complete",$session);
							
					}
	
				}catch(Exception $e){
					var_dump($e);
				}	
			}
				
			if(isset($_POST["back"]) || isset($_POST["back_x"])){
				$session = false;				
				if(defined("SOYSHOP_IS_MOBILE")&&SOYSHOP_COOKIE){
					if(defined("SOYSHOP_MOBILE_CARRIER")&&SOYSHOP_MOBILE_CARRIER== "DoCoMo"){
						$session = true;
					}
				}
				$this->jump("register",$session);
			}
		}
	}

	
	function ConfirmPage(){
		WebPage::WebPage();

		$mypage = MyPageLogic::getMyPage();
		if($mypage->getIsLoggedin())$this->jumpToTop();//すでにログインしていたら飛ばす
		
		$user = $mypage->getUserInfo();
		if(is_null($user))$user = new SOYShop_User();

		$url = soyshop_get_mypage_url() . "/register/confirm";
		if(isset($_GET[session_name()])){
			$url = $url."?".session_name() . "=" . session_id();
		}
		
		$this->addForm("form", array(
			"method" => "post",
			"action" => $url
		));

		//顧客情報フォーム
		$this->buildForm($user);

		//送付先フォーム
		$this->buildSendForm($user);
	}
	
	
	function sendTmpRegisterMail($user,$token,$limit){

		try{
			$mailLogic = SOY2Logic::createInstance("logic.mail.MailLogic");
			$config = $mailLogic->getMyPageMailConfig("tmp_register");
			
			SOY2::import("domain.order.SOYShop_Order");
			//convert title
			$title = $mailLogic->convertMailContent($config["title"],$user, new SOYShop_Order());

			$query = soyshop_get_mypage_url(true)."/register/tmp/complete?q=" . $token;
			$text = "\n有効期限は" . date("Y年m月d日", $limit) . "までとなっています。\n";

			//convert content
			$mailBody = $config["header"] . "\n" . $query . "\n" . $text . "\n" . $config["footer"];
			
			$content  = $mailLogic->convertMailContent($mailBody,$user, new SOYShop_Order());
			
			$mailLogic->sendMail($user->getMailAddress(),$title,$content);
			
		}catch(Exception $e){
			
		}
		
	}

	function sendRegisterMail($user){

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