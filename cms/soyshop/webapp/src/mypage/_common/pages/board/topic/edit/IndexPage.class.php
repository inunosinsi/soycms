<?php

SOY2::import("module.plugins.bulletin_board.util.BulletinBoardUtil");
class IndexPage extends MainMyPagePageBase{

	private $id;

	function doPost(){
		//ログインしていない場合はdoPostを禁止する
		if(!$this->getMyPage()->getIsLoggedIn()) $this->jumpToTop();

		if(soy2_check_token()){
			$values = array(
				"content" => $_POST["Post"],
				"post_id" => $this->id
			);
			$this->getMyPage()->setAttribute("soyboard_post_content_edit", $values);
			$this->jump("board/topic/edit/confirm/" . $this->id);
		}
		$this->jump("board/topic/edit/" . $this->id . "?failed");
	}

	function __construct($args){
		// 掲示板アプリプラグインを有効にしていない場合は表示しない
		SOY2::import("util.SOYShopPluginUtil");
		if(!SOYShopPluginUtil::checkIsActive("bulletin_board")) $this->jumpToTop();

		if(!isset($args[0]) && !is_numeric($args[0])) $this->jumpToTop();
		$this->id = (int)$args[0];

		$post = SOY2Logic::createInstance("module.plugins.bulletin_board.logic.PostLogic")->getById($this->id, $this->getUserId());
		if(is_null($post->getId())) $this->jumpToTop();
		$topic = SOY2Logic::createInstance("module.plugins.bulletin_board.logic.TopicLogic")->getById($post->getTopicId(), true);
		if(is_null($topic->getId())) $this->jumpToTop();	//トピックが所属するグループが非公開であるか？は上の処理でわかる

		$group = SOY2Logic::createInstance("module.plugins.bulletin_board.logic.GroupLogic")->getById($topic->getGroupId());

		parent::__construct();

		$this->addLink("top_link", array(
			"link" => soyshop_get_mypage_url() . "/board/"
		));

		$this->addLink("group_link", array(
			"link" => soyshop_get_mypage_url() . "/board/topic/" . $group->getId()
		));

		$this->addLabel("group_name", array(
			"text" => $group->getName()
		));

		$this->addLink("topic_link", array(
			"link" => soyshop_get_mypage_url() . "/board/topic/detail/" . $topic->getId()
		));

		$this->addLabel("topic_label", array(
			"text" => $topic->getLabel()
		));


		$this->addForm("post_form");

		//投稿中の内容
		$values = $this->getMyPage()->getAttribute("soyboard_post_content_edit");
		$content = (isset($values["content"]) && isset($values["post_id"]) && $values["post_id"] == $this->id) ? $values["content"] : $post->getContent();

		$this->addTextArea("content", array(
			"name" => "Post",
			"value" => BulletinBoardUtil::returnHTML($content)
		));

		$this->addLabel("usage_prohibited_html_tags", array(
			"html" => self::_getUsageProhibitedHtmlTagList()
		));
	}

	private function _getUsageProhibitedHtmlTagList(){
		$list = BulletinBoardUtil::getUsageProhibitedHtmlTagList();
		$str = "";
		foreach($list as $tag){
			$str .= "&lt;" . $tag . "&gt; ";
		}
		return trim($str);
	}
}
