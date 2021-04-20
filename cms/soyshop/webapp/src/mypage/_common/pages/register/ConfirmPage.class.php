<?php
SOY2HTMLFactory::importWebPage("register.IndexPage");
class ConfirmPage extends IndexPage{

	function doPost(){

		//保存
		if(soy2_check_token() && soy2_check_referer()){

			if(isset($_POST["register"]) || isset($_POST["register_x"])){

				$mypage = $this->getMyPage();
				$userDAO = SOY2DAOFactory::create("user.SOYShop_UserDAO");
				$user = $mypage->getUserInfo();

				//パスワード
				SOY2::import("util.SOYShopPluginUtil");
				if(SOYShopPluginUtil::checkIsActive("generate_password")){	//パスワードの自動生成　後ほどパスワードをメールで通知する
					SOY2::import("module.plugins.generate_password.util.GeneratePasswordUtil");
					$cnf = GeneratePasswordUtil::getConfig();
					$len = (isset($cnf["password_strlen"]) && is_numeric($cnf["password_strlen"])) ? (int)$cnf["password_strlen"] : 12;
					$isIncludeSymbol = (isset($cnf["include_symbol"]) && $cnf["include_symbol"] == 1);	//ランダムな文字列に記号を含めるか？
					$pw = soyshop_create_random_string($len, $isIncludeSymbol);
					GeneratePasswordUtil::saveAutoGeneratePassword($user->getMailAddress(), $pw);
					$user->setPassword($pw);
				}

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
						self::_sendTmpRegisterMail($user, $token, $limit);
						$this->jump("register/tmp");

					}else{
						//仮登録なし
						self::_sendRegisterMail($user);
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

	function __construct(){

		$mypage = $this->getMyPage();

		//すでにログインしていたら飛ばす
		if($mypage->getIsLoggedin()) $this->jumpToTop();

		$user = $mypage->getUserInfo();

		//直接URLを入力したら入力フォームに戻す
		if(is_null($user)) $this->jump("register");

		parent::__construct();

		//顧客情報フォーム
		$this->buildForm($user, $mypage);
	}

	/**
	 * 仮登録メールの送信
	 * @param SOYShop_User $user
	 * @param string $token
	 * @param integer $limit 有効期限がtimestamp
	 */
	private function _sendTmpRegisterMail(SOYShop_User $user, $token, $limit){

		$mailLogic = SOY2Logic::createInstance("logic.mail.MailLogic");
		$config = $mailLogic->getMyPageMailConfig("tmp_register");

		SOY2::import("domain.order.SOYShop_Order");
		//convert title
		$title = $mailLogic->convertMailContent($config["title"], $user, new SOYShop_Order());

		$query = soyshop_get_mypage_url(true) . "/register/tmp/complete?q=" . $token;

		//リダイレクト
		$mypage = $this->getMyPage();
		$r = $mypage->getAttribute(MyPageLogic::REGISTER_REDIRECT_KEY);
		if(isset($r)){
			$mypage->clearAttribute(MyPageLogic::REGISTER_REDIRECT_KEY);
			$query .= "&r=" . $r;
		}
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
			//@TODO エラーログ出力
		}
	}
}
