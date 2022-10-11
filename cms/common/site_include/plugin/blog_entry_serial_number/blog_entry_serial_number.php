<?php

BlogEntrySerialNumberPlugin::registerPlugin();
class BlogEntrySerialNumberPlugin {

	const PLUGIN_ID = "blog_entry_serial_number";

	function init(){
		CMSPlugin::addPluginMenu(self::PLUGIN_ID, array(
			"name" => "ブログ記事連番プラグイン",
			"description" => "ブログの記事に連番を付けます",
			"author" => "齋藤毅",
			"url" => "https://saitodev.co/article/3170",
			"mail" => "tsuyoshi@saitodev.co",
			"version" => "0.5"
		));

		if(CMSPlugin::activeCheck(self::PLUGIN_ID)){
			//設定画面
			CMSPlugin::addPluginConfigPage(self::PLUGIN_ID, array(
				$this, "config_page"
			));

			//公開画面側
			if(defined("_SITE_ROOT_")){
				CMSPlugin::setEvent('onEntryOutput', self::PLUGIN_ID, array($this, "onEntryOutput"));
			}else{
				//
			}
		}
	}

	function onEntryOutput($arg){
		$entryId = $arg["entryId"];
		$htmlObj = &$arg["SOY2HTMLObject"];

		$className = get_class($htmlObj);
		$serialNumber = ($className == "EntryListComponent") ? self::_counter($className) + self::_offset($className) : 0;

		$htmlObj->addLabel("blog_entry_serial_number", array(
			"soy2prefix" => "cms",
			"text" => $serialNumber
		));
	}

	private function _counter(string $className){
		static $n;
		if(is_null($n)) $n = array();
		if(!isset($n[$className])) $n[$className] = 0;
		return $n[$className]++;
	}

	private function _offset(string $className){
		static $o;
		if(is_null($o)) $o = array();
		if(!isset($o[$className])){
			$o[$className] = self::_getDisplayCount() * self::_getPageNumber();
		}
		return $o[$className];
	}

	//表示件数の設定を取得
	private function _getDisplayCount(){
		$blogPage = self::_getPage();

		$thisUri = $blogPage->getUri();
		if(strlen($thisUri)) $thisUri .= "/";

		$pathInfo = (isset($_SERVER["PATH_INFO"]) && is_string($_SERVER["PATH_INFO"])) ? $_SERVER["PATH_INFO"] : "";

		//開いているページがカテゴリページか調べる
		if(strpos($pathInfo, $thisUri . $blogPage->getCategoryPageUri())){
			return (int)$blogPage->getCategoryDisplayCount();
		}

		//開いているページがアーカイブページか調べる
		if(strpos($pathInfo, $thisUri . $blogPage->getMonthPageUri())){
			return (int)$blogPage->getMonthDisplayCount();
		}

		return (int)$blogPage->getTopDisplayCount();
	}

	//ページ番号を取得
	private function _getPageNumber(){
		$pathInfo = (isset($_SERVER["PATH_INFO"]) && is_string($_SERVER["PATH_INFO"])) ? $_SERVER["PATH_INFO"] : "";
		if(!strlen($pathInfo)) return 0;
	
		preg_match('/page-(\d*)/', $pathInfo, $tmp);
		return (isset($tmp[1]) && is_numeric($tmp[1])) ? (int)$tmp[1] : 0;
	}

	private function _getPage(){
		static $pages;
		if(is_null($pages)) $pages = array();
		$pageId = (int)$_SERVER["SOYCMS_PAGE_ID"];
		if(!isset($pages[$pageId])) $pages[$pageId] = soycms_get_blog_page_object($pageId);
		return $pages[$pageId];
	}

	function config_page($message){
		SOY2::import("site_include.plugin.blog_entry_serial_number.config.BlogEntrySerialNumberConfigPage");
		$form = SOY2HTMLFactory::createInstance("BlogEntrySerialNumberConfigPage");
		$form->setPluginObj($this);
		$form->execute();
		return $form->getObject();
	}

	/**
	 * プラグインの登録
	 */
	public static function registerPlugin(){
		$obj = CMSPlugin::loadPluginConfig(self::PLUGIN_ID);
		if(is_null($obj)) $obj = new BlogEntrySerialNumberPlugin();
		CMSPlugin::addPlugin(self::PLUGIN_ID, array($obj, "init"));
	}
}
