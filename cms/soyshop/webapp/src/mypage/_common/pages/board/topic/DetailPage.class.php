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

			if(isset($_POST["upload"])){
				if(isset($_FILES["image"]) && is_uploaded_file($_FILES["image"]["tmp_name"])){
					if(SOY2Logic::createInstance("module.plugins.bulletin_board.logic.UploadLogic", array("topicId" => $this->id, "mypage" => $this->getMyPage()))->uploadTmpFile($_FILES["image"]["tmp_name"], $_FILES["image"]["type"])){
						$this->jump("board/topic/detail/" . $this->id . "#post_form");
					}
				}
			}else{
				$this->jump("board/topic/confirm/");
			}
		}
		$this->jump("board/topic/detail/" . $this->id . "?failed#post_form");
	}

	function __construct($args){
		// 掲示板アプリプラグインを有効にしていない場合は表示しない
		SOY2::import("util.SOYShopPluginUtil");
		if(!SOYShopPluginUtil::checkIsActive("bulletin_board")) $this->jumpToTop();

		if(!isset($args[0]) && !is_numeric($args[0])) $this->jumpToTop();
		$this->id = (int)$args[0];

		$uploadLogic = SOY2Logic::createInstance("module.plugins.bulletin_board.logic.UploadLogic", array("topicId" => $this->id, "mypage" => $this->getMyPage()));

		//削除
		if(isset($_GET["remove"]) && soy2_check_token()){
			$uploadLogic->remove($_GET["remove"]);
			$this->jump("board/topic/detail/" . $this->id . "#post_form");
		}

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
			"currentLoggedInUserId" => $this->getUser()->getId(),
			"uploadLogic" => $uploadLogic
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
			"link" => soyshop_get_mypage_login_url(false, true)
		));

		/** ログインしている時 **/
		DisplayPlugin::toggle("is_logged_in", $this->getMyPage()->getIsLoggedIn());
		$this->addForm("post_form", array(
			"enctype" => "multipart/form-data"
		));

		//投稿中の内容
		$post = $this->getMyPage()->getAttribute("soyboard_post_content");

		$this->addTextArea("content", array(
			"name" => "Post",
			"value" => (isset($post["content"])) ? $post["content"] : ""
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

		//仮ディレクトリの画像一覧
		$tmpFiles = $uploadLogic->getTmpFilePathes();
		DisplayPlugin::toggle("image", count($tmpFiles));

		$this->createAdd("image_list", "_common.board.topic.ImageListComponent", array(
			"list" => BulletinBoardUtil::pushEmptyValues($tmpFiles)
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
