<?php
SOY2HTMLFactory::importWebPage("register.IndexPage");
class ConfirmPage extends IndexPage{
	
	function doPost(){
		
		//保存
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
					$tmpUser = false;
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
						$userId = $userDAO->update($user);
					}else{
						$userId = $userDAO->insert($user);
					}
					
					//ユーザカスタムフィールドの値をセッションに入れる
					SOYShopPlugin::load("soyshop.user.customfield");
					SOYShopPlugin::invoke("soyshop.user.customfield", array(
						"mode" => "register",
						"app" => $mypage,
						"userId" => $userId
					));
						
					if($tmpUserMode){
						//仮登録あり
						list($token,$limit) = $mypage->createToken($user->getMailAddress());
						$this->sendTmpRegisterMail($user, $token, $limit);
						$this->jump("register/tmp");
							
					}else{
						//仮登録なし
						$this->sendRegisterMail($user);
						$this->jump("register/complete");
						
					}
	
				}catch(Exception $e){
					
				}	
			}
				
			if(isset($_POST["back"]) || isset($_POST["back_x"])){
				$this->jump("register");
			}
		}
	}
	
	function ConfirmPage(){

		$mypage = MyPageLogic::getMyPage();
		
		//すでにログインしていたら飛ばす
		if($mypage->getIsLoggedin()){
			$this->jumpToTop();
		}
		
		$user = $mypage->getUserInfo();
		
		//直接URLを入力したら入力フォームに戻す
		if(is_null($user)){
			$this->jump("register");
		}

		$this->backward = new BackwardUserComponent();
		$this->component = new UserComponent();
		
		WebPage::WebPage();

		//顧客情報フォーム
		$this->buildForm($user, $mypage, UserComponent::MODE_CUSTOM_CONFIRM);

	}
	
	/**
	 * 仮登録メールの送信
	 * @param SOYShop_User $user
	 * @param string $token
	 * @param integer $limit 有効期限がtimestamp
	 */
	function sendTmpRegisterMail(SOYShop_User $user, $token, $limit){

		$mailLogic = SOY2Logic::createInstance("logic.mail.MailLogic");
		$config = $mailLogic->getMyPageMailConfig("tmp_register");
		
		SOY2::import("domain.order.SOYShop_Order");
		//convert title
		$title = $mailLogic->convertMailContent($config["title"], $user, new SOYShop_Order());

		$query = soyshop_get_mypage_url(true) . "/register/tmp/complete?q=" . $token;
		$text = "\n" . MessageManager::get("MYPAGE_LIMIT_TERM_CONTENT", array("limit" => date("Y年m月d日 H:i", $limit))) . "\n";

		//convert content
		$mailBody = $config["header"] . "\n" . $query . "\n" . $text . "\n" . $config["footer"];
		$content  = $mailLogic->convertMailContent($mailBody, $user, new SOYShop_Order());

		try{
			$mailLogic->sendMail($user->getMailAddress(), $title, $content);
		}catch(Exception $e){
			//@TODO エラーログ出力
		}
	}
	
	/**
	 * 本登録メールの送信
	 * @param SOYShop_User $user
	 */
	function sendRegisterMail($user){

		$mailLogic = SOY2Logic::createInstance("logic.mail.MailLogic");
		$config = $mailLogic->getMyPageMailConfig("register");
		
		SOY2::import("domain.order.SOYShop_Order");
		//convert title
		$title = $mailLogic->convertMailContent($config["title"], $user, new SOYShop_Order());

		//convert content
		$mailBody = $config["header"] . "\n" . $config["footer"];
		$content  = $mailLogic->convertMailContent($mailBody, $user, new SOYShop_Order());

		try{
			$mailLogic->sendMail($user->getMailAddress(), $title, $content);
		}catch(Exception $e){
			//@TODO エラーログ出力
		}
	}
}

class UserCustomfieldConfirm extends HTMLList{
	
	protected function populateItem($entity, $key, $counter, $length){
		$this->addLabel("customfield_name", array(
			"text" => (isset($entity["name"])) ? $entity["name"] : ""
		));

		$this->addLabel("customfield_confirm", array(
			"html" => (isset($entity["confirm"])) ? $entity["confirm"] : ""
		));
	}
}
?>