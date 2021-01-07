<?php
HTMLCachePlugin::register();

class HTMLCachePlugin{

	const PLUGIN_ID = "x_html_cache";

	//挿入するページ
	var $config_per_page = array();
	var $config_per_blog = array();

	function getId(){
		return self::PLUGIN_ID;
	}

	function init(){
		CMSPlugin::addPluginMenu(self::PLUGIN_ID,array(
			"name"=>"HTMLキャッシュプラグイン",
			"description"=>"サーバでページを組み立てブラウザにレスポンスを返す直前の状態をキャッシュ化してページの表示速度の高速化を図る",
			"author"=>"齋藤毅",
			"url"=>"https://saitodev.co/article/3096",
			"mail"=>"tsuyoshi@saitodev.co",
			"version"=>"0.8"
		));
		CMSPlugin::addPluginConfigPage(self::PLUGIN_ID,array(
			$this,"config_page"
		));

		if(CMSPlugin::activeCheck(self::PLUGIN_ID)){
			//管理画面側
			if(!defined("_SITE_ROOT_")){
				CMSPlugin::setEvent('onPageUpdate', self::PLUGIN_ID, array($this, "onPageUpdate"));
				CMSPlugin::setEvent('onPageRemove', self::PLUGIN_ID, array($this, "onPageUpdate"));

				CMSPlugin::setEvent('onEntryUpdate', self::PLUGIN_ID, array($this, "onEntryUpdate"));
				CMSPlugin::setEvent('onEntryCreate', self::PLUGIN_ID, array($this, "onEntryUpdate"));
				CMSPlugin::setEvent('onEntryRemove', self::PLUGIN_ID, array($this, "onEntryUpdate"));
				CMSPlugin::setEvent('onEntryCopy', self::PLUGIN_ID, array($this, "onEntryUpdate"));
			//公開側
			}else{
				CMSPlugin::setEvent('onOutput',self::PLUGIN_ID, array($this,"onOutput"), array("filter"=>"all"));
			}
		}
	}

	function onOutput($arg){
		$html = &$arg["html"];
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
				self::_generateStaticHTMLCacheFile($html);
				break;
			case Page::PAGE_TYPE_BLOG:
				$webPage = &$arg["webPage"];
				switch($webPage->mode){
					case CMSBlogPage::MODE_TOP:
					case CMSBlogPage::MODE_ENTRY:
					case CMSBlogPage::MODE_MONTH_ARCHIVE:
					case CMSBlogPage::MODE_CATEGORY_ARCHIVE:
						if(isset($this->config_per_blog[$page->getId()][$webPage->mode]) && $this->config_per_blog[$page->getId()][$webPage->mode] == 1){
							self::_generateStaticHTMLCacheFile($html);
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
	private function _generateStaticHTMLCacheFile($html){
		//トレイリングスラッシュ対策で末尾のスラッシュを抜いておく
		$pathInfo = (isset($_SERVER["PATH_INFO"])) ? rtrim($_SERVER["PATH_INFO"], "/") : "_top";
		$alias = trim(substr($pathInfo, strrpos($pathInfo, "/")), "/");

		$dir = _SITE_ROOT_ . "/.cache/static_cache/";
		if(!file_exists($dir)) mkdir($dir);

		if(is_numeric($alias)){
			$dir .= "n/";
			if(!file_exists($dir)) mkdir($dir);
		}else{
			$dir .= "s/";
			if(!file_exists($dir)) mkdir($dir);
		}

		$hash = md5($pathInfo);
		for($i = 0; $i < 10; ++$i){
			$dir .= substr($hash, 0, 1) . "/";
			if(!file_exists($dir)) mkdir($dir);
			$hash = substr($hash, 1);
		}

		file_put_contents($dir . $hash . ".html", $html);
	}

	function onPageUpdate($arg){
		//static_cacheの削除
		self::_removeCache();
	}

	function onEntryUpdate($arg){
		//static_cacheの削除
		self::_removeCache();
	}

	private function _removeCache(){
		$staticCacheDir = UserInfoUtil::getSiteDirectory(true) . ".cache/static_cache/";
		if(file_exists($staticCacheDir)) self::_deleteDir($staticCacheDir);
	}

	private function _deleteDir($path){
		//ディレクトリ指定でスラッシュがあれば除去
		$path = rtrim($path, "/");
		//指定されたディレクトリの中身一覧取得
		$list = glob($path . "/*");

		foreach($list as $key => $value){
			//ディレクトリ(フォルダ)なら再帰呼出し
			if(is_dir($value)){
				self::_deleteDir($value);
			//ファイルなら削除
			}else{
				unlink($value);
			}
		}

		//指定されたディレクトリの中身が空ならディレクトリ削除して終了
		$list = glob($path . "/*");
		if(count($list) === 0){
			rmdir($path);
			return;
		}
	}

	function config_page(){
		SOY2::import("site_include.plugin.x_html_cache.config.HTMLCacheConfigPage");
		$form = SOY2HTMLFactory::createInstance("HTMLCacheConfigPage");
		$form->setPluginObj($this);
		$form->execute();
		return $form->getObject();
	}

	public static function register(){
		$obj = CMSPlugin::loadPluginConfig(self::PLUGIN_ID);
		if(!$obj) $obj = new HTMLCachePlugin();
		CMSPlugin::addPlugin(self::PLUGIN_ID, array($obj, "init"));
	}
}
