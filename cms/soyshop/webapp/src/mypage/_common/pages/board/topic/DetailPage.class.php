<?php
class DetailPage extends MainMyPagePageBase{

	private $id;

	function __construct($args){
		// 掲示板アプリプラグインを有効にしていない場合は表示しない
		SOY2::import("util.SOYShopPluginUtil");
		if(!SOYShopPluginUtil::checkIsActive("bulletin_board")) $this->jumpToTop();

		if(!isset($args[0]) && !is_numeric($args[0])) $this->jumpToTop();
		$this->id = (int)$args[0];

		// ログインチェックは不要
		$topic = SOY2Logic::createInstance("module.plugins.bulletin_board.logic.TopicLogic")->getById($this->id, true);
		if(is_null($topic->getId())) $this->jumpToTop();	//トピックが所属するグループが非公開であるか？は上の処理でわかる

		$group = SOY2Logic::createInstance("module.plugins.bulletin_board.logic.GroupLogic")->getById($topic->getGroupId());

		parent::__construct();

		$this->addLink("top_link", array(
			"link" => soyshop_get_mypage_url() . "/board/"
		));

		$this->addLink("group_link", array(
			"link" => soyshop_get_mypage_url() . "/board/group/" . $group->getId()
		));

		$this->addLabel("group_name", array(
			"text" => $group->getName()
		));

		$this->addLabel("topic_label", array(
			"text" => $topic->getLabel()
		));
	}
}
