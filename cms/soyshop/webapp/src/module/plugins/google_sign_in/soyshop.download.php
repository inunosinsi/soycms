<?php

class GoogleSignInDownload extends SOYShopDownload{

	function execute(){
		if(soy2_check_token()){
			$logic = SOY2Logic::createInstance("module.plugins.google_sign_in.logic.SignInLogic");

			$user = $logic->getUserByMailAddress($_POST["mail"]);
			if(is_null($user->getId())){
				$user->setName($_POST["name"]);
				$userId = $logic->registUser($user);

				//失敗
				if(is_null($userId)) self::sendResult(0);
				$user->setId($userId);
			}

			// Google IDの登録
			$logic->saveGoogleId($user->getId(), $_POST["google_id"]);

			//ログイン
			$mypage = MyPageLogic::getMyPage();
			$mypage->noPasswordLogin($user->getId());

			//成功
			self::sendResult(1);
		}
	}

	private function sendResult($res = 1){
		echo json_encode(array("result" => $res));
		exit;
	}
}
SOYShopPlugin::extension("soyshop.download", "google_sign_in", "GoogleSignInDownload");
