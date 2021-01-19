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

			if(isset($_POST["upload"])){
				if(isset($_FILES["image"]) && is_uploaded_file($_FILES["image"]["tmp_name"])){
					$topicId = SOY2Logic::createInstance("module.plugins.bulletin_board.logic.PostLogic")->getById($this->id)->getTopicId();
					if(SOY2Logic::createInstance("module.plugins.bulletin_board.logic.UploadLogic", array("postId" => $this->id, "topicId" => $topicId, "mypage" => $this->getMyPage()))->uploadTmpFile($_FILES["image"]["tmp_name"], $_FILES["image"]["type"])){
						$this->jump("board/topic/edit/" . $this->id);
					}
				}
			}else{
				$this->jump("board/topic/edit/confirm/" . $this->id);
			}
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

		$uploadLogic = SOY2Logic::createInstance("module.plugins.bulletin_board.logic.UploadLogic", array("postId" => $this->id, "topicId" => $topic->getId(), "mypage" => $this->getMyPage()));

		//画像の削除
		if(isset($_GET["remove"]) && soy2_check_token()){
			$uploadLogic->remove($_GET["remove"]);
			$this->jump("board/topic/edit/" . $this->id);
		}

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

		$this->addActionLink("remove_link", array(
			"link" => soyshop_get_mypage_url() . "/board/topic/edit/remove/" . $post->getId(),
			"onclick" => "return confirm('削除してもよろしいでしょうか？');"
		));

		$this->addForm("post_form", array(
			"enctype" => "multipart/form-data"
		));

		//投稿中の内容
		$values = $this->getMyPage()->getAttribute("soyboard_post_content_edit");
		$content = (isset($values["content"]) && isset($values["post_id"]) && $values["post_id"] == $this->id) ? $values["content"] : $post->getContent();

		$this->addTextArea("content", array(
			"name" => "Post",
			"value" => BulletinBoardUtil::returnHTML($content)
		));

		//Advanced textarea
		$this->addModel("advanced_textarea_js", array(
			"attr:src" => soyshop_get_site_url() . "js/textarea.js"
		));

		// $this->addLabel("usage_prohibited_html_tags", array(
		// 	"html" => self::_getUsageProhibitedHtmlTagList()
		// ));

		$this->addLabel("usage_html_tags", array(
			"html" => self::_getUsageHtmlTagList()
		));

		//アップロードフォーム
		SOY2::import("mypage._common.pages._common.board.image.UploadFormComponent");
		$this->addLabel("upload_form_component", array(
			"html" => UploadFormComponent::build()
		));

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
	}

	// private function _getUsageProhibitedHtmlTagList(){
	// 	$list = BulletinBoardUtil::getUsageProhibitedHtmlTagList();
	// 	$str = "";
	// 	foreach($list as $tag){
	// 		$str .= "&lt;" . $tag . "&gt; ";
	// 	}
	// 	return trim($str);
	// }

	private function _getUsageHtmlTagList(){
		$list = BulletinBoardUtil::getUsagableHtmlTagList();
		$str = "";
		foreach($list as $tag){
			$str .= "&lt;" . $tag . "&gt; ";
		}
		return trim($str);
	}
}
