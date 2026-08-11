<?php

SOY2::import("module.plugins.bulletin_board.util.BulletinBoardUtil");
class IndexPage extends MainMyPagePageBase{

	private $id;

	function doPost(){
		if(soy2_check_token()){
			if(isset($_POST["Topic"])){
				$values = $_POST["Topic"];
				$values["groupId"] = $this->id;
				$topicId = SOY2Logic::createInstance("module.plugins.bulletin_board.logic.TopicLogic")->insert($values);
				if(is_numeric($topicId)){
					//トピックの作成の通知を運営者権限を持つアカウントに送信
					SOY2Logic::createInstance("module.plugins.bulletin_board.logic.SendMailLogic")->sendTopicNotice($topicId, $this->getUserId());
					$this->jump("board/topic/detail/" . $topicId . "?successed");
				}
			}
		}

		$this->jump("board/topic/" . $this->id . "?failed");
	}

	function __construct($args){
		// 掲示板アプリプラグインを有効にしていない場合は表示しない
		SOY2::import("util.SOYShopPluginUtil");
		if(!SOYShopPluginUtil::checkIsActive("bulletin_board")) $this->jumpToTop();

		if(!isset($args[0]) && !is_numeric($args[0])) $this->jumpToTop();
		$this->id = (int)$args[0];

		$groupLogic = SOY2Logic::createInstance("module.plugins.bulletin_board.logic.GroupLogic");
		$group = $groupLogic->getById($this->id);
		if(is_null($group->getId())) $this->jumpToTop();
		// ログインチェックは不要

		parent::__construct();

		$this->addLink("top_link", array(
			"link" => soyshop_get_mypage_url() . "/board/"
		));

		$this->addLabel("name", array(
			"text" => $group->getName()
		));

		//グループの説明文
		$groupDesp = trim($groupLogic->getGroupDescriptionById($group->getId()));
		DisplayPlugin::toggle("group_description", strlen($groupDesp));
		$this->addLabel("group_description", array(
			"html" => BulletinBoardUtil::nl2br(BulletinBoardUtil::autoInsertAnchorTag(BulletinBoardUtil::shapeHTML($groupDesp)))
		));

		/** ログインしていない時 **/
		DisplayPlugin::toggle("no_logged_in", !$this->getMyPage()->getIsLoggedIn());
		$this->addLink("login_page_link", array(
			"link" => soyshop_get_mypage_login_url(false, true)
		));

		/** ログインしている時 **/
		DisplayPlugin::toggle("is_logged_in", $this->getMyPage()->getIsLoggedIn());

		$this->addForm("create_form");

		//トピック
		$topics = SOY2Logic::createInstance("module.plugins.bulletin_board.logic.TopicLogic")->getByGroupId($group->getId(), true, true);
		DisplayPlugin::toggle("no_topic", !count($topics));
		$this->createAdd("topic_list", "_common.board.topic.TopicListComponent", array(
			"list" => $topics
		));
	}
}
