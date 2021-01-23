<?php

class GroupListComponent extends HTMLList {

	private $abstracts;

	protected function populateItem($entity, $key){
		$id = (is_numeric($entity->getId())) ? (int)$entity->getId() : 0;

		$this->addLabel("name", array(
			"text" => $entity->getName()
		));

		// @ToDo トピック数
		$this->addLabel("topic_count", array(
			"text" => (is_numeric($entity->getId())) ? self::_dao()->countByGroupId($entity->getId()) : 0
		));

		$this->addLabel("abstract", array(
			"text" => (is_array($this->abstracts) && isset($this->abstracts[$id])) ? self::_convert($this->abstracts[$id]) : ""
		));

		$this->addInput("display_order", array(
			"name" => "DisplayOrder[" . $id . "]",
			"value" => (is_numeric($entity->getDisplayOrder()) && $entity->getDisplayOrder() < SOYBoard_Group::UPPER_LIMIT) ? $entity->getDisplayOrder() : "",
			"style" => "width:80px;"
		));

		$this->addLink("detail_link", array(
			"link" => SOY2PageController::createLink("Config.Detail?plugin=bulletin_board&group_id=" . $id),
		));

		$this->addActionLink("remove_link", array(
			"link" => SOY2PageController::createLink("Config.Detail?plugin=bulletin_board&remove=" . $id),
			"onclick" => "return confirm('削除しますがよろしいですか？')"
		));
	}

	private function _convert($abst){
		$lines = explode("\n", $abst);
		if(!isset($lines)) return "";

		$line = $lines[0];
		if(mb_strlen($line) > 20) $line = mb_substr($line, 0, 20) . "...";
		return $line;
	}

	private function _dao(){
		static $dao;
		if(is_null($dao)){
			SOY2::import("module.plugins.bulletin_board.domain.SOYBoard_TopicDAO");
			$dao = SOY2DAOFactory::create("SOYBoard_TopicDAO");
		}
		return $dao;
	}

	function setAbstracts($abstracts){
		$this->abstracts = $abstracts;
	}
}
