<?php

class BoardDetailPage extends WebPage {

	private $postId;

	function __construct(){
		SOY2::import("module.plugins.bulletin_board.util.BulletinBoardUtil");
	}

	function execute(){
		parent::__construct();

		$post = SOY2Logic::createInstance("module.plugins.bulletin_board.logic.PostLogic")->getById($this->postId);
		$topic = SOY2Logic::createInstance("module.plugins.bulletin_board.logic.TopicLogic")->getById($post->getTopicId());

		$this->addLabel("topic_label", array(
			"text" => $topic->getLabel()
		));

		$this->addLabel("create_date", array(
			"text" => (is_numeric($post->getCreateDate())) ? date("Y-m-d H:i:s", $post->getCreateDate()) : ""
		));

		$user = soyshop_get_user_object($post->getUserId());
		$this->addLink("user_name", array(
			"text" => $user->getDisplayName(),
			"link" => SOY2PageController::createLink("User.Detail.") . $user->getId()
		));

		$this->addLabel("is_open", array(
			"text" => ($post->getIsOpen() == SOYBoard_Post::IS_OPEN) ? "公開" : "非公開"
		));

		$this->addLabel("content", array(
			"html" => BulletinBoardUtil::nl2br(trim(BulletinBoardUtil::shapeHTML($post->getContent())))
		));

		//処理的には無駄だけどマイページを読み込む
		$mypageId = SOYShop_DataSets::get("config.mypage.id");
		SOY2::import("logic.mypage.MyPageLogic");
		$mypage = MyPageLogic::getMyPage($mypageId);
		$mypage->setUserInfo($user);
		$uploadLogic = SOY2Logic::createInstance("module.plugins.bulletin_board.logic.UploadLogic", array("postId" => $post->getId(), "topicId" => $topic->getId(), "mypage" => $mypage));
		$files = $uploadLogic->getFilePathes($post->getId());

		DisplayPlugin::toggle("image", count($files));

		SOY2::import("module.plugins.bulletin_board._component.ImageListComponent");
		$this->createAdd("image_list", "ImageListComponent", array(
			"list" => $files
		));

		//代わりにログイン
		$this->addLink("login_link", array(
			"link" => soyshop_get_mypage_url() . "/login?purchase=proxy&user_id=" . $user->getId() . "&r=" . soyshop_get_mypage_url() . "/board/topic/edit/" . $post->getId(),
			"target"=> "_blank",
			"attr:rel" => "noopener"
		));
	}

	function setPostId($postId){
		$this->postId = $postId;
	}
}
