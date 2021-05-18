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

	//挿入しないページ
	//Array<ページID => 0 | 1> 挿入しないページが1
	var $config_per_page = array();
	//Array<ページID => Array<ページタイプ => 0 | 1>> 挿入しないページが1
	var $config_per_blog = array();

	var $ssl_per_page = array();
	//Array<ページID => Array<ページタイプ => 0 | 1>> 挿入しないページが1
	var $ssl_per_blog = array();

	var $urls = array();

	function getId(){
		return self::PLUGIN_ID;
	}

	function init(){
		CMSPlugin::addPluginMenu(self::PLUGIN_ID,array(
			"name"=>"サイトマッププラグイン",
			"description"=>"",
			"author"=>"齋藤毅",
			"url"=>"http://saitodev.co",
			"mail"=>"tsuyoshi@saitodev.co",
			"version"=>"1.4"
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
		if(strpos($obj->page->getUri(), "sitemap.xml") !== false){
			header("Content-Type: text/xml");

			//全ページ取得
			$pageDao = SOY2DAOFactory::create("cms.PageDAO");
			$sql = "SELECT id, uri, page_type, page_config, udate FROM Page WHERE isPublished != 0 AND openPeriodStart < " . time() . " AND openPeriodEnd > " . time();
			try{
				$res = $pageDao->executeQuery($sql, array());
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
				if(!$site->getIsDomainRoot()) $host .= "/" . $siteId;
				$site = null;

				$dao = new SOY2DAO();

				$xml[] = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>";
				$xml[] = "<urlset xmlns=\"http://www.sitemaps.org/schemas/sitemap/0.9\" xmlns:xsi=\"http://www.w3.org/2001/XMLSchema-instance\">";

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
									$xml[] =  self::buildColumn($http . "://" . $host . "/" . $uri . $configObj->topPageUri, 1, $page->getUdate());
								}

								//月別アーカイブ
								if(isset($configs["_month_"]) && $configs["_month_"] && $configObj->generateMonthFlag){

									$http = (isset($ssls["_month_"]) && $ssls["_month_"]) ? "https" : "http";
									$url = $http . "://" . $host . "/" . $uri;
									if(strlen($configObj->monthPageUri)) $url .= $configObj->monthPageUri . "/";

									//最初の記事と最後の記事を取得
									list($first, $last) = self::getEntrySpan($configObj->blogLabelId);

									$fY = (int)date("Y", $first);
									$lY = (int)date("Y", $last);

									$comY = $lY - $fY + 1;

									for($i = 0; $i < $comY; $i++){
										//最初の年の場合
										if($fY + $i === $fY && $fY + $i !== $lY){
											$fM = (int)date("n", $first);
											for($j = $fM; $j <= 12; $j++){
												$m = (strlen($j) === 1) ? "0" . $j : $j;
												$xml[] =  self::buildColumn($url . $fY . "/" . $m . "/", 0.5, $page->getUdate());
											}
										}

										//最後の年の場合
										if($fY + $i === $lY && $fY + $i !== $fY){
											$lM = (int)date("n", $last);
											for($j = 1; $j <= $lM; $j++){
												$m = (strlen($j) === 1) ? "0" . $j : $j;
												$xml[] =  self::buildColumn($url . $lY . "/" . $m . "/", 0.5, $page->getUdate());
											}
										}

										//最初と最後の年が同じ場合
										if($fY + $i === $lY && $fY + $i === $fY){
											$fM = (int)date("n", $first);
											$lM = (int)date("n", $last);

											for($j = $fM; $j <= $lM; $j++){
												$m = (strlen($j) === 1) ? "0" . $j : $j;
												$xml[] =  self::buildColumn($url . $fY . "/" . $m . "/", 0.5, $page->getUdate());
											}
										}

										//最初の年と最後の年の間の年はすべての月を出力する
										if($fY < $fY + $i && $fY + $i < $lY){
											$tY = $fY + $i;
											for($j = 1; $j <= 12; $j++){
												$m = (strlen($j) === 1) ? "0" . $j : $j;
												$xml[] =  self::buildColumn($url . $tY . "/" . $m . "/", 0.5, $page->getUdate());
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
											$res = $dao->executeQuery("SELECT alias FROM Label WHERE id IN (" . implode(",", $configObj->categoryLabelList) . ")", array());
										}catch(Exception $e){
											$res = array();
										}

										foreach($res as $v){
											if(isset($v["alias"])){
												$alias = rawurlencode($v["alias"]);
												$xml[] =  self::buildColumn($url . $alias, 0.5, $page->getUdate());
											}
										}
									}
								}

								//詳細
								if(isset($configs["_entry_"]) && $configs["_entry_"] && $configObj->generateEntryFlag){

									$http = (isset($ssls["_entry_"]) && $ssls["_entry_"]) ? "https" : "http";
									$url = $http . "://" . $host . "/" . $uri;
									if(strlen($configObj->entryPageUri)) $url .= $configObj->entryPageUri . "/";

									$sql = "SELECT ent.alias, ent.cdate FROM Entry ent ".
											"INNER JOIN EntryLabel lab ".
											"ON ent.id = lab.entry_id ".
											"WHERE lab.label_id = :labelId ".
											"AND ent.isPublished = 1 ".
											"AND ent.openPeriodStart < " . time() . " ".
											"AND ent.openPeriodEnd > " . time() . " ".
											"ORDER BY ent.cdate ASC";
									try{
										$res = $dao->executeQuery($sql, array(":labelId" => $configObj->blogLabelId));
									}catch(Exception $e){
										$res = array();
									}

									if(count($res)){
										foreach($res as $v){
											if(isset($v["alias"])){
												$alias = rawurlencode($v["alias"]);
												$xml[] =  self::buildColumn($url . $alias, 0.8, $v["cdate"]);
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
								$xml[] =  self::buildColumn($url, $priority, $page->getUdate());
							}

							break;
					}
				}

				//手動
				if(count($this->urls)){
					foreach($this->urls as $url){
						$xml[] =  self::buildColumn($url["url"], 0.5, $url["lastmod"]);
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

	private function getEntrySpan($labelId){
		$dao = new SOY2DAO();

		//最初の記事
		$sql = "SELECT ent.cdate FROM Entry ent ".
				"INNER JOIN EntryLabel lab ".
				"ON ent.id = lab.entry_id ".
				"WHERE lab.label_id = :labelId ".
				"AND ent.isPublished = 1 ".
				"AND ent.openPeriodStart < " . time() . " ".
				"AND ent.openPeriodEnd > " . time() . " ";

		$addSql = $sql . "ORDER BY ent.cdate ASC LIMIT 1";

		try{
			$res = $dao->executeQuery($addSql, array(":labelId" => $labelId));
		}catch(Exception $e){
			$res = array();
		}

		if(isset($res[0])){
			$first = (int)$res[0]["cdate"];
		}

		$addSql = $sql . "ORDER BY ent.cdate DESC LIMIT 1";

		try{
			$res = $dao->executeQuery($addSql, array(":labelId" => $labelId));
		}catch(Exception $e){
			$res = array();
		}

		if(isset($res[0])){
			$last = (int)$res[0]["cdate"];
		}

		return array($first, $last);
	}

	private function buildColumn($url, $priority = 0.5, $lastmod = 0){
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
		$cols[] = "	<priority>" . $priority . "</priority>";
		$cols[] = "	<lastmod>" . date("Y-m-d", $lastmod) . "T" . date("H:i:s", $lastmod) . "+09:00</lastmod>";
		$cols[] = "</url>";

		return implode("\n", $cols);
	}

	private function _isTrailingSlash(){
		static $is;
		if(is_bool($is)) return $is;

		if(file_exists(_SITE_ROOT_ . "/.plugin/canonical_url.active")){
			if(!class_exists("CanonicalUrlPlugin")) SOY2::import("site_include.plugin.canonical_url.canonical_url", ".php");
			$cnf = soy2_unserialize(file_get_contents(_SITE_ROOT_ . "/.plugin/canonical_url.config"));
			$is = ($cnf->getIsTrailingSlash() == 1);
		}else{	//カノニカルプラグインが無効の場合は何もしない
			$is = true;
		}

		return $is;
	}

	public static function register(){

		$obj = CMSPlugin::loadPluginConfig(self::PLUGIN_ID);
		if(!$obj){
			$obj = new SitemapPlugin();
		}

		CMSPlugin::addPlugin(self::PLUGIN_ID, array($obj, "init"));
	}
}
