<?php

class LoginWithAmazonDownload extends SOYShopDownload{

	function execute(){
		if(isset($_GET["login_with_amazon"]) && isset($_GET["access_token"])){
			$token = $_GET["access_token"];
			// https://developer.amazon.com/ja/docs/login-with-amazon/obtain-customer-profile.htmlを参考にしてコードを作成
			$logic = SOY2Logic::createInstance("module.plugins.login_with_amazon.logic.LoginWithAmazonLogic");
			$d = $logic->access($token);

			if (is_null($d)) {
				// アクセストークンがこちらに属していない
				header('HTTP/1.1 404 Not Found');
				echo 'ページが見つかりません';
				exit;
			}

			$d = $logic->getProfile($token);

			// 取得したuser_id(AmazonID)とメールアドレスですでにユーザが登録されていればそのままログイン
			$userId = $logic->getUserIdByAmazonId($d->user_id);
			if(is_null($userId)){
				$userId = $logic->register($d->user_id, $d->name, $d->email);
			}

			$mypage = MyPageLogic::getMyPage();
			$mypage->noPasswordLogin($userId);
			$mypage->autoLogin();

			// jump
			$r = $mypage->getAttribute(MyPageLogic::REGISTER_REDIRECT_KEY);
			$mypage->clearAttribute(MyPageLogic::REGISTER_REDIRECT_KEY);
			$mypage->save();

			if(isset($r)){
				$param = soyshop_remove_get_value($r);
				soyshop_redirect_designated_page($param, "login=complete");
				exit;
			}
		}

		//すべての処理が終わったら、loginトップへ
		soyshop_redirect_login_form();
	}
}
SOYShopPlugin::extension("soyshop.download", "login_with_amazon", "LoginWithAmazonDownload");
