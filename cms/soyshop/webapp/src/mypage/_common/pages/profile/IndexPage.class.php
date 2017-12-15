<?php

class IndexPage extends MainMyPagePageBase{

	private $displayUserId;

	function __construct($args){
		//このページはログイン関係なく閲覧できるので、ログインチェックは行わない

		$profileId = (isset($args[0])) ? $args[0] : null;
		$displayUser = MyPageLogic::getMyPage()->getUserByProfileId($profileId);

		//ユーザがプロフィールページの閲覧を許可していない場合は前のページかトップページに飛ばす
		if($displayUser->getIsProfileDisplay() != SOYShop_User::PROFILE_IS_DISPLAY){
			soyshop_redirect_from_profile();
		}

		$this->userId = $displayUser->getId();

		parent::__construct();

		$this->addLabel("profile_name", array(
			"text" => $displayUser->getDisplayName()
		));

		self::buildProfile($displayUser);
	}

	private function buildProfile(SOYShop_User $displayUser){

		DisplayPlugin::toggle("nickname", (strlen($displayUser->getNickname()) > 0));
		$this->addModel("is_nickname", array(
			"visible" => (strlen($displayUser->getNickname()) > 0)
		));

		$this->addLabel("nickname", array(
			"text" => $displayUser->getNickname()
		));

		DisplayPlugin::toggle("image", (strlen($displayUser->getImagePath()) > 0));
		$this->addModel("is_image", array(
			"visible" => (strlen($displayUser->getImagePath()))
		));

		$displayUserLogic = SOY2Logic::createInstance("logic.user.UserLogic");
		$width = $displayUserLogic->getDisplayImage($displayUser);
		$this->addImage("image", array(
			"src"     => $displayUser->getAttachmentsUrl() . $displayUser->getImagePath(),
    		"visible" => (strlen($displayUser->getImagePath()) > 0),
    		"style"   => "width:" . $width . "px;"
		));

		DisplayPlugin::toggle("gender", (!is_null($displayUser->getGender())));
		$this->addModel("is_gender", array(
			"visible" => (!is_null($displayUser->getGender()))
		));

		$this->addLabel("gender", array(
			"text" => ((int)$displayUser->getGender() === SOYShop_User::USER_SEX_MALE) ? MessageManager::get("SEX_MALE") :
			        ( ((int)$displayUser->getGender() === SOYShop_User::USER_SEX_FEMALE) ? MessageManager::get("SEX_FEMALE") : "" )
		));

		DisplayPlugin::toggle("url", (strlen($displayUser->getUrl()) > 0));
		$this->addModel("is_url", array(
			"visible" => (strlen($displayUser->getUrl()) > 0)
		));

		$this->addLink("url", array(
			"link" => $displayUser->getUrl(),
			"text" => $displayUser->getUrl(),
			"target" => "_blank"
		));

		DisplayPlugin::toggle("memo", (strlen($displayUser->getMemo()) > 0));
		$this->addModel("is_memo", array(
			"visible" => (strlen($displayUser->getMemo()) > 0)
		));

		$this->addLabel("memo", array(
			"html" => nl2br($displayUser->getMemo())
		));
	}
}
