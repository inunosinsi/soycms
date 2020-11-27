<?php

class CommentFormComponent extends HTMLForm{

	const SOY_TYPE = SOY2HTML::HTML_BODY;

	private $entryComment;

	function execute(){

		//cookieから読みだす：高速化キャッシュ対応のため廃止
		$array = array();
		//@parse_str($_COOKIE["soycms_comment"],$array);

		$this->addInput("title", array(
			"name" => "title",
			"value" => $this->entryComment->getTitle(),
			"soy2prefix" => "cms"
		));

		$this->addInput("author", array(
			"name" => "author",
			"value" => (strlen($this->entryComment->getAuthor()) > 0) ? $this->entryComment->getAuthor() : @$array["author"],
			"soy2prefix" => "cms"
		));

		$this->addTextArea("body", array(
			"name" => "body",
			"value" => $this->entryComment->getBody(),
			"soy2prefix" => "cms"
		));

		$this->addInput("mail_address", array(
			"name" => "mail_address",
			"value" => (strlen($this->entryComment->getMailAddress()) > 0) ? $this->entryComment->getMailAddress() : @$array["mailaddress"],
			"soy2prefix" => "cms"
		));

		$this->addInput("url", array(
			"name" => "url",
			"value" => (strlen($this->entryComment->getUrl()) > 0) ? $this->entryComment->getUrl() : @$array["url"],
			"soy2prefix" => "cms"
		));

		parent::execute();
	}


	function getEntryComment() {
		return $this->entryComment;
	}
	function setEntryComment($entryComment) {
		$this->entryComment = $entryComment;
	}
}
