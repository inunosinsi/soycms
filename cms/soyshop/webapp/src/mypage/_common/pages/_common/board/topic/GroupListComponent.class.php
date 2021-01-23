<?php

class GroupListComponent extends HTMLList {

	protected function populateItem($entity, $key){
		$id = (is_numeric($entity->getId())) ? (int)$entity->getId() : 0;

		$this->addLabel("name", array(
			"text" => $entity->getName()
		));

		$this->addLink("group_detail_link", array(
			"link" => soyshop_get_mypage_url() . "/board/topic/" . $id
		));

		$abst = (is_array($this->abstracts) && isset($this->abstracts[$id])) ? trim(BulletinBoardUtil::nl2br(BulletinBoardUtil::autoInsertAnchorTag(BulletinBoardUtil::shapeHTML($this->abstracts[$id])))) : "";
		$isAbst = (strlen($abst));
		$this->addModel("is_abstract", array(
			"visible" => ($isAbst)
		));
		$this->addLabel("abstract", array(
			"html" => ($isAbst) ? $abst : ""
		));

		$this->addLabel("topic_count", array(
			"text" => number_format(self::_topicLogic()->countByGroupId($id))
		));

		$latestPostTopicId = self::_postLogic()->getLatestPostTopicIdByGroupId($id);
		$this->addLink("latest_post_topic_link", array(
			"link" => (is_numeric($latestPostTopicId)) ? soyshop_get_mypage_url() . "/board/topic/detail/" . $latestPostTopicId . "#post_last" : null,
			"text" => (is_numeric($latestPostTopicId)) ? self::_topicLogic()->getById($latestPostTopicId)->getLabel() : "---"
		));
	}

	private function _topicLogic(){
		static $logic;
		if(is_null($logic)) $logic = SOY2Logic::createInstance("module.plugins.bulletin_board.logic.TopicLogic");
		return $logic;
	}

	private function _postLogic(){
		static $logic;
		if(is_null($logic)) $logic = SOY2Logic::createInstance("module.plugins.bulletin_board.logic.PostLogic");
		return $logic;
	}

	function setAbstracts($abstracts){
		$this->abstracts = $abstracts;
	}
}
