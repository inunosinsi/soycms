<?php
class CMSPageController extends SOY2PageController{

	var $args;
	var $siteConfig;
	var $dao;
	var $pageType;
	public $webPage;

	function execute(){
		$start = microtime(true);

		$pathBuilder = $this->getPathBuilder();

		//デフォルトページ
		$siteConfigDao = SOY2DAOFactory::create("cms.SiteConfigDAO");
		$siteConfig = $siteConfigDao->get();

		$dao = SOY2DAOFactory::create("cms.PageDAO");
		//onNotFound()でdaoが取れないとでてるので、ここで保存。
		$this->dao = $dao;

		//パスからURIと引数に変換
		$uri  = $pathBuilder->getPath();
		$args = $pathBuilder->getArguments();

		//保存
		$this->args = $args;
		$this->siteConfig = $siteConfig;

		//URLShortener
		//CMS:PLUGIN callEventFunction
		CMSPlugin::callEventFunc('onSiteAccess', array("controller" => $this));

		//文字コード変換
		$_GET = $this->convertEncoding($_GET);
		$_POST = $this->convertEncoding($_POST);

		//ヘッダー送信（先に送信しておかないと後で上書きできない）
		header("Content-Type: text/html; charset=" . $this->siteConfig->getCharsetText());
		//多言語対応のために保留
		//header("Content-Language: ja");

		try{
			try{
				$page = $dao->getActivePageByUri($uri);
				if($page->isActive() < 0){
					throw new Exception("out of date.");
				}
				//argsが一つでページ種別がブログの場合はもう少し試す
				if(count($args) === 1 && $args[0] != "feed" && $page->getPageType() == Page::PAGE_TYPE_BLOG){
					try{
						$page = $dao->getActivePageByUri($uri . "/" . $args[0]);
					}catch(Exception $e){
						throw new Exception("out of date.");
					}
				}
			}catch(Exception $e){
				$page = new Page();
			}

			try{

				//閲覧制限チェック
				if($this->siteConfig && $this->siteConfig->isShowOnlyAdministrator()){

					//セッションからログインしているかどうか取得
					SOY2::import("util.UserInfoUtil");
					SOY2::import("domain.admin.Site");

					if(!UserInfoUtil::isLoggined() || !UserInfoUtil::getSite()
						OR soy2_realpath(_SITE_ROOT_) != soy2_realpath(UserInfoUtil::getSite()->getPath())
					){
						throw new Exception("not logined");
					}
				}

				$_SERVER["SOYCMS_PAGE_URI"] = $page->getUri();
				$_SERVER["SOYCMS_PAGE_ID"] = $page->getId();

				$this->pageType = $page->getPageType();

				switch($page->getPageType()){
					case Page::PAGE_TYPE_BLOG:
						$webPage = &SOY2HTMLFactory::createInstance("CMSBlogPage", array(
							"arguments" => array($page->getId(), $args, $siteConfig),
							"siteRoot" => SOY2PageController::createLink("")
						));
						//TODO 存在しないページへのアクセスで例外を投げる
						break;

					case Page::PAGE_TYPE_MOBILE:
						$webPage = &SOY2HTMLFactory::createInstance("CMSMobilePage", array(
							"arguments" => array($page->getId(), $args, $siteConfig),
							"siteRoot" => SOY2PageController::createLink("")
						));
						break;

					case Page::PAGE_TYPE_APPLICATION:
						$webPage = &SOY2HTMLFactory::createInstance("CMSApplicationPage", array(
							"arguments" => array($page->getId(), $args, $siteConfig),
							"siteRoot" => SOY2PageController::createLink("")
						));
						break;

					case Page::PAGE_TYPE_NORMAL:
					default:

						/*
						 * URIが空のページに対して、/index.html以外（hoge.htmlなど）でアクセスした場合
						 */
						if(empty($uri) && count($args) > 0 && strstr($args[0], "index.htm") === false){
							throw new Exception("存在しないページ");
						}

						$webPage = &SOY2HTMLFactory::createInstance("CMSPage", array(
							"arguments" => array($page->getId(), $args, $siteConfig),
							"siteRoot" => SOY2PageController::createLink("")
						));
						break;
				}

				$this->webPage = $webPage;
				$webPage->main();

				//プラグインonLoadイベントの呼び出し
				$onLoad = CMSPlugin::getEvent('onPageLoad');
				foreach($onLoad as $plugin){
					$func = $plugin[0];
					$filter = $plugin[1]['filter'];
					switch($filter){
						case 'all':
							call_user_func($func, array('page' => &$page, 'webPage' => &$webPage));
							break;
						case 'blog':
							if($page->getPageType() == Page::PAGE_TYPE_BLOG){
								call_user_func($func, array('page' => &$page, 'webPage' => &$webPage));
							}
							break;
						case 'page':
							if($page->getPageType() == Page::PAGE_TYPE_NORMAL){
								call_user_func($func, array('page' => &$page, 'webPage' => &$webPage));
							}
							break;
					}
				}

				$webPage->parseTime = microtime(true) - $start;

				//出力
				$html = $this->getOutput($page, $webPage);
				//プラグイン
				$html = $this->onOutput($html, $page, $webPage);
				//文字コード変換
				$html = $this->convertCharset($html, $webPage);

				//改行コードを統一しておく
				$html = strtr($html, array("\r\n" => "\n", "\r" => "\n"));

				header("Content-Length: " . strlen($html));
				echo $html;

			}catch(Exception $e){
				$this->onNotFound();
			}
		}catch(Exception $e){
			error_log($e);
			$html = '<html><head><title>Error</title></head><body>500 Internal Server Error<body></html>';
			header("HTTP/1.1 500 Internal Server Error");
			header("Content-Type: text/html; charset=UTF-8");
			header("Content-Length: ".strlen($html));
			header("X-Error: 500 Internal Server Error");
			echo $html;
		}
	}

	/**
	 * 出力内容を取得
	 */
	function getOutput($page, $webPage){
		ob_start();
		CMSPlugin::callEventFunc("beforeOutput");
		$webPage->display();
		CMSPlugin::callEventFunc("afterOutput");
		$html = ob_get_contents();
		ob_end_clean();

		return $html;
	}

	/**
	 * onOutputのプラグインを呼び出す。
	 */
	function onOutput($html, $page, $webPage){
		$onLoad = CMSPlugin::getEvent('onOutput');
		foreach($onLoad as $plugin){
			$func = $plugin[0];
			$res = call_user_func($func, array('html' => $html, 'page' => &$page, 'webPage' => &$webPage));
			if(!is_null($res) && is_string($res)) $html = $res;
		}
		return $html;
	}

	/**
	 * 文字コード変換
	 */
	function convertCharset($html, $webPage){
		$html = $webPage->beforeConvert($html);
		$html = $this->siteConfig->convertToSiteCharset($html);
		$html = $webPage->afterConvert($html);
		return $html;
	}

	function onNotFound($path = NULL, $args = NULL, $classPath = NULL){

		$page = $this->dao->getErrorPage();
		$this->pageType = $page->getPageType();

		$webPage = &SOY2HTMLFactory::createInstance("CMSPage", array(
			"arguments" => array($page->getId(),$this->args,$this->siteConfig),
			"siteRoot" => SOY2PageController::createLink("")
		));

		$this->webPage = $webPage;
		$webPage->main();

		//出力
		$html = $this->getOutput($page, $webPage);
		//プラグイン
		try{
			$html = $this->onOutput($html, $page, $webPage);
		}catch(Exception $e){
			//プラグインでの例外は無視
		}
		//文字コード変換
		$html = $this->convertCharset($html, $webPage);

		//404NotFoundが表示される直前で読み込まれる
		CMSPlugin::callEventFunc('onSite404NotFound');

		header("HTTP/1.1 404 Not Found");
		header("Content-Type: text/html; charset=" . $this->siteConfig->getCharsetText());
		header("Content-Length: " . strlen($html));
		echo $html;
	}

	function &getPathBuilder(){
		static $builder;
		if(!$builder) $builder = new CMS_PathInfoBuilder();
		return $builder;
	}

	/**
	 * POSTデータの文字コード変換
	 */
	function convertEncoding($obj = null){
		if(!$obj) $obj = $_POST;

		if(!is_array($obj)) return;

		foreach($obj as $key => $value){
			if(is_array($value)){
				$obj[$key] = $this->convertEncoding($value);
			}else{
				$obj[$key] = $this->siteConfig->convertFromSiteCharset($value);
			}
		}

		return $obj;
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

class CMS_PathInfoBuilder extends SOY2_PathInfoPathBuilder{

	var $path;
	var $arguments;

	function __construct(){
		$pathInfo = (isset($_SERVER['PATH_INFO'])) ? $_SERVER['PATH_INFO'] : "";

		//先頭の「/」と末尾の「/」は取り除く
		$pathInfo = preg_replace('/^\/|\/$/', "", $pathInfo);

		list($this->path, $this->arguments) = self::parsePath($pathInfo);
	}

	/**
	 * パスからページのURI部分とパラメータ部分を抽出する
	 */
	public static function parsePath($path){
		static $dao;
		if(!$dao) $dao = SOY2DAOFactory::create("cms.PageDAO");

		$_uri = explode("/", $path);

		$uri = "";
		$args = array();

		while(count($_uri)){
			$baseuri = implode("/", $_uri);

			$testUri = $baseuri;
			if($dao->checkUri($testUri)){
				$uri = $testUri;
				break;
			}

			// path/index.htmlも試す
			$testUri = $baseuri."/index.html";
			if($dao->checkUri($testUri)){
				$uri = $testUri;
				break;
			}

			// path/index.htmも試す
			$testUri = $baseuri . "/index.htm";
			if($dao->checkUri($testUri)){
				$uri = $testUri;
				break;
			}

			//uriの末尾をargsに移す
			array_unshift($args, array_pop($_uri));
		}

		//uriが空の時でargsの値が1の時はargs[0]をuriに持ってくる。argsの値が2以上の場合はブログページである可能性が高い
		if(!strlen($uri) && count($args) === 1 && $args[0] != "feed") $uri = $args[0];

		return array($uri, $args);
	}

	/**
	 * フロントコントローラーからの相対パスを解釈してURLを生成する
	 */
	function createLinkFromRelativePath($path, $isAbsoluteUrl = false){
		//scheme
		$scheme = (isset($_SERVER["HTTPS"]) || defined("SOY2_HTTPS") && SOY2_HTTPS) ? "https" : "http";

		//port
		if( $_SERVER["SERVER_PORT"] == "80" && !isset($_SERVER["HTTPS"]) || $_SERVER["SERVER_PORT"] == "443" && isset($_SERVER["HTTPS"]) ){
			$port = "";
		}elseif(strlen($_SERVER["SERVER_PORT"]) > 0){
			$port = ":" . $_SERVER["SERVER_PORT"];
		}else{
			$port = "";
		}

		//host (domain)
		$host = $_SERVER["SERVER_NAME"];

		/**
		 * 絶対URLが渡されたらそのまま返す
		 */
		if(preg_match("/^https?:/", $path)){
			return $path;
		}

		/**
		 * 絶対パスが渡されたときもそのまま返す
		 */
		if(preg_match("/^\//", $path)){
			if($isAbsoluteUrl){
				return $scheme . "://" . $host . $port . $path;
			}else{
				return $path;
			}
		}

		/**
		 * 相対パス（絶対URL、絶対パス以外）のとき
		 */
		//フロントコントローラーのURLでの絶対パス（ファイル名index.phpは削除する）
		$scriptPath = (isset($_SERVER['SCRIPT_NAME']) && strlen($_SERVER['SCRIPT_NAME']) != 0) ? $_SERVER['SCRIPT_NAME'] : "/";
		if($scriptPath[strlen($scriptPath) - 1] == "/"){
			//サーバーによってはindex.phpが付かないところもあるようだ（Ablenet）
		}else{
			$scriptPath = preg_replace("/" . basename($scriptPath) . "\$/", "", $scriptPath);
		}

		$url = self::convertRelativePathToAbsolutePath($path, $scriptPath);

		if($isAbsoluteUrl){
			return $scheme . "://" . $host . $port . $url;
		}else{
			return $url;
		}
	}
}
