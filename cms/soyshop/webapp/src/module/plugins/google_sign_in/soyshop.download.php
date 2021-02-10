<?php

class GoogleSignInDownload extends SOYShopDownload{

	function execute(){
		if(soy2_check_token()){
			$logic = SOY2Logic::createInstance("module.plugins.google_sign_in.logic.SignInLogic");
			SOY2::import("module.plugins.google_sign_in.util.GoogleSignInUtil");
			$config = GoogleSignInUtil::getConfig();

			$user = $logic->getUserByMailAddress($_POST["mail"]);
			if(is_null($user->getId())){
				$user->setName($_POST["name"]);
				$user->setUserType(SOYShop_User::USERTYPE_REGISTER);	//身元がわかっているので、仮登録の期間を飛ばすことにする

				$userId = $logic->registUser($user);

				//失敗
				if(is_null($userId)) self::sendResult(0);
				$user->setId($userId);
			}

			// Google IDの登録
			$logic->saveGoogleId($user->getId(), $_POST["google_id"]);

			//ログイン
			if($user->getUserType() == SOYShop_User::USERTYPE_REGISTER){
				$mypage = MyPageLogic::getMyPage();
				$mypage->noPasswordLogin($user->getId());
				$mypage->autoLogin();
				$mypage->save();

				//成功
				self::sendResult(1);
			}else{
				//仮登録モード
				self::sendResult(2);
			}
		}
	}

	private function sendResult($res = 1){
		echo json_encode(array("result" => $res));
		exit;
	}
}
SOYShopPlugin::extension("soyshop.download", "google_sign_in", "GoogleSignInDownload");
