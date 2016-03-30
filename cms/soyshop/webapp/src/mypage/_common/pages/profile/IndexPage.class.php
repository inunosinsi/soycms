<?php

class IndexPage extends MainMyPagePageBase{

	private $userId;

	function IndexPage($args){
		
		//このページはログイン関係なく閲覧できるので、ログインチェックは行わない
		$mypage = MyPageLogic::getMyPage();
		
		$profileId = (isset($args[0])) ? $args[0] : null;
		$user = $mypage->getProfileUser($profileId);
		
		//ユーザがプロフィールページの閲覧を許可していない場合は前のページかトップページに飛ばす
		if($user->getIsProfileDisplay() != SOYShop_User::PROFILE_IS_DISPLAY){
			soyshop_redirect_from_profile();
		}

		$this->userId = $user->getId();
		
		WebPage::WebPage();
		
		$this->addLabel("profile_name", array(
			"text" => $user->getDisplayName()
		));
		
		$this->buildProfile($user);
	}
	
	function buildProfile($user){
		
		$this->addModel("is_nickname", array(
			"visible" => (strlen($user->getNickname()) > 0)
		));
		
		$this->addLabel("nickname", array(
			"text" => $user->getNickname()
		));
				
		$this->addModel("is_image", array(
			"visible" => (strlen($user->getImagePath()))
		));
		
		$userLogic = SOY2Logic::createInstance("logic.user.UserLogic");
		$width = $userLogic->getDisplayImage($user);
		$this->addImage("image", array(
			"src"     => $user->getAttachmentsUrl() . $user->getImagePath(),
    		"visible" => (strlen($user->getImagePath()) > 0),
    		"style"   => "width:" . $width . "px;"
		));
						
		$this->addModel("is_gender", array(
			"visible" => (!is_null($user->getGender()))
		));
		
		$this->addLabel("gender", array(
			"text" => ((int)$user->getGender() === SOYShop_User::USER_SEX_MALE) ? MessageManager::get("SEX_MALE") :
			        ( ((int)$user->getGender() === SOYShop_User::USER_SEX_FEMALE) ? MessageManager::get("SEX_FEMALE") : "" )
		));
		
		$this->addModel("is_url", array(
			"visible" => (strlen($user->getUrl()) > 0)
		));
		
		$this->addLink("url", array(
			"link" => $user->getUrl(),
			"text" => $user->getUrl(),
			"target" => "_blank"
		));
		
		$this->addModel("is_memo", array(
			"visible" => (strlen($user->getMemo()) > 0)
		));
		
		$this->addLabel("memo", array(
			"html" => nl2br($user->getMemo())
		));
	}
}
?>