<?php

XPublisherPlugin::register();

class XPublisherPlugin{

	const PLUGIN_ID = "x_publisher";

	//挿入するページ
	var $config_per_page = array();

	private $exts = array(
		"jpg", "jpeg", "png", "gif", "txt", "xml", "json", "css", "js", "ico"
	);

	function getId(){
		return self::PLUGIN_ID;
	}

	function init(){
		CMSPlugin::addPluginMenu(self::PLUGIN_ID,array(
			"name"=>"静的化プラグイン",
			"description"=>"主に標準ページで静的化します",
			"author"=>"齋藤毅",
			"url"=>"https://saitodev.co/article/3096",
			"mail"=>"tsuyoshi@saitodev.co",
			"version"=>"0.6"
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

				CMSPlugin::setEvent('onClearCache', self::PLUGIN_ID, array($this, "onClearCache"));
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

		//静的化の対象のページか？
		if(!isset($this->config_per_page[$page->getId()]) || $this->config_per_page[$page->getId()] != 1) return $html;

		//GETがある場合は検索ページと見なして対象外とする 緯度軽度も
		if(isset($_GET["q"]) || isset($_GET["lat"]) || isset($_GET["lng"])) return $html;

		//GETの値がある場合は対象外
		if(isset($_SERVER["REDIRECT_QUERY_STRING"]) && strpos($_SERVER["REDIRECT_QUERY_STRING"], "pathinfo") != 0) return $html;

		//URIにsearchとresultがある場所は検索結果ページと見なして、静的化の対象外とする
		if(strpos($page->getUri(), "search") !== false || strpos($page->getUri(), "result") !== false) return $html;

		switch($page->getPageType()){
			case Page::PAGE_TYPE_BLOG:
				$webPage = &$arg["webPage"];
				switch($webPage->mode){
					case CMSBlogPage::MODE_TOP:
						self::_generateStaticHTMLFile($html);
						break;
					default:
						//何もしない
				}
				break;
			case Page::PAGE_TYPE_NORMAL:
				self::_generateStaticHTMLFile($html);
				break;
			default:
				//何もしない
		}

		return $html;
	}

	private function _generateStaticHTMLFile($html){

		//末尾が指定の拡張子の場合は以後の処理を実行しない
		$reqUri = $_SERVER["REQUEST_URI"];
		foreach($this->exts as $ext){
			if(stripos($reqUri, ".".$ext)) return;
		}

		//静的の対象ページであるか？調べる
		if(!self::_checkUri($reqUri)) return;

		$currentDir = $_SERVER["DOCUMENT_ROOT"];

		$dirs = explode("/", trim($reqUri, "/"));
		if(count($dirs)){
			foreach($dirs as $dir){
				if(!strlen($dir)) break;
				if(strpos($dir, ".html") || strpos($dir, ".php")) break;

				//この２つのディレクトリは確実に関係ないたた調べるのを省く
				if($dir == "fonts" || $dir == "images") break;
				$currentDir .= "/" . $dir;
				if(!file_exists($currentDir) && strlen($currentDir) <= 100){
					mkdir($currentDir);
				}
			}
		}

		if(file_exists($currentDir)){
			//配列の最後の値がhtmlかどうかを確認する
			$lastDir = (count($dirs)) ? end($dirs) : "";
			$staticFilePath = (strlen($lastDir) && strpos($lastDir, ".html")) ? rtrim($currentDir . "/" . $lastDir, "/") : $currentDir . "/index.html";
			file_put_contents($staticFilePath, $html);
		}
	}

	function onPageUpdate($arg){
		self::_removeStaticHTMLFile();
	}

	function onEntryUpdate($arg){
		self::_removeStaticHTMLFile();
	}

	function onClearCache($obj){
		self::_removeStaticHTMLFile();
	}

	private function _checkUri($req){
		if(!is_array($this->config_per_page) || !count($this->config_per_page)) return false;
		$siteId = trim(substr(_SITE_ROOT_, strrpos(_SITE_ROOT_, "/")), "/");
		if(strpos($req, "/" . $siteId . "/") === 0) $req = str_replace("/" . $siteId. "/", "", $req);
		$req = trim($req, "/");

		if(!strlen($req)) return true;	//URIが空の場合は判定できないから常にtrueにしておく
		$pageIds = array();
		foreach($this->config_per_page as $pageId => $on){
			$pageIds[] = $pageId;
		}

		if(!count($pageIds)) return false;
		$uriList = self::_getUriList($pageIds);
		if(!count($uriList)) return false;

		foreach($uriList as $uri){
			if(strpos($req, $uri) === 0) return true;
		}

		return false;
	}

	private function _getUriList($pageIds){
		$dao = new SOY2DAO();
		try{
			$res = $dao->executeQuery("SELECT id, uri FROM Page WHERE id IN (" .implode(",", $pageIds) . ")");
		}catch(Exception $e){
			$res = array();
		}

		if(!count($res)) return array();

		$list = array();

		foreach($res as $v){
			if(!isset($v["uri"]) || !strlen($v["uri"])) continue;
			$list[(int)$v["id"]] = $v["uri"];
		}
		return $list;
	}

	private function _removeStaticHTMLFile(){
		if(!is_array($this->config_per_page) || !count($this->config_per_page)) return;

		$siteDir = UserInfoUtil::getSiteDirectory(true);
		if(UserInfoUtil::getSiteIsDomainRoot()){
			$siteDir = rtrim($siteDir, "/");
			$siteDir = substr($siteDir, 0, strrpos($siteDir, "/")) . "/";
		}

		$pageDao = SOY2DAOFactory::create("cms.PageDAO");
		foreach($this->config_per_page as $pageId => $on){
			try{
				$page = $pageDao->getById($pageId);
			}catch(Exception $e){
				continue;
			}
			$dir = $siteDir . $page->getUri();
			if(strpos($dir, ".html")){
				if(file_exists($dir)){
					unlink($file);
				}
			}else{
				$path = rtrim($dir, "/") . "/index.html";
				if(file_exists($path)){
					unlink($path);
				}
			}

			//ページャ分を削除
			self::_removeStaticHTMLBlogPagerFile($dir);
		}
	}

	private function _removeStaticHTMLBlogPagerFile($targetDir){
		$targetDir = rtrim($targetDir, "/") . "/";
		$dirs = scandir($targetDir);
		foreach($dirs as $dir){
			if(strpos($dir, ".") === 0) continue;
			if(preg_match('/page-[0-9]*/', $dir)){
				$file = $targetDir . $dir . "/index.html";
				if(file_exists($file)){
					unlink($file);
				}
			}
		}
	}

	function config_page(){
		SOY2::import("site_include.plugin.x_publisher.config.PublisherConfigPage");
		$form = SOY2HTMLFactory::createInstance("PublisherConfigPage");
		$form->setPluginObj($this);
		$form->execute();
		return $form->getObject();
	}

	public static function register(){
		$obj = CMSPlugin::loadPluginConfig(self::PLUGIN_ID);
		if(!$obj) $obj = new XPublisherPlugin();
		CMSPlugin::addPlugin(self::PLUGIN_ID, array($obj, "init"));
	}
}
