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
		$isImage = (is_array($imgFiles) && count($imgFiles));

		//署名
		$sign = self::_getSignByUserId($userId);
		$isSign = (strlen($sign));

		$this->addModel("is_card_footer", array(
			"visible" => ($isImage || $isSign)
		));

		$this->addModel("is_images", array(
			"visible" => $isImage
		));
		$this->createAdd("image_list", "_common.board.topic.ImageListComponent", array(
			"list" => ($isImage) ? BulletinBoardUtil::pushEmptyValues($imgFiles) : array()
		));

		$this->addModel("is_signature", array(
			"visible" => $isSign
		));

		$this->addLabel("signature", array(
			"html" => ($isSign) ? BulletinBoardUtil::nl2br(BulletinBoardUtil::autoInsertAnchorTag(BulletinBoardUtil::shapeHTML($sign))) : ""
		));
	}

	private function _getSignByUserId($userId){
		static $signs;
		if(is_null($signs)) $signs = array();
		if(!isset($signs[$userId])) $signs[$userId] = self::_usfLogic()->get($userId, BulletinBoardUtil::FIELD_ID_SIGNATURE);
		return $signs[$userId];
	}

	private function _usfLogic(){
		static $logic;
		if(is_null($logic)) $logic = SOY2Logic::createInstance("module.plugins.user_custom_search_field.logic.UserDataBaseLogic");
		return $logic;
	}

	function setCurrentLoggedInUserId($currentLoggedInUserId){
		$this->currentLoggedInUserId = $currentLoggedInUserId;
	}

	function setUploadLogic($uploadLogic){
		$this->uploadLogic = $uploadLogic;
	}
}
