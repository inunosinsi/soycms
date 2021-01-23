<?php

class FacebookLoginDownload extends SOYShopDownload{

	function execute(){
		if(soy2_check_token()){
			require_once(dirname(__FILE__) . "/lib/autoload.php");

			SOY2::import("module.plugins.facebook_login.util.FacebookLoginUtil");
			$config = FacebookLoginUtil::getConfig();

			$fb = new \Facebook\Facebook([
				'app_id' => htmlspecialchars(trim($config["app_id"]), ENT_QUOTES, "UTF-8"),
				'app_secret' => htmlspecialchars(trim($config["app_secret"]), ENT_QUOTES, "UTF-8"),
				'default_graph_version' => htmlspecialchars(trim($config["api_version"]), ENT_QUOTES, "UTF-8"),
  			]);

			try {
				// Get the \Facebook\GraphNodes\GraphUser object for the current user.
				// If you provided a 'default_access_token', the '{access-token}' is optional.
				$response = $fb->get('/me?fields=name,email', $_POST["access_token"]);
			} catch(\Facebook\Exceptions\FacebookResponseException $e) {
				self::sendResult($e->getMessage(), 0);
			} catch(\Facebook\Exceptions\FacebookSDKException $e) {
  				self::sendResult($e->getMessage(), 0);
			}

			$me = $response->getGraphUser();

			//登録
			if(!is_null($me->getEmail())){
				$logic = SOY2Logic::createInstance("module.plugins.facebook_login.logic.FBLoginLogic");
				$user = $logic->getUserByMailAddress($me->getEmail());
				if(is_null($user->getId())){
					$user->setName($me->getName());
					$userId = $logic->registUser($user);

					//失敗
					if(is_null($userId)) self::sendResult("failed", 0);
					$user->setId($userId);
				}

				// Facebook IDの登録
				$logic->saveFacebookId($user->getId(), $_POST["facebook_id"]);

				//ログイン
				$mypage = MyPageLogic::getMyPage();
				$mypage->noPasswordLogin($user->getId());
				$mypage->autoLogin();
				$mypage->save();

				//成功
				self::sendResult("OK", 1);
			}
		}

		self::sendResult("failed", 0);
	}

	private function log($v){
		file_put_contents("fb.txt", var_export($v, true));
	}

	private function sendResult($mes, $res = 1){
		echo json_encode(array("message" => $mes, "result" => $res));
		exit;
	}
}
SOYShopPlugin::extension("soyshop.download", "facebook_login", "FacebookLoginDownload");
