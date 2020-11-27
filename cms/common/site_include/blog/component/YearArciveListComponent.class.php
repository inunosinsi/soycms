<?php

/**
 * 月別アーカイブを表示
 */
class YearArciveListComponent extends HTMLList{

	var $yearPageUri;
	var $format;

	function setYearPageUri($uri){
		$this->yearPageUri = $uri;
	}

	function setFormat($format){
		$this->format = $format;
	}

	protected function populateItem($count,$key){

		$this->addLink("archive_link", array(
			"link" => $this->yearPageUri . date('Y',$key),
			"soy2prefix"=>"cms"
		));

		$this->createAdd("archive_year","DateLabel",array(
			"text"=>$key,
			"soy2prefix"=>"cms",
			"defaultFormat"=>"Y年"
		));
		$this->createAdd("entry_count","CMSLabel",array(
			"text"=>$count,
			"soy2prefix"=>"cms"
		));

	}

}
