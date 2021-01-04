<?php

class GroupListComponent extends HTMLList {

	protected function populateItem($entity, $key){
		$id = (is_numeric($entity->getId())) ? (int)$entity->getId() : 0;

		$this->addLabel("name", array(
			"text" => $entity->getName()
		));

		// @ToDo トピック数
		$this->addLabel("topic_count", array(
			"text" => 0
		));

		$this->addInput("display_order", array(
			"name" => "DisplayOrder[" . $id . "]",
			"value" => (is_numeric($entity->getDisplayOrder()) && $entity->getDisplayOrder() < SOYBoard_Group::UPPER_LIMIT) ? $entity->getDisplayOrder() : "",
			"style" => "width:80px;"
		));

		$this->addActionLink("remove_link", array(
			"link" => SOY2PageController::createLink("Config.Detail?plugin=bulletin_board&remove=" . $id),
			"onclick" => "return confirm('削除しますがよろしいですか？')"
		));
	}
}
