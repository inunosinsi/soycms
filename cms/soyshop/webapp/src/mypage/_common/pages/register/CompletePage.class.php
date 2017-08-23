<?php
class CompletePage extends MainMyPagePageBase{

	function __construct(){

		$mypage = MyPageLogic::getMyPage();
		
		//リダイレクト
		$loginUrl = soyshop_get_mypage_url() . "/login";
		$r = $mypage->getAttribute(MyPageLogic::REGISTER_REDIRECT_KEY);
		if(isset($r)){
			$mypage->clearAttribute(MyPageLogic::REGISTER_REDIRECT_KEY);
			$loginUrl .= "?r=" . $r;
		}
		
		$mypage->clearUserInfo();
		$mypage->clearErrorMessage();
		$mypage->save();
		
		parent::__construct();

		$this->addLink("login_link", array(
			"link" => $loginUrl
		));
	}
}
?>