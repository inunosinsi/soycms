<?php

class RecentTrackBackListComponent extends HTMLList{

	var $entryPageUri;

	function setEntryPageUri($uri){
		$this->entryPageUri = $uri;
	}

	function populateItem($trackback){
		$link = $this->entryPageUri . rawurlencode($trackback->getAlias());

		$this->createAdd("title","CMSLabel",array(
			"text"=>$trackback->getTitle(),
			"soy2prefix" => "cms"
		));
		$this->addLink("url", array(
			"link"=>$trackback->getUrl(),
			"soy2prefix" => "cms"
		));
		$this->createAdd("blog_name","CMSLabel",array(
			"text"=>$trackback->getBlogName(),
			"soy2prefix" => "cms"
		));
		$this->createAdd("excerpt","CMSLabel",array(
			"text"=>$trackback->getExcerpt(),
			"soy2prefix" => "cms"
		));
		$this->createAdd("submit_date","DateLabel",array(
			"text"=>$trackback->getSubmitdate(),
			"soy2prefix" => "cms"
		));
		$this->createAdd("submit_time","DateLabel",array(
			"text"=>$trackback->getSubmitdate(),
			"soy2prefix"=>"cms",
			"defaultFormat"=>"H:i"
		));
		$this->addLink("entry_link", array(
			"link"=>$link,
			"soy2prefix"=>"cms"
		));

		$this->createAdd("entry_title","CMSLabel",array(
			"text"=>$trackback->getEntryTitle(),
			"soy2prefix"=>"cms"
		));
	}
}
