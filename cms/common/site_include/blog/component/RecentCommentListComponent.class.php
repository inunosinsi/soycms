<?php

class RecentCommentListComponent extends HTMLList{

	var $entryPageUri;

	function setEntryPageUri($uri){
		$this->entryPageUri = $uri;
	}

	function populateItem($comment){

		$this->createAdd("entry_title","CMSLabel",array(
			"text" => $comment->getEntryTitle(),
			"soy2prefix" => "cms"
		));

		$this->createAdd("title","CMSLabel",array(
			"text" => $comment->getTitle(),
			"soy2prefix" => "cms"
		));

		$this->createAdd("author","CMSLabel",array(
			"text" => $comment->getAuthor(),
			"soy2prefix" => "cms"
		));

		$this->createAdd("submit_date","DateLabel",array(
			"text" => $comment->getSubmitDate(),
			"soy2prefix" => "cms"
		));
		$this->createAdd("submit_time","DateLabel",array(
			"text"=>$comment->getSubmitDate(),
			"soy2prefix"=>"cms",
			"defaultFormat"=>"H:i"
		));

		$this->addLink("entry_link", array(
			"link" => $this->entryPageUri . rawurlencode($comment->getAlias()),
			"soy2prefix" => "cms"
		));


		/* 以下1.2.8～ */
		$comment_body = str_replace("\n","@@@@__BR__MARKER__@@@@",$comment->getBody());
		$comment_body = htmlspecialchars($comment_body, ENT_QUOTES, "UTF-8");
		$comment_body = str_replace("@@@@__BR__MARKER__@@@@","<br>",$comment_body);

		$this->createAdd("body","CMSLabel",array(
			"html" => $comment_body,
			"soy2prefix" => "cms"
		));

		$this->addLink("url", array(
			"link" => $comment->getUrl(),
			"soy2prefix" => "cms"
		));

		$this->addLink("mail_address", array(
			"link" => "mailto:".$comment->getMailAddress(),
			"soy2prefix" => "cms"
		));

	}
}
