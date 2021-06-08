<?php
/*
 * パン屑リスト出力プラグイン
 *
 */

class BreadPlugin{

	const PLUGIN_ID = "bread";

	var $separetor = "&gt;";

	function setCms_separetor($separetor){
		$this->separetor = $separetor;
	}

	/**
	 * ×separetor
	 * ○separator
	 */
	function setCms_separator($separetor){
		$this->separetor = $separetor;
	}

	function getId(){
		return self::PLUGIN_ID;
	}

	function init(){
		CMSPlugin::addPluginMenu(self::PLUGIN_ID,array(
			"name" => "パン屑リスト出力プラグイン",
			"description" => "パン屑リストを出力することが出来ます。",
			"author" => "株式会社Brassica",
			"url" => "https://brassica.jp/",
			"mail" => "soycms@soycms.net",
			"version" => "1.4"
		));

		if(CMSPlugin::activeCheck(self::PLUGIN_ID)){

			CMSPlugin::addPluginConfigPage(self::PLUGIN_ID, array(
				$this, "config_page"
			));
			CMSPlugin::addBlock($this->getId(), "page", array(
				$this, "block"
			));
		}
	}

	function config_page($message){
		return file_get_contents(dirname(__FILE__)."/info.html");
	}

	function block($html, $pageId){

		//ブログページがどれ程あるか？を事前に調べておく
		$blogPageIdList = self::_getBlogPageIdList();

		$buff = array();

		for(;;){
			//ブログページの場合
			if(count($blogPageIdList) && is_numeric(array_search($pageId, $blogPageIdList))){
				try{
					$page = self::_blogDao()->getById($pageId);
				}catch(Exception $e){
					break;
				}

				//記事毎ページやアーカイブページの時の対応
				if(!count($buff) && isset($_SERVER["BLOG_PAGE_MODE"])){
					$reqUri = $_SERVER["REQUEST_URI"];
					switch($_SERVER["BLOG_PAGE_MODE"]){
						case BlogPage::MODE_ENTRY:
							try{
								$buff[] = SOY2DAOFactory::create("cms.EntryDAO")->getByAlias(self::_getAliasByUri($reqUri))->getTitle();
							}catch(Exception $e){
								//
							}
							break;
						case BlogPage::MODE_CATEGORY:
							try{
								$buff[] = SOY2DAOFactory::create("cms.LabelDAO")->getByAlias(self::_getAliasByUri($reqUri))->getCaption();
							}catch(Exception $e){
								//
							}
							break;
						case BlogPage::MODE_ARCHIVE:
							$dateArr = self::_getDateArray($reqUri);
							$dateStr = "";
							if(count($dateArr)){
								$dateStr = $dateArr[0] . "年";
								if(isset($dateArr[1])) $dateStr .= $dateArr[1] . "月";
								if(isset($dateArr[2])) $dateStr .= $dateArr[2] . "日";
							}
							$buff[] = $dateStr;
							break;
					}
				}
			}else{
				try{
					$page = self::_dao()->getById($pageId);
				}catch(Exception $e){
					break;
				}
			}

			if(!count($buff)){
				$buff[] = $page->getTitle();
			}else{
				if(defined("CMS_PREVIEW_MODE")){
					$link = SOY2PageController::createLink("Page.Preview") ."/". $page->getId();
				}else{
					$link = SOY2PageController::createLink("") . $page->getUri();
				}
				$buff[] = '<a href="'.htmlspecialchars($link, ENT_QUOTES, "UTF-8").'">'.htmlspecialchars($page->getTitle(), ENT_QUOTES, "UTF-8").'</a>';
			}

			$pageId = $page->getParentPageId();
			if(!is_numeric($pageId)) break;
		}

		$buff = array_reverse($buff);

		return implode($this->separetor, $buff);
	}

	private function _getBlogPageIdList(){
		try{
			$res = self::_dao()->executeQuery("SELECT id FROM Page WHERE page_type = " . Page::PAGE_TYPE_BLOG . " AND isPublished = " . Page::PAGE_ACTIVE);
		}catch(Exception $e){
			$res = array();
		}
		if(!count($res)) return array();

		$list = array();
		foreach($res as $v){
			$list[] = (int)$v["id"];
		}
		return $list;
	}

	private function _getAliasByUri($uri){
		$uri = rtrim($uri, "/");
		$alias = trim(substr($uri, strrpos($uri, "/")), "/");
		if(is_numeric(strpos($alias, "%"))) $alias = rawurldecode($alias);
		return $alias;
	}

	private function _getDateArray($uri){
		$arr = explode("/", $uri);
		$values = array();
		$isDateValue = false;
		foreach($arr as $v){
			$v = trim($v);
			if(!$isDateValue && strlen($v) === 4){
				//4桁の数字であれば$isDateValueをtrueにする
				preg_match('/^[\d]{4}/', $v, $tmp);
				if(isset($tmp[0])) $isDateValue = true;
			}

			if($isDateValue){
				$values[] = (int)$v;
			}

			if(count($values) >= 3) break;
		}

		return $values;
	}

	private function _dao(){
		static $dao;
		if(is_null($dao)) $dao = SOY2DAOFactory::create("cms.PageDAO");
		return $dao;
	}

	private function _blogDao(){
		static $dao;
		if(is_null($dao)) $dao = SOY2DAOFactory::create("cms.BlogPageDAO");
		return $dao;
	}

	public static function register(){
		$obj = CMSPlugin::loadPluginConfig(self::PLUGIN_ID);
		if(is_null($obj)) $obj = new BreadPlugin();
		CMSPlugin::addPlugin(self::PLUGIN_ID, array($obj, "init"));
	}
}
BreadPlugin::register();
