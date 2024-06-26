<?php

class GoogleAnalyticsUtil{

	//挿入箇所
	const INSERT_INTO_THE_BEGINNING_OF_HEAD = 5;	//<head>直後に挿入
	const INSERT_INTO_THE_END_OF_HEAD = 2;	//</head>直前に挿入
	const INSERT_INTO_THE_BEGINNING_OF_BODY = 1;	//<body>直後に挿入
	const INSERT_INTO_THE_END_OF_BODY = 3;	//</body>直前に挿入
	const INSERT_AFTER_THE_END_OF_BODY = 0;	//</body>直後に挿入
	const INSERT_INTO_THE_END_OF_HTML = 4;	//HTMLの末尾に挿入

	const INSERT_TAG_DISPLAY = 1;
	const INSERT_TAG_NOT_DISPLAY = 0;

	public static function getConfig(){
		return SOYShop_DataSets::get("google_analytics", array(
			"tracking_code" => "",
			"insert_to_head" => self::INSERT_AFTER_THE_END_OF_BODY,
			"gtm" => array("header" => "", "body" => "")
		));
	}

	public static function saveConfig($values){
		SOYShop_DataSets::put("google_analytics", $values);
	}

	public static function getPageDisplayConfig(){
		$cnf = SOYShop_DataSets::get("google_analytics_page_config", null);
		if(is_null($cnf)){
			$cnf = array();

			$pageIds = array_keys(soyshop_get_page_list());
			if(!count($pageIds)) return array();
			foreach($pageIds as $pageId){
				$cnf[$pageId] = self::INSERT_TAG_DISPLAY;
			}
		}

		return $cnf;
	}

	public static function savePageDisplayConfig($array){

		$pageIds = array_keys(soyshop_get_page_list());

		$config = array();
		foreach($pageIds as $pageId){
			$config[$pageId] = (in_array($pageId, $array)) ? self::INSERT_TAG_DISPLAY : self::INSERT_TAG_NOT_DISPLAY;
		}
		SOYShop_DataSets::put("google_analytics_page_config", $config);
	}
}
