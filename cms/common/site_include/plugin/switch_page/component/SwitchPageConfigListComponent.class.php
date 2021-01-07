<?php

class SwitchPageConfigListComponent extends HTMLList {

	private $pageList;

	function populateItem($entity, $idx){
		$entity = (is_array($entity)) ? $entity : array();

		$this->addLabel("start", array(
			"text" => (isset($entity["start"]) && (int)$entity["start"] > SwitchPageUtil::PERIOD_START) ? date("Y-m-d H:i:s", $entity["start"]) : "---"
		));

		$this->addLabel("end", array(
			"text" => (isset($entity["end"]) && (int)$entity["end"] < SwitchPageUtil::PERIOD_END) ? date("Y-m-d H:i:s", $entity["start"]) : "---"
		));

		$this->addLabel("from", array(
			"text" => (isset($entity["from"]) && is_numeric($entity["from"]) && isset($this->pageList[$entity["from"]])) ? $this->pageList[$entity["from"]] : ""
		));

		$this->addLabel("to", array(
			"text" => (isset($entity["to"]) && is_numeric($entity["to"]) && isset($this->pageList[$entity["to"]])) ? $this->pageList[$entity["to"]] : ""
		));

		$this->addActionLink("remove_link", array(
			"link" => SOY2PageController::createLink("Plugin.Config?switch_page") . "&idx=" . $idx
		));
	}

	function setPageList($pageList){
		$this->pageList = $pageList;
	}
}
