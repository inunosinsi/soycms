<?php

class SOYShopCommonSearchTitleFormat extends SOYShopTitleFormatBase{

	const FORMAT = "%SEARCH_WORD%";

	function titleFormatOnSearchPage(){
		preg_match('/\d.*/', $_SERVER["REQUEST_URI"], $tmp);
		if(!isset($tmp[0]) || !is_numeric($tmp[0])) return array();

		$page = soyshop_get_page_object((int)$tmp[0]);
		if($page->getType() != SOYShop_Page::TYPE_SEARCH || $page->getPageObject()->getModule() != "common_search") return array();

		return array(
			array(
				"label" => "検索ワード",
				"format" => self::FORMAT
			)
		);
	}

	function convertOnSearchPage(string $title){
		if(soy2_strpos($title, self::FORMAT) < 0) return $title;

		$q = (isset($_GET["q"]) && is_string($_GET["q"])) ? htmlspecialchars(trim($_GET["q"]), ENT_QUOTES, "UTF-8") : "";
		return str_replace(self::FORMAT, $q, $title);
	}
}
SOYShopPlugin::extension("soyshop.title.format", "common_search", "SOYShopCommonSearchTitleFormat");
