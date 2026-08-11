<?php

class TopicListComponent extends HTMLList {

	protected function populateItem($entity, $key){
		$id = (is_numeric($entity->getId())) ? (int)$entity->getId() : 0;

		$this->addLabel("label", array(
			"text" => $entity->getLabel()
		));

		$this->addLink("topic_detail_link", array(
			"link" => soyshop_get_mypage_url() . "/board/topic/detail/" . $id
		));

		//投稿数
		$this->addLabel("post_count", array(
			"text" => ($id > 0) ? soy2_number_format(self::_logic()->countPostByTopicId($id)) : 0
		));

		//最初と最後の投稿を取得する
		list($firstPost, $lastPost) = self::_logic()->getFirstAndLastPost($id);

		$firstUser = soyshop_get_user_object($firstPost->getUserId());
		$this->addLink("first_post_user", array(
			"link" => (is_numeric($firstUser->getId())) ? soyshop_get_mypage_url() . "/board/user/detail/" . $firstUser->getId() : null,
			"text" => (is_numeric($firstUser->getId())) ? $firstUser->getDisplayName() : "---"
		));

		$lastUser = soyshop_get_user_object($lastPost->getUserId());
		$this->addLink("last_post_user", array(
			"link" => (is_numeric($firstUser->getId())) ? soyshop_get_mypage_url() . "/board/user/detail/" . $lastUser->getId() : null,
			"text" => (is_numeric($firstUser->getId())) ? $lastUser->getDisplayName() : "---"
		));

		$this->addLabel("last_post_date", array(
			"text" => (is_numeric($lastPost->getCreateDate())) ? date("Y-m-d H:i:s", $lastPost->getCreateDate()) : "投稿なし"
		));
	}

	private function _logic(){
		static $logic;
		if(is_null($logic)) $logic = SOY2Logic::createInstance("module.plugins.bulletin_board.logic.PostLogic");
		return $logic;
	}
}
