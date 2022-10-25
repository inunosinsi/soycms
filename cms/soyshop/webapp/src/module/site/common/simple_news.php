<?php

function soyshop_simple_news($html, $htmlObj){


	$obj = $htmlObj->create("soyshop_simple_news", "HTMLTemplatePage", array(
		"arguments" => array("soyshop_simple_news", $html)
	));

	$news = SOYShop_DataSets::get("plugin.simple_news", array());

	$obj->createAdd("news_list", "HTMLList", array(
		"list" => $news,
		"soy2prefix" => SOYSHOP_SITE_PREFIX,//互換性のため残しておく
		'populateItem:function($array,$key)' =>
				'$url = (isset($array["url"]) && is_string($array["url"])) ? $array["url"] : "";' .
				'$createDate = (isset($array["create_date"]) && is_numeric($array["create_date"])) ? (int)$array["create_date"] : 0;'.
				'$this->createAdd("create_date","HTMLLabel", array("soy2prefix" => SOYSHOP_SITE_PREFIX, "text" => $createDate));' .
				'$this->createAdd("title","HTMLLabel", array("soy2prefix" => SOYSHOP_SITE_PREFIX, "html" => (strlen($url) > 0) ? "<a href=\"".$url."\">" . $array["text"] . "</a>" : @$array["text"]));'
	));

	$obj->createAdd("news_list", "HTMLList", array(
		"list" => $news,
		"soy2prefix" => "block",
		'populateItem:function($array,$key)' =>
				'$url = (isset($array["url"]) && is_string($array["url"])) ? $array["url"] : "";' .
				'$createDate = (isset($array["create_date"]) && is_numeric($array["create_date"])) ? (int)$array["create_date"] : 0;'.
				'$this->createAdd("create_date","HTMLLabel", array("soy2prefix" => SOYSHOP_SITE_PREFIX, "text" => $createDate));' .
				'$this->createAdd("title","HTMLLabel", array("soy2prefix" => SOYSHOP_SITE_PREFIX, "html" => (strlen($url) > 0) ? "<a href=\"".$url."\">" . $array["text"] . "</a>" : @$array["text"]));'
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
