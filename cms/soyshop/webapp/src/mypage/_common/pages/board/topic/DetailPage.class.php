<?php

SOY2::import("module.plugins.bulletin_board.util.BulletinBoardUtil");
class DetailPage extends MainMyPagePageBase{

	private $id;

	function doPost(){
		//ログインしていない場合はdoPostを禁止する
		if(!$this->getMyPage()->getIsLoggedIn()) $this->jumpToTop();

		if(soy2_check_token()){
			$values = array(
				"content" => $_POST["Post"],
				"topic_id" => $this->id
			);
			$this->getMyPage()->setAttribute("soyboard_post_content", $values);
			$this->jump("board/topic/confirm/");
		}
		$this->jump("board/topic/" . $this->id . "?failed");
	}

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

		$posts = SOY2Logic::createInstance("module.plugins.bulletin_board.logic.PostLogic")->getByTopicId($topic->getId());
		DisplayPlugin::toggle("show_post_button", count($posts) > 2);

		//topicに紐付いたpost
		$this->createAdd("post_list", "_common.board.post.PostListComponent", array(
			"list" => $posts,
			"currentLoggedInUserId" => $this->getUser()->getId()
		));

		$this->addLink("top_link", array(
			"link" => soyshop_get_mypage_url() . "/board/"
		));

		$this->addLink("group_link", array(
			"link" => soyshop_get_mypage_url() . "/board/topic/" . $group->getId()
		));

		$this->addLabel("group_name", array(
			"text" => $group->getName()
		));

		$this->addLabel("topic_label", array(
			"text" => $topic->getLabel()
		));

		/** ログインしていない時 **/
		DisplayPlugin::toggle("no_logged_in", !$this->getMyPage()->getIsLoggedIn());
		$this->addLink("login_page_link", array(
			"link" => soyshop_get_mypage_url() . "/login?=r=" . rawurldecode($_SERVER["REQUEST_URI"])
		));

		/** ログインしている時 **/
		DisplayPlugin::toggle("is_logged_in", $this->getMyPage()->getIsLoggedIn());
		$this->addForm("post_form");

		//投稿中の内容
		$post = $this->getMyPage()->getAttribute("soyboard_post_content");

		$this->addTextArea("content", array(
			"name" => "Post",
			"value" => (isset($post["content"])) ? $post["content"] : ""
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
