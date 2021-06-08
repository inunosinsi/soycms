<?php

class SOYCMSOutputContents{

	//デバッグモード
	const DEBUG_MODE = 0;
	const DEBUG_MODE_HEADER = 1;//ヘッダーにX-SOYCMS-Cacheを出力
	const DEBUG_MODE_HTML   = 2;//HTMLにもデバッグ情報を出力

	//キャッシュ保存ディレクトリ
	const CacheDir = "/.cache/soy_static/";
	private $cacheDir;

	//キャッシュのファイル名の元となる一意な値：md5(REQUEST_URI)
	private $md5_pathinfo;

	//各種ファイルパス
	private $cache;
	private $cache_header;
	private $cache_gen;

	//キャッシュの有効期間を示すファイル
	private $lifetimeFile;

	//クライアントから受け取ったHTTPヘッダー：If-Modified-Since取得用
	private $http_headers;

	//アクセス時刻
	private $request_time;
	//コンテンツ生成時刻
	private $generate_time;


	function __construct(){

		//「キャッシュのクリア」で削除されるようにキャッシュディレクトリ以下に置く
		$this->cacheDir = _SITE_ROOT_.self::CacheDir;

		//キャッシュのファイル名をREQUEST_URIから生成する
		$uri = isset($_SERVER["REQUEST_URI"]) ? $_SERVER["REQUEST_URI"] : "" ;
		$this->md5_pathinfo = md5($uri.$this->getCarrier());

		//ファイルパス
		$this->cache = "{$this->cacheDir}{$this->md5_pathinfo[0]}/{$this->md5_pathinfo[1]}/{$this->md5_pathinfo}.html";
		$this->cache_header = "{$this->cacheDir}{$this->md5_pathinfo[0]}/{$this->md5_pathinfo[1]}/{$this->md5_pathinfo}.header.php";
		$this->cache_gen = "{$this->cacheDir}{$this->md5_pathinfo[0]}/{$this->md5_pathinfo[1]}/{$this->md5_pathinfo}.check";
		//$cache_inc = "{$this->cacheDir}{$this->md5_pathinfo[0]}/{$this->md5_pathinfo[1]}/{$this->md5_pathinfo}.inc.php";

		$this->lifetimeFile = "{$this->cacheDir}lifetime";
		$this->request_time = $_SERVER["REQUEST_TIME"];

		$this->http_headers = function_exists("apache_request_headers") ? apache_request_headers() : null ;
	}

	/**
	 * 通常の出力処理
	 */
	static public function execute_normal(){
		include(_CMS_COMMON_DIR_."/site.inc.php");
		SOY2DAOConfig::Dsn(_SITE_DSN_);
		if(defined("_SITE_DB_USER_")) SOY2DAOConfig::user(_SITE_DB_USER_);
		if(defined("_SITE_DB_PASSWORD_")) SOY2DAOConfig::pass(_SITE_DB_PASSWORD_);
		$CMSPageController = SOY2PageController::init("CMSPageController");
		SOY2PageController::run();

		return $CMSPageController;
	}

	/**
	 * キャッシュがあればそれを出力する
	 */
	public function execute(){

		//キャッシュを使わない
		if(
			//GETでもHEADでもない場合
			!isset($_SERVER["REQUEST_METHOD"])
			|| $_SERVER["REQUEST_METHOD"] != "GET" && $_SERVER["REQUEST_METHOD"] != "HEAD"
			//REQUEST_URIがない場合
			|| !isset($_SERVER["REQUEST_URI"])
		){
			self::execute_normal();
			return;
		}

		//生成中は最大10秒待つ
		$this->waitIfGenerating(10);

		if( $this->hasValidCache() ){
			/* 有効なキャッシュが存在する */

			//キャッシュファイルを示すヘッダー
			if(self::DEBUG_MODE >= self::DEBUG_MODE_HEADER)header("X-SOYCMS-Cache: ".$this->md5_pathinfo);

			//If-Modified-Since対応
			if( $this->ifNotModified() && !headers_sent() ){
				header("HTTP/1.1 304 Not Modified");
				return;
			}else{
				//出力
				if(file_exists($this->cache_header)) include($this->cache_header);
				$this->output();
			}

		}else{
			/* キャッシュがない */

			//ページの出力内容を取る
			ob_start();
			$CMSPageController = self::execute_normal();
			$contents = ob_get_clean();
			$this->generate_time = time();

			//まず出力
			$this->output($contents);

			//キャッシュディレクトリを作成
			$this->makeDir();

			//以下の場合はキャッシュを生成しないで終了
			if(
				//アプリケーションページ
				   $CMSPageController->getPageType() == Page::PAGE_TYPE_APPLICATION
				//ログイン中のみ表示
				|| $CMSPageController->siteConfig && $CMSPageController->siteConfig->isShowOnlyAdministrator()
				//キャッシュ保存先に書き込めない
				|| !file_exists(dirname($this->cache)) || !is_writable(dirname($this->cache))
				//キャッシュを使わない設定の場合
				|| !defined("SOYCMS_USE_CACHE") || !SOYCMS_USE_CACHE
			){
				return;
			}else{
				//エラー発生時
				$headers = headers_list();
				foreach( $headers as $header){
					if(strpos($header, "X-Error") !== false){
						return;
					}
				}
			}

			//生成中のマーク
			touch($this->cache_gen);

			//直近の公開期間をまたいだときは更新があったと見なす
//			if( file_exists($this->lifetimeFile) && $this->request_time >= filemtime($this->lifetimeFile) ){
//				touch(_SITE_DB_FILE_, $this->request_time);
//			}

			//直近の公開開始/終了日時を$lifetimeFileの更新日時として保存
			$this->saveLifetime($CMSPageController->getCurrentContentsLifetime());

			//出力内容とヘッダーをキャッシュに保存する
			$this->saveCache($contents);
			$this->saveHeaders($CMSPageController);

			//生成中のマークを消す
			if(file_exists($this->cache_gen)) unlink($this->cache_gen);

		}
	}

	/**
	 * 生成中なら引数の秒数だけ待つ
	 */
	private function waitIfGenerating($max_wait_time = 10){
		$_count = 0;
		while( file_exists($this->cache_gen) && filemtime($this->cache_gen) > $this->request_time - $max_wait_time && $_count++<$max_wait_time){
			sleep(1);
		}
	}

	/**
	 * If-Not-Modified-Since
	 */
	private function ifNotModified(){
		return
			   isset($this->http_headers) && is_array($this->http_headers)
			&& isset($this->http_headers["If-Modified-Since"])
			&& strtotime($this->http_headers["If-Modified-Since"]) >= filemtime($this->cache)
			;
	}

	/**
	 * 有効なキャッシュが存在するかどうか
	 */
	private function hasValidCache(){
		return
			//キャッシュが存在する
			file_exists($this->cache)
			//キャッシュの有効期間内
			&& ( !defined("SOYCMS_CACHE_LIFETIME") || $this->request_time < filemtime($this->cache) + SOYCMS_CACHE_LIFETIME )
			//データベースに変更がない
			&& defined("_SITE_DB_FILE_") && filemtime($this->cache) > filemtime(_SITE_DB_FILE_)
			//このファイル自身に変更がない
			&& filemtime($this->cache) > filemtime(__FILE__)
			//公開期間をまたいでいない
			&& file_exists($this->lifetimeFile) && $this->request_time < filemtime($this->lifetimeFile)
			//ユーザー設定ファイルに変更がない
			&& defined("_CMS_COMMON_DIR_")
			&& ( !file_exists(_CMS_COMMON_DIR_."/config/user.config.php") || filemtime($this->cache) > filemtime(_CMS_COMMON_DIR_."/config/user.config.php") )
			;
	}

	/**
	 * キャッシュファイルまたは文字列を出力
	 * @param String $contents 出力するHTML、これが指定されなければキャッシュを出力する
	 * HTTPヘッダーでContent-Lengthも出力する
	 * 可能であればgzip圧縮を使う
	 */
	private function output($contents = null){
		ob_start();
			$ob = ob_start("ob_gzhandler");//ブラウザが受け入れてないならfalseが返る

			if(is_null($contents)){
				readfile($this->cache);
				if(self::DEBUG_MODE >= self::DEBUG_MODE_HTML && isset($_SERVER["REQUEST_URI"])) echo '<div class="cache" style="background-color:orange;position:absolute;top:0px;left:0px;width:100%;background-color:orange;line-height:2em;height:2em;text-align:center;">This is cache. '.$_SERVER["REQUEST_URI"].' => '.$this->md5_pathinfo.'</div>';
			}else{
				if(strlen($this->generate_time))header("Last-Modified: ".gmdate("D, d M Y H:i:s T", $this->generate_time), true);
				echo $contents;
				if(self::DEBUG_MODE >= self::DEBUG_MODE_HTML) echo '<div style="position:absolute;top:0px;left:0px;width:100%;background-color:lightblue;line-height:2em;height:2em;text-align:center;">This is NOT cache.</div>';
			}

			if($ob) ob_end_flush();//ob_gzhandlerの分
			header("Content-Length: ".ob_get_length(), true);
		ob_end_flush();
	}

	/**
	 * キャッシュディレクトリを作成
	 */
	private function makeDir(){
		if(!file_exists(dirname($this->cache))) @mkdir(dirname($this->cache),0777,true);
	}

	/**
	 * キャッシュを保存
	 */
	private function saveCache($contents){
		@file_put_contents($this->cache, $contents);
		if(file_exists($this->cache)) touch($this->cache, $this->generate_time);
	}

	/**
	 * HTTPヘッダーを保存
	 */
	private function saveHeaders($CMSPageController){
		//404とContent-TypeをHTTPヘッダー出力用ファイルに保存する
		//headers_list()ではHTTP Statusは取得できない
		$headers = headers_list();
		$h = array();
		foreach($headers as $header){
			if( stripos($header, "Content-Type:") === 0 ){
				$h[] = "header(\"{$header}\");";
			}
		}

		//404ページでは404を、その他の場合はLast-Modifiedを返すようにする
		if(
		      $CMSPageController->getPageType() == Page::PAGE_TYPE_ERROR
		   || $CMSPageController->getPageType() == Page::PAGE_TYPE_BLOG && $CMSPageController->webPage->total === 0
		){
			$h[] = "header(\"HTTP/1.1 404 Not Found\");";
		}else{
			//キャッシュの作成日時 GMT
			$h[] = "header(\"Last-Modified: ".gmdate("D, d M Y H:i:s T", filemtime($this->cache))."\");";
		}

		if(count($h) >0){
			@file_put_contents($this->cache_header, "<?php\n".implode("\n", $h)."\n?>");
		}
	}

	/**
	 * $lifetimeFileを更新日時を指定して保存
	 */
	private function saveLifetime($lifetime){
		@file_put_contents($this->lifetimeFile, $lifetime."\n".date("r",$lifetime));
		if(file_exists($this->lifetimeFile)) touch($this->lifetimeFile, $lifetime);
		if(file_exists($this->lifetimeFile) && filemtime($this->lifetimeFile) != $lifetime) @unlink($this->lifetimeFile);
	}

	/**
	 * 絵文字用にケータイのキャリアを取得する
	 * @TODO スマートホンも含めるべき？GoogleAnalyticsプラグイン
	 */
	private function getCarrier(){
		$data = isset($_SERVER["HTTP_USER_AGENT"]) ? $_SERVER["HTTP_USER_AGENT"] : "" ;
		if(preg_match("/DoCoMo/i", $data)){
			return "docomo";// i-mode
		} else if(preg_match("/(J\-PHONE|Vodafone|MOT\-[CV]980|SoftBank|Semulator)/i", $data)){
			return "softback";// softbank
		} else if(preg_match("/KDDI\-/i", $data) || preg_match("/UP\.Browser/i", $data)){
			return "au";// ezweb
		} else if(preg_match("/^PDXGW/i", $data) || preg_match("/(DDIPOCKET|WILLCOM);/i", $data)){
			return "willcom";// willcom
		} else if(preg_match("/^L\-mode/i", $data)){
			return "lmode";// l-mode
		} else {
			return "etc";
		}
	}

}
