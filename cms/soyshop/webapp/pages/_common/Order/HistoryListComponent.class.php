<?php

class HistoryListComponent extends HTMLList{

	protected function populateItem($bean){

		$this->addLabel("history_date", array(
			"text" => date("Y-m-d H:i:s", $bean->getDate())
		));

		$this->addLabel("history_content", array(
			"html" => nl2br($bean->getContent())
		));
	}
}
?>