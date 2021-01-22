<?php

SOY2::import("module.plugins.bulletin_board.util.BulletinBoardUtil");
class ConfirmPage extends MainMyPagePageBase{

	private $id;

	function doPost(){
		if(soy2_check_token()){
			if(isset($_POST["post"])){
				$mypage = $this->getMyPage();
				$post = $mypage->getAttribute("soyboard_post_content");
				if(!isset($post["topic_id"]) || !is_numeric($post["topic_id"]) || !isset($post["content"])) $this->jump("board/topic/confirm?failed");

				$content = trim(BulletinBoardUtil::shapeHTML($post["content"]));
				if(!strlen($content)) $this->jump("board/topic/confirm?failed");

				$postLogic = SOY2Logic::createInstance("module.plugins.bulletin_board.logic.PostLogic");
				$postId = $postLogic->save($mypage->getUserId(), (int)$post["topic_id"], null, $content);
				if(is_numeric($postId)){
					//同一トピック内で二人以上のアカウントが居る場合は通知メールを送信
					$userIds = $postLogic->getUserIdsWithinSameTopicByPostId($postId);
					if(count($userIds) > 1){
						SOY2Logic::createInstance("module.plugins.bulletin_board.logic.SendMailLogic")->sendPostNotice($postId, $userIds, $this->getUserId());
					}

					$this->jump("board/topic/complete/" . $postId);
				}

				$this->jump("board/topic/confirm?failed");
			}

			if(isset($_POST["back"])){
				$this->jump("board/topic/detail/" . $this->id . "#post_form");
			}
		}

	}

	function __construct(){
		//ログインチェック
		if(!$this->getMyPage()->getIsLoggedIn()) $this->jumpToTop();

		// 掲示板アプリプラグインを有効にしていない場合は表示しない
		SOY2::import("util.SOYShopPluginUtil");
		if(!SOYShopPluginUtil::checkIsActive("bulletin_board")) $this->jumpToTop();

		$post = $this->getMyPage()->getAttribute("soyboard_post_content");
		$content = trim(BulletinBoardUtil::shapeHTML($post["content"]));
		if(!strlen($content)) $this->jumpToTop();

		$topic = SOY2Logic::createInstance("module.plugins.bulletin_board.logic.TopicLogic")->getById((int)$post["topic_id"], true);
		if(is_null($topic->getId())) $this->jumpToTop();	//トピックが所属するグループが非公開であるか？は上の処理でわかる

		$this->id = $topic->getId();

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

		$this->addLabel("topic_label", array(
			"text" => $topic->getLabel()
		));

		//内容の確認
		$this->addLabel("content", array(
			"html" => BulletinBoardUtil::nl2br(BulletinBoardUtil::autoInsertAnchorTag($content))
		));

		//仮ディレクトリの画像一覧
		$tmpFiles = SOY2Logic::createInstance("module.plugins.bulletin_board.logic.UploadLogic", array("topicId" => $this->id, "mypage" => $this->getMyPage()))->getTmpFilePathes();
		DisplayPlugin::toggle("image", count($tmpFiles));

		$this->createAdd("image_list", "_common.board.topic.ImageListComponent", array(
			"list" => BulletinBoardUtil::pushEmptyValues($tmpFiles)
		));

		//アップロードした画像の確認用のモーダル
		SOY2::import("mypage._common.pages._common.board.image.ImageModalComponent");
		$this->addLabel("image_modal", array(
			"html" => ImageModalComponent::build()
		));

		$this->addForm("form");
	}
}
