<?php
class CMSPageController extends SOY2PageController{

	var $args;
	var $siteConfig;
	var $pageType;
	var $webPage;	//プラグインで使用する

	function execute(){
		$start = microtime(true);

		/**
		 * 下記の定数を定義する
		 * SOYCMS_SITE_ID
		 * SOYCMS_IS_DOCUMENT_ROOT
		 */
		CMSUtil::defineSiteConstant();
		
		//デフォルトページ
		$siteConfig = soycms_get_site_config_object();

		//パスからURIと引数に変換
		SOY2::import("site_include.CMSPathInfoBuilder");
		$pathBuilder = new CMSPathInfoBuilder();
		$uri  = $pathBuilder->getPath();
		$args = $pathBuilder->getArguments();
		unset($pathBuilder);
		
		//保存
		$this->args = $args;
		$this->siteConfig = $siteConfig;

		//URLShortener
		//CMS:PLUGIN callEventFunction
		CMSPlugin::callEventFunc('onSiteAccess', array("controller" => $this));

		//文字コード変換
		$_GET = self::_convertEncoding($_GET);
		$_POST = self::_convertEncoding($_POST);

		//ヘッダー送信（先に送信しておかないと後で上書きできない）
		header("Content-Type: text/html; charset=" . $this->siteConfig->getCharsetText());
		//多言語対応のために保留
		//header("Content-Language: ja");

		try{
			$page = soycms_get_hash_table_dao("page")->getActivePageByUri($uri);
		}catch(Exception $e){
			$this->onNotFound();
		}
		
		//404ページでも下記の値を使用する
		$_SERVER["SOYCMS_PAGE_URI"] = $page->getUri();
		$_SERVER["SOYCMS_PAGE_ID"] = $page->getId();

		if($page->isActive() < 0){
			$this->onNotFound();
		}
		
		//閲覧制限チェック
		if($this->siteConfig && $this->siteConfig->isShowOnlyAdministrator()){
			//セッションからログインしているかどうか取得
			SOY2::import("util.UserInfoUtil");
			SOY2::import("domain.admin.Site");

			if(!UserInfoUtil::isLoggined() || !UserInfoUtil::getSite()
				OR soy2_realpath(_SITE_ROOT_) != soy2_realpath(UserInfoUtil::getSite()->getPath())
			){
				$this->onNotFound();
			}
		}
		
		// ページオブジェクト取得後
		CMSPlugin::callEventFunc('onAfterGettingPageObject', array("pageId" => $_SERVER["SOYCMS_PAGE_ID"]));

		$this->pageType = $page->getPageType();
		
		switch($page->getPageType()){
			case Page::PAGE_TYPE_BLOG:
				$pageClass = "CMSBlogPage";
				break;

			case Page::PAGE_TYPE_MOBILE:
				$pageClass = "CMSMobilePage";
				break;

			case Page::PAGE_TYPE_APPLICATION:
				$pageClass = "CMSApplicationPage";
				break;

			case Page::PAGE_TYPE_NORMAL:
			default:
				/*
				 * URIが空のページに対して、/index.html以外（hoge.htmlなど）でアクセスした場合
				 */
				if(empty($uri) && count($args) > 0 && is_bool(strstr($args[0], "index.htm"))){
					//ページャの場合は404NotFoundにしない
					if(!isset($args[0])){
						$this->onNotFound();
					}else{
						preg_match('/^page-\d+/', $args[0], $tmp);
						if(!isset($tmp[0])) $this->onNotFound();												
					}
					
					//throw new Exception("存在しないページ");
				}
				$pageClass = "CMSPage";
				break;
		}

		// Argsの値を調べる必要があるか？
		$isCheckArgs = ($pageClass != "CMSBlogPage");
		if($isCheckArgs && $pageClass == "CMSApplicationPage"){
			switch(soycms_get_application_page_object($_SERVER["SOYCMS_PAGE_ID"])->getApplicationId()){
				case "gallery":	//SOY Galleryの場合はページャがあるのでArgsの中身は調べない
					$isCheckArgs = false;
					break;
				default:
					//
			}
		}

		// ブログページ以外ではargsにページャに関するもの以外がないことを確認
		if($isCheckArgs && count($args) && strlen($args[0])){
			// タグクラウドプラグインのページの場合
			if(CMSPlugin::activeCheck("TagCloud")){
				SOY2::import("site_include.plugin.tag_cloud.util.TagCloudUtil");
				if(TagCloudUtil::getPageIdSettedTagCloudBlock() != $_SERVER["SOYCMS_PAGE_ID"] || !TagCloudUtil::checkIsTagExists($args[0])) $this->onNotFound();
			// キーワード自動抽出プラグイン(Geminiの場合)
			}else if(CMSPlugin::activeCheck("gemini_keyword")){
				SOY2::import("site_include.plugin.gemini_keyword.util.GeminiKeywordUtil");
				if(GeminiKeywordUtil::getPageIdSettedGeminiKeywordBlock() != $_SERVER["SOYCMS_PAGE_ID"] || !GeminiKeywordUtil::checkIsKeywordExists($args[0])) $this->onNotFound();
			// ページャの場合
			}else{
				preg_match('/^page-\d+/', $args[0], $tmp);
				if(!isset($tmp[0])) $this->onNotFound();

				//page-\d/文字列形式のargsであった場合
				if(isset($tmp[0]) && count($args) > 1) $this->onNotFound();
			}
		}

		SOY2::import("site_include." . $pageClass);
		$this->webPage = &SOY2HTMLFactory::createInstance($pageClass, array(
			"arguments" => array($page->getId(), $args, $siteConfig),
			"siteRoot" => SOY2PageController::createLink("")
		));
		
		if($this->webPage->getError() instanceof Exception){
			$this->onNotFound();
		}
		
		$this->webPage->main();

		//プラグインonLoadイベントの呼び出し
		$onLoads = CMSPlugin::getEvent('onPageLoad');
		if(is_array($onLoads) && count($onLoads)){
			foreach($onLoads as $plugin){
				$func = $plugin[0];
				$filter = $plugin[1]['filter'];
				switch($filter){
					case 'all':
						call_user_func($func, array('page' => &$page, 'webPage' => &$this->webPage));
						break;
					case 'blog':
						if($page->getPageType() == Page::PAGE_TYPE_BLOG){
							call_user_func($func, array('page' => &$page, 'webPage' => &$this->webPage));
						}
						break;
					case 'page':
						if($page->getPageType() == Page::PAGE_TYPE_NORMAL){
							call_user_func($func, array('page' => &$page, 'webPage' => &$this->webPage));
						}
						break;
				}
			}
		}

		$this->webPage->parseTime = microtime(true) - $start;

		//出力
		$html = $this->getOutput($page);
		//プラグイン
		$html = $this->onOutput($html, $page);
		//文字コード変換
		$html = $this->convertCharset($html);

		//改行コードを統一しておく
		$html = strtr($html, array("\r\n" => "\n", "\r" => "\n"));

		header("Content-Length: " . strlen($html));
		echo $html;
		exit;
	}

	/**
	 * 出力内容を取得
	 */
	function getOutput(Page $page){
		ob_start();
		CMSPlugin::callEventFunc("beforeOutput");
		$this->webPage->display();
		CMSPlugin::callEventFunc("afterOutput");
		$html = ob_get_contents();
		ob_end_clean();

		return $html;
	}

	/**
	 * onOutputのプラグインを呼び出す。
	 */
	function onOutput(string $html, Page $page){
		$onLoads = CMSPlugin::getEvent('onOutput');
		if(!is_array($onLoads) || !count($onLoads)) return $html;
		foreach($onLoads as $plugin){
			$func = $plugin[0];
			$filter = (isset($plugin[1]["filter"])) ? $plugin[1]["filter"] : "all";
			$exeFunc = false;

			switch($filter){
				case "blog":
					if($this->webPage instanceof CMSBlogPage) $exeFunc = true;
					break;
				case "page":
					if(!$this->webPage instanceof CMSBlogPage) $exeFunc = true;
					break;
				case "all":
				default:
					$exeFunc = true;
					break;
			}
			
			if(!$exeFunc) continue;

			$res = call_user_func($func, array('html' => $html, 'page' => &$page, 'webPage' => &$this->webPage));
			if(!is_null($res) && is_string($res)) $html = $res;
		}
		return $html;
	}

	/**
	 * 文字コード変換
	 */
	function convertCharset(string $html){
		$html = $this->webPage->beforeConvert($html);
		$html = $this->siteConfig->convertToSiteCharset($html);
		$html = $this->webPage->afterConvert($html);
		return $html;
	}

	
	function onInternalServerError(){
		$html = '<html><head><title>Error</title></head><body>500 Internal Server Error<body></html>';
		header("HTTP/1.1 500 Internal Server Error");
		header("Content-Type: text/html; charset=UTF-8");
		header("Content-Length: ".strlen($html));
		header("X-Error: 500 Internal Server Error");
		echo $html;
		exit;
	}

	function onNotFound(string $path="", array $args=array(), string $classPath=""){
		if(defined("INVALID_404_NOT_FOUND") && INVALID_404_NOT_FOUND) return;	//何もしない

		if(!isset($_SERVER["SOYCMS_PAGE_URI"])) $_SERVER["SOYCMS_PAGE_URI"] = "";
		if(!isset($_SERVER["SOYCMS_PAGE_ID"])) $_SERVER["SOYCMS_PAGE_ID"] = 0;

		$page = soycms_get_hash_table_dao("page")->getErrorPage();
		$this->pageType = $page->getPageType();

		SOY2::import('site_include.CMSPage');
		$this->webPage = &SOY2HTMLFactory::createInstance("CMSPage", array(
			"arguments" => array($page->getId(),$this->args,$this->siteConfig),
			"siteRoot" => SOY2PageController::createLink("")
		));

		$this->webPage->main();

		//出力
		$html = $this->getOutput($page);
		//プラグイン
		try{
			$html = $this->onOutput($html, $page);
		}catch(Exception $e){
			//プラグインでの例外は無視
		}
		//文字コード変換
		$html = $this->convertCharset($html);

		//404NotFoundが表示される直前で読み込まれる
		CMSPlugin::callEventFunc('onSite404NotFound');

		header("HTTP/1.1 404 Not Found");
		header("Content-Type: text/html; charset=" . $this->siteConfig->getCharsetText());
		header("Content-Length: " . strlen($html));
		echo $html;
		exit;
	}

	/**
	 * POSTデータの文字コード変換
	 * @param array
	 * @return array
	 */
	private function _convertEncoding(array $arr=array()){
		if(!count($arr)) $arr = $_POST;
		if(!is_array($arr)) return array();

		foreach($arr as $key => $value){
			if(is_array($value)){
				$arr[$key] = self::_convertEncoding($value);
			}else{
				$arr[$key] = $this->siteConfig->convertFromSiteCharset($value);
			}
		}

		return $arr;
	}

	/**
	 * 現在の公開セット（記事＋ページ）の有効期限を返す
	 * @return Number UnixTime
	 */
	function getCurrentContentsLifetime(){
		$minTime = CMSUtil::DATE_MAX;
		if(defined("SOYCMS_CACHE_LIFETIME")){
			$minTime = min($minTime, time() + (int)SOYCMS_CACHE_LIFETIME);
		}
		try{
			$entryDao = SOY2DAOFactory::create("cms.EntryDAO");
			$time = $entryDao->getNearestClosingEntry(time());
			if(isset($time)) $minTime = min($minTime, $time);
			$time = $entryDao->getNearestOpeningEntry(time());
			if(isset($time)) $minTime = min($minTime, $time);

			$pageDao = SOY2DAOFactory::create("cms.PageDAO");
			$time = $pageDao->getNearestClosingPage(time());
			if(isset($time)) $minTime = min($minTime, $time);
			$time = $pageDao->getNearestOpeningPage(time());
			if(isset($time)) $minTime = min($minTime, $time);
		}catch(Exception $e){
			$minTime = 0;
		}

		return $minTime;
	}

	/**
	 * ページタイプ（標準、ブログ、携帯、アプリ、…）を返す
	 */
	function getPageType(){
		return $this->pageType;
	}
}
