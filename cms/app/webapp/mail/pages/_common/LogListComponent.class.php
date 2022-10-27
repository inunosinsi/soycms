<?php

class LogListComponent extends HTMLList{

	protected function populateItem($entity){

		$this->addLabel("log_time", array(
			"text" => (is_numeric($entity->getTime())) ? (date("Y-m-d H:i:s", $entity->getTime())) . " - " . $entity->getContent() : "",
			"href" => "javascript:void(0);",
			"onclick" => "toggle_content('log_content_".$entity->getId()."');return 0;",
		));

		$this->addLabel("log_content", array(
			"html" => nl2br(htmlspecialchars($entity->getMore())),
			"attr:id" => "log_content_" . $entity->getId()
		));

	}
}
