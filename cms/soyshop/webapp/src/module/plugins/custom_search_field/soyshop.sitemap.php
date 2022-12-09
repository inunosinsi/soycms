<?php

class CustomSearchFieldSitemap extends SOYShopSitemapBase{

	function __construct(){}

	/**
	 * array(
	 *	array(
	 *		"loc" => "uri",
	 *		"priority" => "0.8",
	 *		"lastmod" => "timestamp",
	 *		"lngs" => array(
	 *			"lng" => array(
	 *				"uri" => "concatedUri"
	 *			)
	 *		)
	 *	)
	 *)
	 */
	function items(){
		$cnfs = self::_config();
		if(!count($cnfs)) return array();

		//カスタムサーチフィールドを設置したページを取得する
		$pages = self::_pages();
		if(!count($pages)) return array();

		$items = array();

		foreach($pages as $page){
			foreach($cnfs as $fieldId => $cnf){
				if(!isset($cnf["sitemap"]) || !is_numeric($cnf["sitemap"]) || (int)$cnf["sitemap"] !== (int)$page->getId()) continue;
				if(!isset($cnf["option"][UtilMultiLanguageUtil::LANGUAGE_JP]) || !strlen($cnf["option"][UtilMultiLanguageUtil::LANGUAGE_JP])) continue;

				$uri = $page->getUri();
				$lim = $page->getPageObject()->getLimit();

				$logic = SOY2Logic::createInstance("module.plugins.custom_search_field.logic.SearchLogic");

				foreach(explode("\n", $cnf["option"][UtilMultiLanguageUtil::LANGUAGE_JP]) as $idx => $opt){
					$opt = trim($opt);
					if(!strlen($opt)) continue;

					// 紐付いている商品の更新日の最大。カスタムサーチフィールドでフィールド値に紐付いた内、最期に更新した商品を取得
					$latestItemUpdate = $logic->getLatestItem($fieldId, $opt)->getUpdateDate();
					if(!is_numeric($latestItemUpdate)) $latestItemUpdate = $page->getUpdateDate();

					$csfUri = $uri . "/" . $fieldId . "/" . $opt;

					//多言語化用
					$lngs = (SOYShopPluginUtil::checkIsActive("util_multi_language")) ? self::_buildLngUrlList($uri, $fieldId, $idx, $cnf["option"]) : array();

					$items[] = array(
						"loc" => $csfUri,
						"priority" => "0.5",
						"lastmod" => $latestItemUpdate,
						"lngs" => $lngs
					);

					//ページャ
					$total = $logic->countItemList($fieldId, $opt);
					$div = (int)ceil($total / $lim);
					if($div < 2) continue;

					for($i = 2; $i <= $div; $i++){
						//多言語化の値にページャを付ける
						if(count($lngs)){
							foreach($lngs as $lng => $lngValues){
								$lngs[$lng]["uri"] = $lngValues["uri"] . "/page-" . $i . ".html";
							}
						}

						$items[] = array(
							"loc" => $csfUri . "/page-" . $i . ".html",
							"priority" => "0.3",
							"lastmod" => $latestItemUpdate,
							"lngs" => $lngs
						);
					}
				}
			}
		}

		return $items;
	}

	private function _pages(){
		try{
			$pages = SOY2DAOFactory::create("site.SOYShop_PageDAO")->getByType(SOYShop_Page::TYPE_LIST);
			if(!count($pages)) return array();
		}catch(Exception $e){
			return array();
		}

		$list = array();
		foreach($pages as $page){
			if($page->getObject()->getType() != "custom") continue;
			if($page->getObject()->getModuleId() != "custom_search_field") continue;
			$list[(int)$page->getId()] = $page;
		}
		unset($pages);

		return $list;
	}

	private function _buildLngUrlList(string $uri, string $fieldId, int $idx, array $csfCnf){
		SOY2::import("module.plugins.util_multi_language.util.UtilMultiLanguageUtil");
		$lngs = array_keys(UtilMultiLanguageUtil::allowLanguages());
		if(count($lngs) < 2) return array();

		$list = array();
		foreach($lngs as $lng){
			if($lng == UtilMultiLanguageUtil::LANGUAGE_JP || !isset($csfCnf[$lng]) || !self::_isMultiLanguagePage($uri, $lng)) continue;
			$multiOpt = self::_getMultiLanguageOption($idx, $csfCnf[$lng]);
			if(!strlen($multiOpt)) continue;

			$list[$lng] = array(
				"uri" => $uri . "/" . $fieldId . "/" . $multiOpt
			);
		}

		return $list;
	}

	//指定のページに該当するの多言語ページはあるか？
	private function _isMultiLanguagePage(string $uri, string $lng){
		$filename = $lng . "_" . str_replace(array("/", "."), "_", $uri) . "_page.php";
		$filename = str_replace("__", "_", $filename);
		return file_exists(SOYSHOP_SITE_DIRECTORY . ".page/" . $filename);
	}

	//指定のcsf値に該当する多言語用のcsf値はあるか？
	private function _getMultiLanguageOption(int $idx, string $optRaw){
		$optRaw = trim($optRaw);
		if(!strlen($optRaw)) return null;
		$opts = explode("\n", $optRaw);
		if(!count($opts)) return null;

		return (isset($opts[$idx])) ? trim($opts[$idx]) : null;

	}

	private function _config(){
		static $cnf;
		if(is_null($cnf)){
			SOY2::import("module.plugins.custom_search_field.util.CustomSearchFieldUtil");
			$cnf = CustomSearchFieldUtil::getConfig();
		}
		return $cnf;
	}
}
SOYShopPlugin::extension("soyshop.sitemap", "custom_search_field", "CustomSearchFieldSitemap");
