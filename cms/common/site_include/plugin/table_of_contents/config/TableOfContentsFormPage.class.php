<?php

class TableOfContentsFormPage extends WebPage {

	private $pluginObj;
	const FILE_NAME = "table_of_contents_sample.jpg";

	function __costruct(){
	}

	function execute(){
		self::moveSampleImage();

		parent::__construct();

		$this->addImage("sample", array(
			"src" => UserInfoUtil::getSiteUrl() . "sample/" . self::FILE_NAME
		));
	}

	private function moveSampleImage(){
		$dir = UserInfoUtil::getSiteDirectory() . "sample/";
		if(!file_exists($dir)){
			mkdir($dir);
		}

		$dist = $dir . self::FILE_NAME;
		if(!file_exists($dist)){
			$source = dirname(dirname(__FILE__)) . "/img/" . self::FILE_NAME;
			copy($source, $dist);
		}
	}

	function setPluginObj($pluginObj){
		$this->pluginObj = $pluginObj;
	}
}
