<?php

class LINELoginDownload extends SOYShopDownload{

	function execute(){
		if(isset($_GET["line_login"])){
			if(isset($_GET["code"]) && isset($_GET["state"])){
				$logic = SOY2Logic::createInstance("module.plugins.line_login.logic.LINELoginLogic");
				if($logic->checkLoggedIn($_GET["state"])){
					$token = $logic->getTokenByCode($_GET["code"]);
					if(!is_null($token)){
						$values = $logic->getLINEProfileByToken($token);
						if(isset($values["userId"])){
							$userId = $logic->getUserIdByLineId($values["userId"]);
							if(is_null($userId)){
								//LINEからアカウントのメールアドレスは取得できないので、ダミーのメールアドレスを使うしかない。
								$userId = $logic->registerMemberOnSOYShop($values);
							}

							if(isset($userId)){
								//ログインする
								$mypage = MyPageLogic::getMyPage();
				                $mypage->noPasswordLogin($userId);
							}
						}
					}
				}
			}
		}

		//すべての処理が終わったら、loginトップへ
		soyshop_redirect_login_form();
	}
}
SOYShopPlugin::extension("soyshop.download", "line_login", "LINELoginDownload");
