<?php

class PostListComponent extends HTMLList {

	protected function populateItem($entity, $key){
		$this->addLabel("create_date", array(
			"text" => (is_numeric($entity->getCreateDate())) ? date("Y-m-d H:i:s", $entity->getCreateDate()) : ""
		));

		$user = soyshop_get_user_object($entity->getUserId());
		$this->addLink("user_name", array(
			"text" => $user->getDisplayName(),
			"link" => SOY2PageController::createLink("User.Detail.") . $user->getId()
		));

		$this->addLabel("topic_label", array(
			"text" => self::_topicLogic()->getById($entity->getTopicId())->getLabel()
		));

		$this->addLabel("is_open", array(
			"text" => (is_numeric($entity->getIsOpen()) && $entity->getIsOpen() == SOYBoard_Post::IS_OPEN) ? "公開" : "非公開"
		));

		$this->addLink("detail_link", array(
			"link" => SOY2PageController::createLink("Extension.Detail.bulletin_board.") . $entity->getId()
		));
	}

	private function _topicLogic(){
		static $logic;
		if(is_null($logic)) $logic = SOY2Logic::createInstance("module.plugins.bulletin_board.logic.TopicLogic");
		return $logic;
	}
}
