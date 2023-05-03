<?php

function soyshop_simple_news($html, $htmlObj){


	$obj = $htmlObj->create("soyshop_simple_news", "HTMLTemplatePage", array(
		"arguments" => array("soyshop_simple_news", $html)
	));

	$news = SOYShop_DataSets::get("plugin.simple_news", array());
	
	$obj->createAdd("news_list", "SimpleNewsListComponent", array(
		"list" => $news,
		"soy2prefix" => SOYSHOP_SITE_PREFIX,//互換性のため残しておく
	));

	$obj->createAdd("news_list", "SimpleNewsListComponent", array(
		"list" => $news,
		"soy2prefix" => "block",
	));


	//商品があるときだけ表示
	if(is_array($news) && count($news) > 0){
		$obj->display();
	}else{
		ob_start();
		$obj->display();
		ob_end_clean();
	}
}

if(!class_exists("SimpleNewsListComponent")){

	class SimpleNewsListComponent extends HTMLList {
		protected function populateItem($entity){
			$url = (isset($entity["url"]) && is_string($entity["url"])) ? $entity["url"] : "";
			if(strlen($url) && preg_match('/\/\/$/', $url)) $url = "";	// $urlが//で終わっている場合は空文字にする
			
			$createDate = (isset($entity["create_date"]) && is_string($entity["create_date"]) && preg_match('/[\d]{4}-[\d]{1,2}-[\d]{1,2}/', $entity["create_date"])) ? $entity["create_date"] : "";

			$this->addLabel("create_date", array(
				"soy2prefix" => SOYSHOP_SITE_PREFIX, 
				"text" => $createDate
			));

			$txt = (isset($entity["text"])) ? $entity["text"] : "";
			$this->addLabel("title", array(
				"soy2prefix" => SOYSHOP_SITE_PREFIX,
				"html" => (strlen($url) > 0) ? "<a href=\"".$url."\">" . $txt . "</a>" : $txt
			));
		}
	}
}
