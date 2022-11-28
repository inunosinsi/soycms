<?php

class NewsListComponent extends HTMLList{

	protected function populateItem($news){

		$this->addLabel("create_date", array(
			"text" => (isset($news["create_date"])) ? $news["create_date"] : ""
		));

		$this->addLabel("text", array(
			"text" => (isset($news["text"])) ? $news["text"] : ""
		));

		$this->addLabel("url", array(
			"text" => (isset($news["url"])) ? $news["url"] : ""
		));
	}
}
?>