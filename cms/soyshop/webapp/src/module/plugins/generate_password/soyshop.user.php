<?php
class GeneratePasswordUser extends SOYShopUserBase{

	function executeOnListPage(){
		SOY2::import("module.plugins.generate_password.util.GeneratePasswordUtil");
		$cnf = GeneratePasswordUtil::getConfig();

		// 管理画面で自動生成を行うか？
		if(!isset($cnf["generate_pw_on_admin"]) || (int)$cnf["generate_pw_on_admin"] !== 1) return;

		//パスワードが登録されていないユーザがいる場合は自動でパスワードを生成
		//顧客一覧ページで下記のコードを実行する理由は、新規作成時に失敗しても良いように
		$userIds = self::_getNoPasswordUserIds();
		if(!count($userIds)) return;

		//パスワードの自動生成
		$len = (isset($cnf["password_strlen"]) && is_numeric($cnf["password_strlen"])) ? (int)$cnf["password_strlen"] : 12;
		$userDao = SOY2DAOFactory::create("user.SOYShop_UserDAO");

		//メール送信関連の設定
		$mailLogic = SOY2Logic::createInstance("logic.mail.MailLogic");
		$mailCnf = $mailLogic->getMyPageMailConfig("register");

		$isIncludeSymbol = (isset($cnf["include_symbol"]) && $cnf["include_symbol"] == 1);

		SOY2::import("domain.order.SOYShop_Order");

		//パスワードの自動生成
		foreach($userIds as $userId){
			$user = soyshop_get_user_object($userId);
			$pw = soyshop_create_random_string($len, $isIncludeSymbol);
			GeneratePasswordUtil::saveAutoGeneratePassword($user->getMailAddress(), $pw);
			$user->setPassword($user->hashPassword($pw));
			try{
				$userDao->update($user);
			}catch(Exception $e){
				continue;
			}

			//メールの送信設定
			if(!isset($cnf["send_mail_on_admin"]) || (int)$cnf["send_mail_on_admin"] !== 1) continue;

			$title = $mailLogic->convertMailContent($mailCnf["title"], $user, new SOYShop_Order());
			$mailCnf["header"] .= GeneratePasswordUtil::buildPasswordMessage($user->getMailAddress());
			$mailBody = $mailCnf["header"] . "\n" . $mailCnf["footer"];
			$content  = $mailLogic->convertMailContent($mailBody, $user, new SOYShop_Order());
			try{
				$mailLogic->sendMail($user->getMailAddress(), $title, $content);
			}catch(Exception $e){
				//
			}
		}
	}

	private function _getNoPasswordUserIds(){
		$dao = new SOY2DAO();
		try{
			$res = $dao->executeQuery("SELECT id FROM soyshop_user WHERE password IS NULL AND is_disabled != 1;");
		}catch(Exception $e){
			$res = array();
		}
		if(!count($res)) return array();

		$ids = array();
		foreach($res as $v){
			$ids[] = (int)$v["id"];
		}
		return $ids;
	}
}
SOYShopPlugin::extension("soyshop.user", "generate_password", "GeneratePasswordUser");
