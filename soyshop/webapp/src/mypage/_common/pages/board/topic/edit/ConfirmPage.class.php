<?php

SOY2::import("module.plugins.bulletin_board.util.BulletinBoardUtil");
class ConfirmPage extends MainMyPagePageBase{

	private $id;

	function doPost(){
		if(soy2_check_token()){
			if(isset($_POST["post"])){
				$mypage = $this->getMyPage();
				$post = $mypage->getAttribute("soyboard_post_content_edit");

				$content = trim(BulletinBoardUtil::shapeHTML($post["content"]));
				if(!strlen($content)) $this->jump("board/topic/edit/confirm/" . $this->id . "?failed");;

				$postId = SOY2Logic::createInstance("module.plugins.bulletin_board.logic.PostLogic")->save($mypage->getUserId(), null, $this->id, $content);
				if(is_numeric($postId)){
					$this->jump("board/topic/edit/complete/" . $this->id);
				}

				$this->jump("board/topic/edit/confirm/" . $this->id . "?failed");
			}

			if(isset($_POST["back"])){
				$this->jump("board/topic/edit/" . $this->id);
			}
		}

	}

	function __construct($args){
		//ログインチェック
		if(!$this->getMyPage()->getIsLoggedIn()) $this->jumpToTop();
		if(!isset($args[0]) || !is_numeric($args[0])) $this->jumpToTop();

		$this->id = (int)$args[0];

		// 掲示板アプリプラグインを有効にしていない場合は表示しない
		SOY2::import("util.SOYShopPluginUtil");
		if(!SOYShopPluginUtil::checkIsActive("bulletin_board")) $this->jumpToTop();

		$values = $this->getMyPage()->getAttribute("soyboard_post_content_edit");
		$content = trim(BulletinBoardUtil::shapeHTML($values["content"]));
		if(!strlen($content)) $this->jumpToTop();	// @ToDo コンテンツを空欄にした時はどうしよう？

		$post = SOY2Logic::createInstance("module.plugins.bulletin_board.logic.PostLogic")->getById($this->id, $this->getUser()->getId());
		if(is_null($post->getId())) $this->jumpToTop();	//ポストを投稿したユーザの確認

		$topic = SOY2Logic::createInstance("module.plugins.bulletin_board.logic.TopicLogic")->getById($post->getTopicId(), true);
		if(is_null($topic->getId())) $this->jumpToTop();	//トピックが所属するグループが非公開であるか？は上の処理でわかる

		$group = SOY2Logic::createInstance("module.plugins.bulletin_board.logic.GroupLogic")->getById($topic->getGroupId());

		parent::__construct();

		DisplayPlugin::toggle("failed", isset($_GET["failed"]));

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

		//内容の確認
		$this->addLabel("content", array(
			"html" => BulletinBoardUtil::nl2br(BulletinBoardUtil::autoInsertAnchorTag($content))
		));

		$uploadLogic = SOY2Logic::createInstance("module.plugins.bulletin_board.logic.UploadLogic", array("postId" => $this->id, "topicId" => $topic->getId(), "mypage" => $this->getMyPage()));
		$imgFiles = $uploadLogic->getFilePathes($post->getId());

		//仮ディレクトリの画像一覧
		$tmpFiles = $uploadLogic->getTmpFilePathes();
		$imgFiles = array_merge($imgFiles, $tmpFiles);
		DisplayPlugin::toggle("image", count($imgFiles));

		$this->createAdd("image_list", "_common.board.topic.ImageListComponent", array(
			"list" => BulletinBoardUtil::pushEmptyValues($imgFiles)
		));

		//アップロードした画像の確認用のモーダル
		SOY2::import("mypage._common.pages._common.board.image.ImageModalComponent");
		$this->addLabel("image_modal", array(
			"html" => ImageModalComponent::build()
		));

		$this->addForm("form");
	}
}
