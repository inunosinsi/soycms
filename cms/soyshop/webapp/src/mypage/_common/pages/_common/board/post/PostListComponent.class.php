<?php
if(!class_exists("BulletinBoardUtil")) SOY2::import("module.plugins.bulletin_board.util.BulletinBoardUtil");
class PostListComponent extends HTMLList {

	private $currentLoggedInUserId;
	private $uploadLogic;

	protected function populateItem($entity, $key){
		$id = (is_numeric($entity->getId())) ? (int)$entity->getId() : 0;
		$userId = (is_numeric($entity->getUserId())) ? (int)$entity->getUserId() : 0;
		$user = soyshop_get_user_object($userId);

		$this->addModel("post_id", array(
			"attr:id" => $id
		));

		$this->addLink("user_detail_link", array(
			"link" => ($user->getIsPublish()) ? soyshop_get_mypage_url() . "/board/user/detail/" . $user->getId() : null,
			"text" => ($user->getIsPublish()) ? $user->getDisplayName() : "退会したユーザ"
		));

		$this->addModel("show_edit_link", array(
			"visible" => (is_numeric($this->currentLoggedInUserId) && $userId == $this->currentLoggedInUserId)
		));
		$this->addLink("edit_link", array(
			"link" => (is_numeric($this->currentLoggedInUserId) && $userId == $this->currentLoggedInUserId) ? soyshop_get_mypage_url() . "/board/topic/edit/" . $entity->getId() : null
		));

		$this->addLabel("create_date", array(
			"text" => (is_numeric($entity->getCreateDate())) ? date("Y-m-d H:i:s", $entity->getCreateDate()) : ""
		));

		$this->addLabel("content", array(
			"html" => BulletinBoardUtil::nl2br(BulletinBoardUtil::autoInsertAnchorTag(BulletinBoardUtil::shapeHTML($entity->getContent())))
		));

		//画像ファイル
		$imgFiles = ($id > 0) ? $this->uploadLogic->getFilePathes($id) : array();
		$this->addModel("is_images", array(
			"visible" => (is_array($imgFiles) && count($imgFiles))
		));
		$this->createAdd("image_list", "_common.board.topic.ImageListComponent", array(
			"list" => BulletinBoardUtil::pushEmptyValues($imgFiles)
		));
	}

	function setCurrentLoggedInUserId($currentLoggedInUserId){
		$this->currentLoggedInUserId = $currentLoggedInUserId;
	}

	function setUploadLogic($uploadLogic){
		$this->uploadLogic = $uploadLogic;
	}
}