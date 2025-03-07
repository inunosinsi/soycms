<?php
class CompletePage extends MainMyPagePageBase{

	function __construct(){

		$mypage = $this->getMyPage();

		//リダイレクト
		$loginUrl = soyshop_get_mypage_url() . "/login";
		$r = $mypage->getAttribute(MyPageLogic::REGISTER_REDIRECT_KEY);
		if(isset($r)){
			$mypage->clearAttribute(MyPageLogic::REGISTER_REDIRECT_KEY);
			$loginUrl .= "?r=" . $r;
		}

		// ConfirmPageで登録した顧客情報をメールアドレスから辿る
		$mailAddress = ($mypage->getUserInfo() instanceof SOYShop_User) ? $mypage->getUserInfo()->getMailAddress() : "";

		$mypage->clearUserInfo();
		$mypage->clearErrorMessage();
		$mypage->save();

		parent::__construct();

		$this->addLink("login_link", array(
			"link" => $loginUrl
		));

		SOYShopPlugin::load("soyshop.mypage");
		SOYShopPlugin::invoke("soyshop.mypage", array(
			"mode" => "register_complete",
			"userId" => (strlen($mailAddress)) ? (int)soyshop_get_user_object_by_mailaddress($mailAddress)->getId() : 0
		));	
	}
}
