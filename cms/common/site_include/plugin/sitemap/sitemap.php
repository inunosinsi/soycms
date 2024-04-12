<?php
/*
 * Created on 2009/06/12
 *
 * To change the template for this generated file go to
 * Window - Preferences - PHPeclipse - PHP - Code Templates
 */

SitemapPlugin::register();

class SitemapPlugin{

	const PLUGIN_ID = "sitemap";
	const PAGE_TYPE_NORMAL = 0;
	const PAGE_TYPE_BLOG_TOP = 1;
	const PAGE_TYPE_BLOG_CATEGORY = 2;
	const PAGE_TYPE_BLOG_ARCHIVE = 3;
	const PAGE_TYPE_BLOG_ENTRY = 4;
	const PAGE_TYPE_APPLICATION = 5;
	const PAGE_TYPE_OTHER = 6;

	//挿入しないページ
	//Array<ページID => 0 | 1> 挿入しないページが1
	var $config_per_page = array();
	//Array<ページID => Array<ページタイプ => 0 | 1>> 挿入しないページが1
	var $config_per_blog = array();

	var $ssl_per_page = array();
	//Array<ページID => Array<ページタイプ => 0 | 1>> 挿入しないページが1
	var $ssl_per_blog = array();

	var $urls = array();

	// 多言語化の有無
	private $multiLanguageConfs = array();
	
	// サイトID
	private $siteId = "";

	function getId(){
		return self::PLUGIN_ID;
	}

	function init(){
		CMSPlugin::addPluginMenu(self::PLUGIN_ID,array(
			"name"=>"サイトマッププラグイン",
			"type" => Plugin::TYPE_SITE,
			"description"=>"",
			"author"=>"齋藤毅",
			"url"=>"http://saitodev.co",
			"mail"=>"tsuyoshi@saitodev.co",
			"version"=>"1.7"
		));
		CMSPlugin::addPluginConfigPage(self::PLUGIN_ID,array(
			$this,"config_page"
		));

		if(CMSPlugin::activeCheck(self::PLUGIN_ID)){
			CMSPlugin::setEvent('onPageOutput', self::PLUGIN_ID, array($this, "onPageOutput"));
		}
	}

	function config_page(){
		SOY2::import("site_include.plugin.sitemap.config.SitemapConfigFormPage");
		$form = SOY2HTMLFactory::createInstance("SitemapConfigFormPage");
		$form->setPluginObj($this);
		$form->execute();
		return $form->getObject();
	}

	function onPageOutput($obj){

		$xml = array();

		//サイトマップの時のみ
		if(soy2_strpos($obj->page->getUri(), "sitemap.xml") >= 0){
			
			//多言語プラグイン関連の設定
			self::_setMultiLanguageConfig();

			header("Content-Type: text/xml");

			//全ページ取得
			$pageDao = soycms_get_hash_table_dao("page");
			try{
				$res = $pageDao->executeQuery(
					"SELECT id, uri, page_type, page_config, udate FROM Page ".
					"WHERE isPublished != 0 ".
					"AND openPeriodStart < :start ".
					"AND openPeriodEnd > :end", 
					array(":start" => time(), ":end" => time())
				);
			}catch(Exception $e){
				$res = array();
			}

			if(count($res)){
				$pages = array();
				foreach($res as $row){
					$pages[] = $pageDao->getObject($row);
				}

				$host = $_SERVER["HTTP_HOST"];

				//ルート設定があるか調べる
				$siteId = trim(substr(_SITE_ROOT_, strrpos(_SITE_ROOT_, "/")), "/");
				$old = CMSUtil::switchDsn();
				try{
					$site = SOY2DAOFactory::create("admin.SiteDAO")->getBySiteId($siteId);
				}catch(Exception $e){
					$site = new Site();
				}
				CMSUtil::resetDsn($old);

				//ルート設定ではない場合は$hostにsiteIdを追加する
				if(!$site->getIsDomainRoot()) {
					$this->siteId = $siteId; 
					$host .= "/" . $this->siteId;
				}
				$site = null;

				$dao = new SOY2DAO();

				$xml[] = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>";
				if(count($this->multiLanguageConfs)){
					$xml[] = "<urlset xmlns=\"http://www.sitemaps.org/schemas/sitemap/0.9\" xmlns:xhtml=\"http://www.w3.org/1999/xhtml\">";
				}else{
					$xml[] = "<urlset xmlns=\"http://www.sitemaps.org/schemas/sitemap/0.9\" xmlns:xsi=\"http://www.w3.org/2001/XMLSchema-instance\">";
				}
				

				foreach($pages as $page){
					switch($page->getPageType()){
						case Page::PAGE_TYPE_BLOG:
							//サイトマップに掲載するページ
							if(isset($this->config_per_blog[$page->getId()])){
								$configs = $this->config_per_blog[$page->getId()];
								$ssls = (isset($this->ssl_per_blog[$page->getId()])) ? $this->ssl_per_blog[$page->getId()] : array();
								$uri = (strlen($page->getUri())) ? $page->getUri() . "/" : "";

								$configObj = $page->getPageConfigObject();

								//トップページ
								if(isset($configs["_top_"]) && $configs["_top_"] && $configObj->generateTopFlag){

									$http = (isset($ssls["_top_"]) && $ssls["_top_"]) ? "https" : "http";
									$xml[] =  self::_buildColumn(self::PAGE_TYPE_BLOG_TOP, $http . "://" . $host . "/" . $uri . $configObj->topPageUri, 1, $page->getUdate());
								}

								//月別アーカイブ
								if(isset($configs["_month_"]) && $configs["_month_"] && $configObj->generateMonthFlag){

									$http = (isset($ssls["_month_"]) && $ssls["_month_"]) ? "https" : "http";
									$url = $http . "://" . $host . "/" . $uri;
									if(strlen($configObj->monthPageUri)) $url .= $configObj->monthPageUri . "/";

									//最初の記事と最後の記事を取得
									list($first, $last) = self::_getEntrySpan($configObj->blogLabelId);

									$fY = (int)date("Y", $first);
									$lY = (int)date("Y", $last);

									$comY = $lY - $fY + 1;

									for($i = 0; $i < $comY; $i++){
										//最初の年の場合
										if($fY + $i === $fY && $fY + $i !== $lY){
											$fM = (int)date("n", $first);
											for($j = $fM; $j <= 12; $j++){
												$m = (strlen($j) === 1) ? "0" . $j : $j;
												$xml[] =  self::_buildColumn(self::PAGE_TYPE_BLOG_ARCHIVE, $url . $fY . "/" . $m . "/", 0.5, $page->getUdate());
											}
										}

										//最後の年の場合
										if($fY + $i === $lY && $fY + $i !== $fY){
											$lM = (int)date("n", $last);
											for($j = 1; $j <= $lM; $j++){
												$m = (strlen($j) === 1) ? "0" . $j : $j;
												$xml[] =  self::_buildColumn(self::PAGE_TYPE_BLOG_ARCHIVE, $url . $lY . "/" . $m . "/", 0.5, $page->getUdate());
											}
										}

										//最初と最後の年が同じ場合
										if($fY + $i === $lY && $fY + $i === $fY){
											$fM = (int)date("n", $first);
											$lM = (int)date("n", $last);

											for($j = $fM; $j <= $lM; $j++){
												$m = (strlen($j) === 1) ? "0" . $j : $j;
												$xml[] =  self::_buildColumn(self::PAGE_TYPE_BLOG_ARCHIVE, $url . $fY . "/" . $m . "/", 0.5, $page->getUdate());
											}
										}

										//最初の年と最後の年の間の年はすべての月を出力する
										if($fY < $fY + $i && $fY + $i < $lY){
											$tY = $fY + $i;
											for($j = 1; $j <= 12; $j++){
												$m = (strlen($j) === 1) ? "0" . $j : $j;
												$xml[] =  self::_buildColumn(self::PAGE_TYPE_BLOG_ARCHIVE, $url . $tY . "/" . $m . "/", 0.5, $page->getUdate());
											}
										}
									}

								}

								//カテゴリー
								if(isset($configs["_category_"]) && $configs["_category_"] && $configObj->generateCategoryFlag){

									$http = (isset($ssls["_category_"]) && $ssls["_category_"]) ? "https" : "http";
									$url = $http . "://" . $host . "/" . $uri;
									if(strlen($configObj->categoryPageUri)) $url .= $configObj->categoryPageUri . "/";

									if(isset($configObj->categoryLabelList)) {
										try{
											$res = $dao->executeQuery(
												"SELECT alias FROM Label WHERE id IN (" . implode(",", $configObj->categoryLabelList) . ")", 
												array()
											);
										}catch(Exception $e){
											$res = array();
										}

										foreach($res as $v){
											if(isset($v["alias"])){
												$alias = rawurlencode($v["alias"]);
												if(is_numeric(strpos($alias, "%2F"))) $alias = str_replace("%2F", "/", $alias);
												$xml[] =  self::_buildColumn(self::PAGE_TYPE_BLOG_CATEGORY, $url . $alias, 0.5, $page->getUdate());
											}
										}
									}
								}

								//詳細
								if(isset($configs["_entry_"]) && $configs["_entry_"] && $configObj->generateEntryFlag){

									$http = (isset($ssls["_entry_"]) && $ssls["_entry_"]) ? "https" : "http";
									$url = $http . "://" . $host . "/" . $uri;
									if(strlen($configObj->entryPageUri)) $url .= $configObj->entryPageUri . "/";

									try{
										$res = $dao->executeQuery(
											"SELECT ent.alias, ent.cdate FROM Entry ent ".
											"INNER JOIN EntryLabel lab ".
											"ON ent.id = lab.entry_id ".
											"WHERE lab.label_id = :labelId ".
											"AND ent.isPublished = 1 ".
											"AND ent.openPeriodStart < :start ".
											"AND ent.openPeriodEnd > :end ".
											"ORDER BY ent.cdate ASC", 
											array(":labelId" => $configObj->blogLabelId, ":start" => time(), ":end" => time())
										);
									}catch(Exception $e){
										$res = array();
									}

									if(count($res)){
										foreach($res as $v){
											if(isset($v["alias"])){
												$alias = rawurlencode($v["alias"]);
												$xml[] =  self::_buildColumn(self::PAGE_TYPE_BLOG_ENTRY, $url . $alias, 0.8, $v["cdate"]);
											}
										}
									}
								}
							}
							break;
						case Page::PAGE_TYPE_NORMAL:
						case Page::PAGE_TYPE_MOBILE:
						case Page::PAGE_TYPE_APPLICATION:
						default:
							//サイトマップに掲載するページ
							if(isset($this->config_per_page[$page->getId()]) && $this->config_per_page[$page->getId()]){

								//httpsのページであるか？
								$http = ($this->ssl_per_page[$page->getId()]) ? "https" : "http";
								$url = $http . "://" . $host . "/" . $page->getUri();
								$priority = (strlen($page->getUri()) === 0) ? 1 : 0.8;
								switch($page->getPageType()){
									case Page::PAGE_TYPE_APPLICATION:
										$xml[] =  self::_buildColumn(self::PAGE_TYPE_APPLICATION, $url, $priority, $page->getUdate());
										break;
									default:
										$xml[] =  self::_buildColumn(self::PAGE_TYPE_NORMAL, $url, $priority, $page->getUdate());
								}
							}

							break;
					}
				}

				//手動
				if(count($this->urls)){
					foreach($this->urls as $url){
						$xml[] =  self::_buildColumn(self::PAGE_TYPE_OTHER, $url["url"], 0.5, $url["lastmod"]);
					}
				}

				$xml[] = "</urlset>";
			}
		}

		$obj->addLabel("sitemap", array(
			"soy2prefix" => "cms",
			"html" => implode("\n", $xml)
		));
	}

	private function _getEntrySpan(int $labelId){
		$dao = new SOY2DAO();

		//最初の記事
		$baseSql = "SELECT ent.cdate FROM Entry ent ".
				"INNER JOIN EntryLabel lab ".
				"ON ent.id = lab.entry_id ".
				"WHERE lab.label_id = :labelId ".
				"AND ent.isPublished = 1 ".
				"AND ent.openPeriodStart < :start ".
				"AND ent.openPeriodEnd > :end ";

		try{
			$res = $dao->executeQuery(
				$baseSql."ORDER BY ent.cdate ASC LIMIT 1", 
				array(":labelId" => $labelId, ":start" => time(), ":end" => time())
			);
		}catch(Exception $e){
			$res = array();
		}

		$first = (isset($res[0])) ? (int)$res[0]["cdate"] : 0; 

		try{
			$res = $dao->executeQuery(
				$baseSql."ORDER BY ent.cdate DESC LIMIT 1", 
				array(":labelId" => $labelId, ":start" => time(), ":end" => time())
			);
		}catch(Exception $e){
			$res = array();
		}

		$last = (isset($res[0])) ? (int)$res[0]["cdate"] : 0;

		return array($first, $last);
	}

	private function _setMultiLanguageConfig(){
		if(!CMSPlugin::activeCheck("util_multi_language")) return;
		
		$langPlugin = CMSPlugin::loadPluginConfig("UtilMultiLanguagePlugin");
		if(!$langPlugin instanceof UtilMultiLanguagePlugin || !$langPlugin->getSameUriMode()) return;

		$this->multiLanguageConfs = SOYCMSUtilMultiLanguageUtil::getLanguagePrefixList($langPlugin);
		if(!count($this->multiLanguageConfs)) return;
		
		// ラベルと記事で多言語のひも付き状況をすべて取得
		self::_dicLogic()->buildDictionary();
	}

	/**
	 * @param int, string, float, int
	 * @return string
	 */
	private function _buildColumn(int $pageType, string $url, float $priority=0.5, int $lastmod=0){
		if(is_null($lastmod)) $lastmod = time();
		// カノニカルURLプラグインと合わせる
		$url = rtrim($url, "/");
		if(self::_isTrailingSlash()) {
			preg_match('/.+\.(html|htm|php?)/i', $url, $tmp);
			if(!count($tmp)) $url .= "/";
		}
		$cols = array();
		$cols[] = "<url>";
		$cols[] = "	<loc>" . $url . "</loc>";

		// 多言語化
		if($pageType < self::PAGE_TYPE_OTHER && is_array($this->multiLanguageConfs) && count($this->multiLanguageConfs)){
			foreach($this->multiLanguageConfs as $lang => $prefix){
				$multiUrl = $url;
				if(strlen($prefix)){
					$domain = self::_extractDomain($url);
					if(strlen($this->siteId)) $domain .= "/".$this->siteId;
					$domain .= "/";
					$multiUrl = str_replace($domain, $domain.$prefix."/", $multiUrl);
				}

				// @ToDo ラベルと記事の場合は紐付いたオブジェクトのエイリアスに切り替える
				if($lang != SOYCMSUtilMultiLanguageUtil::LANGUAGE_JP){
					switch($pageType){
						case self::PAGE_TYPE_BLOG_CATEGORY:
						case self::PAGE_TYPE_BLOG_ENTRY:
							$old = trim(substr($multiUrl, strrpos(rtrim($multiUrl, "/"), "/")), "/");
							$new = self::_dicLogic()->get($old, $lang);
							if(strlen($new)){
								$multiUrl = str_replace($old, rawurlencode($new), $multiUrl);
							}else{
								$multiUrl = "";
							}
							break;
					}
				}

				if(!strlen($multiUrl)) continue;

				$cols[] = "	<xhtml:link rel=\"alternate\" hreflang=\"".$lang."\" href=\"".$multiUrl."\" />";
			}
		}

		$cols[] = "	<priority>" . $priority . "</priority>";
		$cols[] = "	<lastmod>" . date("Y-m-d", $lastmod) . "T" . date("H:i:s", $lastmod) . "+09:00</lastmod>";
		$cols[] = "</url>";

		return implode("\n", $cols);
	}

	private function _isTrailingSlash(){
		static $is;
		if(is_bool($is)) return $is;

		if(CMSPlugin::activeCheck("canonical_url")){
			if(!class_exists("CanonicalUrlPlugin")) SOY2::import("site_include.plugin.canonical_url.canonical_url", ".php");
			$cnf = soy2_unserialize(file_get_contents(_SITE_ROOT_ . "/.plugin/canonical_url.config"));
			$is = ($cnf->getIsTrailingSlash() == 1);
		}else{	//カノニカルプラグインが無効の場合は何もしない
			$is = true;
		}

		return $is;
	}

	/**
	 * @param string
	 * @return string
	 */
	private function _extractDomain(string $url){
		$domain = substr($url, strpos($url, "://")+3);
		if(is_numeric(strpos($domain, "/"))) $domain = substr($domain, 0, strpos($domain, "/"));
		return $domain;
	}

	private function _dicLogic(){
		static $l;
		if(is_null($l)) $l = SOY2Logic::createInstance("site_include.plugin.util_multi_language.logic.MultiLanguageDictionaryLogic");
		return $l;
	}

	public static function register(){
		$obj = CMSPlugin::loadPluginConfig(self::PLUGIN_ID);
		if(!$obj) $obj = new SitemapPlugin();
		CMSPlugin::addPlugin(self::PLUGIN_ID, array($obj, "init"));
	}
}
