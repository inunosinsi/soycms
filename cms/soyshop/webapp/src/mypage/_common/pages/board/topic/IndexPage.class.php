<?php
class IndexPage extends MainMyPagePageBase{

	private $id;

	function doPost(){
		if(soy2_check_token()){
			if(isset($_POST["Topic"])){
				$values = $_POST["Topic"];
				$values["groupId"] = $this->id;
				$topicId = SOY2Logic::createInstance("module.plugins.bulletin_board.logic.TopicLogic")->insert($values);
				if(is_numeric($topicId)){
					$this->jump("board/topic/" . $this->id . "?successed");
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

		$group = SOY2Logic::createInstance("module.plugins.bulletin_board.logic.GroupLogic")->getById($this->id);
		if(is_null($group->getId())) $this->jumpToTop();
		// ログインチェックは不要

		parent::__construct();

		$this->addLink("top_link", array(
			"link" => soyshop_get_mypage_url() . "/board/"
		));

		$this->addLabel("name", array(
			"text" => $group->getName()
		));

		$this->addForm("create_form");

		//トピック
		$topics = SOY2Logic::createInstance("module.plugins.bulletin_board.logic.TopicLogic")->getByGroupId($group->getId(), true);
		DisplayPlugin::toggle("no_topic", !count($topics));
		$this->createAdd("topic_list", "_common.board.topic.TopicListComponent", array(
			"list" => $topics
		));
	}
}
