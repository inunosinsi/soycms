<?php

SOY2::import("module.plugins.bulletin_board.util.BulletinBoardUtil");
class CompletePage extends MainMyPagePageBase{

	function __construct($args){
		//ログインチェック
		if(!$this->getMyPage()->getIsLoggedIn()) $this->jumpToTop();

		// 掲示板アプリプラグインを有効にしていない場合は表示しない
		SOY2::import("util.SOYShopPluginUtil");
		if(!SOYShopPluginUtil::checkIsActive("bulletin_board")) $this->jumpToTop();

		if(!isset($args[0]) || !is_numeric($args[0])) $this->jumpToTop();

		$mypage = $this->getMyPage();
		if(is_null($mypage->getAttribute("soyboard_post_content_edit"))) $this->jumpToTop();

		$post = SOY2Logic::createInstance("module.plugins.bulletin_board.logic.PostLogic")->getById($args[0]);
		if(is_null($post->getId())) $this->jumpToTop();

		parent::__construct();

		$this->addLink("back_link", array(
			"link" => soyshop_get_mypage_url() . "/board/topic/detail/" . $post->getTopicId() . "#" . $post->getId()
		));

		$mypage->clearAttribute("soyboard_post_content_edit");

		//画像のアップロード周り
		$uploadLogic = SOY2Logic::createInstance("module.plugins.bulletin_board.logic.UploadLogic", array("postId" => $post->getId(), "topicId" => $post->getTopicId(), "mypage" => $this->getMyPage()));
		$uploadLogic->move($post->getId());
		$uploadLogic->align($post->getId());
	}
}
