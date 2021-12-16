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
				if(isset($config["pre_register_mode"]) && (int)$config["pre_register_mode"] === 1){	//仮登録モード
					$user->setUserType(SOYShop_User::USERTYPE_TMP);
				}else{
					$user->setUserType(SOYShop_User::USERTYPE_REGISTER);
				}

				$userId = $logic->registerUser($user);

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

	private function sendResult(int $res=1){
		echo json_encode(array("result" => $res));
		exit;
	}
}
SOYShopPlugin::extension("soyshop.download", "google_sign_in", "GoogleSignInDownload");
