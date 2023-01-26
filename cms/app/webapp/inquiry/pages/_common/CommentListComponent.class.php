<?php

class CommentListComponent extends HTMLList{

	protected function populateItem($bean){

		$this->addLabel("title", array(
			"text" => $bean->getTitle()
		));

		$this->addLabel("author", array(
			"text" => $bean->getAuthor()
		));

		$this->addLabel("content", array(
			"html" => $bean->getContent()
		));

		$this->addLabel("create_date", array(
			"text" => (is_numeric($bean->getCreateDate())) ? date("Y-m-d H:i:s", $bean->getCreateDate()) : ""
		));
	}
}
