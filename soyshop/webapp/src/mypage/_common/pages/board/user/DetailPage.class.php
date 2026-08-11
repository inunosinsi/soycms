<?php

class DetailPage extends MainMyPagePageBase{

	function __construct($args){
		// 掲示板アプリプラグインを有効にしていない場合は表示しない
		SOY2::import("util.SOYShopPluginUtil");
		if(!SOYShopPluginUtil::checkIsActive("bulletin_board")) $this->jumpToTop();

		if(!isset($args[0]) && !is_numeric($args[0])) $this->jumpToTop();
		$this->id = (int)$args[0];

		$user = soyshop_get_user_object($this->id);
		if(!$user->isPublished()) $this->jumpToTop();

		parent::__construct();

		$this->addLink("top_link", array(
			"link" => soyshop_get_mypage_url() . "/board/"
		));

		$this->addLink("user_list_link", array(
			"link" => soyshop_get_mypage_url() . "/board/user/"
		));

		$this->addLabel("user_name", array(
			"text" => $user->getDisplayName()
		));

		DisplayPlugin::toggle("url", strlen($user->getUrl()));
		$this->addLink("url", array(
			"link" => $user->getUrl(),
			"text" => $user->getUrl(),
			"target" => "_blank",
			"attr:rel" => "noopener"
		));

		$this->addLabel("register_date", array(
			"text" => (is_numeric($user->getRegisterDate())) ? date("Y-m-d H:i:s", $user->getRegisterDate()) :""
		));

		SOYShopPlugin::load("soyshop.user.customfield");
		$delegate = SOYShopPlugin::invoke("soyshop.user.customfield", array(
			"mode" => "build_named_form",
			"app" => $this->getMyPage(),
			"pageObj" => $this,
			"userId" => $user->getId()
		));

		//SNSのリンク
		list($ghUrl, $twUrl) = self::_getSnsUrls($user->getId());
		$isGithub = (isset($ghUrl));
		$isTwitter = (isset($twUrl));

		DisplayPlugin::toggle("sns", ($isGithub || $isTwitter));

		DisplayPlugin::toggle("github", $isGithub);
		$this->addLink("github_link", array(
			"link" => $ghUrl
		));

		$this->addImage("github_logo", array(
			"src" => self::_getImageFilePath("gh"),
			"alt" => "GitHub"
		));

		DisplayPlugin::toggle("twitter", $isTwitter);
		$this->addLink("twitter_link", array(
			"link" => $twUrl
		));

		$this->addImage("twitter_logo", array(
			"src" => self::_getImageFilePath("tw"),
			"alt" => "Twitter"
		));
	}

	private function _getSnsUrls($userId){
		SOY2::import("module.plugins.bulletin_board.util.BulletinBoardUtil");
		$csfValues = SOY2Logic::createInstance("module.plugins.user_custom_search_field.logic.UserDataBaseLogic")->getByUserId($userId);
		$urls = array();

		//github
		$urls[] = (isset($csfValues[BulletinBoardUtil::FIELD_ID_GITHUB]) && strpos($csfValues[BulletinBoardUtil::FIELD_ID_GITHUB], "https://github.com/") === 0) ? $csfValues[BulletinBoardUtil::FIELD_ID_GITHUB] : null;

		//twitter
		$urls[] = (isset($csfValues[BulletinBoardUtil::FIELD_ID_TWITTER]) && strpos($csfValues[BulletinBoardUtil::FIELD_ID_TWITTER], "https://twitter.com/") === 0) ? $csfValues[BulletinBoardUtil::FIELD_ID_TWITTER] : null;

		return $urls;
	}

	private function _getImageFilePath($mode){
		$imgDir = SOYSHOP_SITE_DIRECTORY . "image/";
		if(!file_exists($imgDir)) mkdir($imgDir);
		$imgPath = $imgDir . $mode . ".png";
		if(!file_exists($imgPath)){
			copy(SOY2::RootDir() . "module/plugins/bulletin_board/img/" . $mode . ".png", $imgPath);
		}
		return "/" . SOYSHOP_ID . "/image/" . $mode . ".png";
	}
}
