<?php

class TrackbackListComponent extends HTMLList{

	function getStartTag(){
		return '<a name="trackback_list"></a>'.parent::getStartTag();
	}

	function populateItem($trackback){

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
			"html"=> str_replace("\n","<br>", htmlspecialchars($trackback->getExcerpt(), ENT_QUOTES, "UTF-8")),
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
	}
}
