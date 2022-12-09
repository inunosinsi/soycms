<?php

class HistoryListComponent extends HTMLList{

	protected function populateItem($bean){

		$this->addLabel("history_date", array(
			"text" => (is_numeric($bean->getDate())) ? date("Y-m-d H:i:s", $bean->getDate()) : ""
		));

		//対応者
		$this->addLabel("history_author", array(
			"text" => $bean->getAuthor()
		));

		$this->addLabel("history_content", array(
			"html" => nl2br($bean->getContent())
		));
	}
}
?>
