<?php
HTMLBackupPlugin::register();

class HTMLBackupPlugin{

	const PLUGIN_ID = "x_html_backup";
	var $config_per_page = array();
	var $config_per_blog = array();

	function getId(){
		return self::PLUGIN_ID;
	}

	function init(){
		CMSPlugin::addPluginMenu(self::PLUGIN_ID,array(
			"name"=>"HTMLファイルバックアッププラグイン",
			"type" => Plugin::TYPE_OPTIMIZE,
			"description"=>"静的化プラグインやHTMLキャッシュプラグインで生成したHTMLファイルをダウンロードできる",
			"author"=>"齋藤毅",
			"url"=>"",
			"mail"=>"tsuyoshi@saitodev.co",
			"version"=>"0.5"
		));
		CMSPlugin::addPluginConfigPage(self::PLUGIN_ID,array(
			$this,"config_page"
		));

		if(CMSPlugin::activeCheck(self::PLUGIN_ID)){
			//公開側
			if(defined("_SITE_ROOT_")){
				CMSPlugin::setEvent('onOutput',self::PLUGIN_ID, array($this,"onOutput"), array("filter"=>"all"));
			}
		}
	}

	function onOutput($arg){
		$html = &$arg["html"];

		// GETパラメータがある場合はどんな場合でも対象外
		if(count($_GET) > 0) return $html;
		
		$page = &$arg["page"];

		//アプリケーションページと404ページの場合は静的化しない
		if($page->getPageType() == Page::PAGE_TYPE_APPLICATION || $page->getPageType() == Page::PAGE_TYPE_ERROR) return $html;

		//HTMLCacheの対象ページであるか？
		if(!isset($this->config_per_page[$page->getId()]) || $this->config_per_page[$page->getId()] != 1) return $html;

		//GETがある場合は検索ページと見なして対象外とする
		if(isset($_GET["q"])) return $html;

		//GETの値がある場合は対象外
		if(isset($_SERVER["REDIRECT_QUERY_STRING"]) && strpos($_SERVER["REDIRECT_QUERY_STRING"], "pathinfo") != 0) return $html;

		//URIにsearchとresultがある場所は検索結果ページと見なして、静的化の対象外とする
		if(strpos($page->getUri(), "search") !== false || strpos($page->getUri(), "result") !== false) return $html;

		switch($page->getPageType()){
			case Page::PAGE_TYPE_NORMAL:
				self::_generateBackupFile($html);
				break;
			case Page::PAGE_TYPE_BLOG:
				switch(SOYCMS_BLOG_PAGE_MODE){
					case CMSBlogPage::MODE_TOP:
					case CMSBlogPage::MODE_ENTRY:
					case CMSBlogPage::MODE_MONTH_ARCHIVE:
					case CMSBlogPage::MODE_CATEGORY_ARCHIVE:
						if(isset($this->config_per_blog[$page->getId()][SOYCMS_BLOG_PAGE_MODE]) && $this->config_per_blog[$page->getId()][SOYCMS_BLOG_PAGE_MODE] == 1){
							self::_generateBackupFile($html);
							break;
						}
					case CMSBlogPage::MODE_RSS:
					case CMSBlogPage::MODE_POPUP:
						return $html;
				}
				break;
			case Page::PAGE_TYPE_APPLICATION:
			default:
				//何もしない
		}

		return $html;
	}

	//HTMLCache
	private function _generateBackupFile(string $html){
		$pathinfo = (isset($_SERVER["PATH_INFO"])) ? $_SERVER["PATH_INFO"] : "/";
		SOY2Logic::createInstance("site_include.plugin.x_html_backup.logic.HTMLBackupLogic")->save($html, $pathinfo);
	}

	function config_page(){
		SOY2::import("site_include.plugin.x_html_backup.config.HTMLBackupConfigPage");
		$form = SOY2HTMLFactory::createInstance("HTMLBackupConfigPage");
		$form->setPluginObj($this);
		$form->execute();
		return $form->getObject();
	}

	public static function register(){
		$obj = CMSPlugin::loadPluginConfig(self::PLUGIN_ID);
		if(!$obj) $obj = new HTMLBackupPlugin();
		CMSPlugin::addPlugin(self::PLUGIN_ID, array($obj, "init"));
	}
}
