<?php

class SimpleNewsAreaPage extends WebPage{

	private $configObj;

	function __construct(){}

	function execute(){
		parent::__construct();

		$news = SOYShop_DataSets::get("plugin.simple_news", array());
		if(!is_array($news)) $news = array();

		DisplayPlugin::toggle("has_news", (count($news) > 0));
		DisplayPlugin::toggle("no_news", (count($news) === 0));

		$this->createAdd("news_list", "_common.Plugin.NewsListComponent", array(
			"list" => $news
		));
	}

	function setConfigObj($configObj){
		$this->configObj = $configObj;
	}
}
