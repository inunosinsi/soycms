<?php

class ChangeHistoryListComponent extends HTMLList {
	protected function populateItem($entity){

		$this->addLabel("history_date", array(
			"text" => (isset($entity["date"]) && is_numeric($entity["date"])) ? date("Y-m-d H:i:s", $entity["date"]) : ""
		));

		$this->addLabel("history_content", array(
			"text" => (isset($entity["content"])) ? $entity["content"] : ""
		));
	}
}
