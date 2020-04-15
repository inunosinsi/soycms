<?php

HTMLCachePlugin::register();

class HTMLCachePlugin{

	const PLUGIN_ID = "html_cache";

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
			"version"=>"0.1"
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

		//GETがある場合は検索ページと見なして対象外とする
		if(isset($_GET["q"])) return $html;

		//@ToDo そのうち禁止するURLの設定を行いたい	cms:module="common.entry_calendar"を使用している場合は静的化を禁止
		if(strpos($html, "cms:blog=")) return $html;

		//GETの値がある場合は対象外
		if(isset($_SERVER["REDIRECT_QUERY_STRING"]) && strpos($_SERVER["REDIRECT_QUERY_STRING"], "pathinfo") != 0) return $html;

		//URIにsearchとresultがある場所は検索結果ページと見なして、静的化の対象外とする
		if(strpos($page->getUri(), "search") !== false || strpos($page->getUri(), "result") !== false) return $html;

		switch($page->getPageType()){
			case Page::PAGE_TYPE_NORMAL:
				self::_generateStaticHTMLFile($html);
				break;
			case Page::PAGE_TYPE_BLOG:
				//ブログの記事詳細の場合は少し趣向を変える /サイトID/.cache/ページID/記事ID.html
				$webPage = &$arg["webPage"];
				switch($webPage->mode){
					case CMSBlogPage::MODE_ENTRY:
					case CMSBlogPage::MODE_MONTH_ARCHIVE:
					case CMSBlogPage::MODE_CATEGORY_ARCHIVE:
						self::_generateStaticHTMLFile($html);
						break;
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

	// /サイトID/.cache/ページID/記事ID.html
	private function _generateStaticHTMLFile($html){
		$pathInfo = (isset($_SERVER["PATH_INFO"])) ? $_SERVER["PATH_INFO"] : "_top";
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
		for($i = 0; $i < 10; $i++){
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
		return "/ルート/サイトID/index.php内のexecute_site();をexecute_site_static_cache();に変更するとブログページの記事詳細も静的化の対象になります。";
	}

	public static function register(){

		$obj = CMSPlugin::loadPluginConfig(self::PLUGIN_ID);
		if(!$obj){
			$obj = new HTMLCachePlugin();
		}

		CMSPlugin::addPlugin(self::PLUGIN_ID, array($obj, "init"));
	}
}
