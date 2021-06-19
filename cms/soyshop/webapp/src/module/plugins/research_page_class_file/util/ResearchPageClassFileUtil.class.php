<?php

class ResearchPageClassFileUtil {

	const MODE_FILE = 1;
	const MODE_PAGE_ID = 2;

	public static function research($mode=1){
		switch($mode){
			case self::MODE_FILE:
				$dir = self::_pageClassDir();
				$files = soy2_scandir($dir);
				if(!count($files)) return array();

				$results = array();
				foreach($files as $file){
					// .phpファイルのみにする
					preg_match('/\.php$/', $file, $tmp);
					if(!count($tmp)) continue;

					if(self::_checkCorrespondenceOmission($file)){
						$results[] = $file;
					}
				}
				break;
			case self::MODE_PAGE_ID:
				$pages = SOY2DAOFactory::create("site.SOYShop_PageDAO")->get();
				if(!count($pages)) return array();

				$results = array();
				foreach($pages as $page){
					if(self::_checkIncorrectClassFileByPageObject($page)){
						$results[] = $page->getCustomClassName();
					}
				}

				break;
		}
		return $results;
	}

	public static function checkIncorrectClassFile(int $pageId){
		$page = soyshop_get_page_object($pageId);
		if(!is_numeric($page->getId())) return false;
		return self::_checkIncorrectClassFileByPageObject($page);
	}

	private static function _checkIncorrectClassFileByPageObject(SOYShop_Page $page){
		$path = SOYSHOP_SITE_DIRECTORY . ".page/" . $page->getCustomClassFileName();
		if(!file_exists($path)) return false;

		$className = $page->getCustomClassName();

		if(!class_exists("SOYShopPageBase")){
			SOY2::import("base.site.SOYShopPageBase");
			SOY2::imports("base.site.pages.*");
		}

		include_once($path);

		$ref = new ReflectionClass($className);
		if(!$ref->isSubClassOf($page->getBaseClassName())) return false;

		return self::_checkCorrespondenceOmission($className);
	}

	/**
	 * クラスファイルの変更漏れのファイルを探す
	 */
	private static function _checkCorrespondenceOmission($className){
		//.phpがあれば除く
		preg_match('/\.php$/', $className, $tmp);
		if(count($tmp)) $className = trim(str_replace(".php", "", $className));

		$path = self::_pageClassDir() . $className . ".php";
		if(!file_exists($path)) return false;

		return (is_numeric(strpos(file_get_contents($path), "function " . $className)));
	}

	private static function _pageClassDir(){
		return SOYSHOP_SITE_DIRECTORY . ".page/";
	}

	public static function save($arr){
		file_put_contents(self::_resultFilePath(), json_encode($arr));
	}

	public static function get(){
		$path = self::_resultFilePath();
		if(!file_exists($path)) return null;

		return json_decode(file_get_contents(self::_resultFilePath()), true);
	}

	private static function _resultFilePath(){
		$dir = SOYSHOP_SITE_DIRECTORY . ".research/";
		if(!file_exists($dir)) mkdir($dir);
		return $dir . "result.json";
	}
}
