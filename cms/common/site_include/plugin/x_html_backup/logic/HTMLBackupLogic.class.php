<?php

class HTMLBackupLogic extends SOY2LogicBase {

	const DIRECTORY_NAME = ".html_backup";
	const ZIP_DIRECTORY_NAME = ".html_backup_zip";
	const ZIP_FILE_NAME = "backup.zip";

	private $configPerPage = array();
	private $configPerBlog = array();

	/**
	 * @param string, string
	 */
	function save(string $html, string $pathinfo){
		$backupDir = self::getBackupDirectory() . soycms_get_site_id_by_frontcontroller() . "/";
		if(!file_exists($backupDir)) mkdir($backupDir);

		file_put_contents(self::_getBackupFilePath($backupDir, $pathinfo), $html);
	}

	function getBackupDirectory(){
		return self::_backupDirectory();
	}

	/**
	 * @return string
	 */
	private function _backupDirectory(){
		if(defined("_SITE_ROOT_")){
			$siteRoot = rtrim(_SITE_ROOT_, "/")."/";
		}else{
			$siteRoot = UserInfoUtil::getSiteDirectory();
		}
		$dir = $siteRoot.self::DIRECTORY_NAME."/";
		if(!file_exists($dir)) mkdir($dir);

		if(!file_exists($dir.".htaccess")) file_put_contents($dir.".htaccess", "Deny from all");

		return $dir;
	}

	/**
	 * @param string, string
	 * @return string
	 */
	private function _getBackupFilePath(string $backupDir, string $pathinfo){
		$pathinfo = trim($pathinfo, "/");
		$subdirs = explode("/", $pathinfo);
		$isExtension = false;
		if(count($subdirs)){
			foreach($subdirs as $dir){
				// 拡張子があるか？
				if(preg_match('/\.html$|\.htm$|\.php$|\.xml$|\.json$/i', $dir)){
					$backupDir .= $dir;
					$isExtension = true;
				}else{
					preg_match('/%[0-9a-fA-F]{2}/', $dir, $tmp);
					if(count($tmp)) $dir = rawurldecode($dir);

					$backupDir .= $dir."/";
					if(!file_exists($backupDir)) mkdir($backupDir);
				}
			}
		}
		
		// 拡張子がなければ、URLの末尾にindex.htmlを加える
		if(!$isExtension) $backupDir .= "index.html";
		if(soy2_strpos($backupDir, "//") >= 0) $backupDir = str_replace("//", "/", $backupDir);
		return $backupDir;
	}

	/**
	 * @return bool
	 */
	function generate(){
		$url = UserInfoUtil::getSitePublishURL()."sitemap.xml";
		$r = file_get_contents($url);
		if(is_bool($r)) return false;

		$xml = simplexml_load_file($url);
		if(is_bool($xml) || !property_exists($xml, "url")) return false;

		foreach($xml->url as $obj){
			if(!$obj instanceof SimpleXMLElement || !property_exists($obj, "loc")) continue;
			$url = (string)$obj->loc;
			if(self::_checkConfigStatus($url) && !self::_isExistedBackupFile($url)){
				$_dust = file_get_contents($url);
			}
		}
	}

	/**
	 * 設定に従ってページを読み込むかを決める
	 * @param string
	 * @return bool
	 */
	private function _checkConfigStatus(string $url){
		$uriList = self::_getUriList();		
		if(!count($uriList)) return false;

		$uri = "/".str_replace(UserInfoUtil::getSitePublishURL(), "", $url);
		if(strlen($uri) === 1){	// uriが空のページの場合
			if(is_numeric(array_search($uri, $uriList))) return true;
		}

		//トップページ以外のページ
		$isRead = false;
		$id = 0;
		foreach($uriList as $pageId => $u){
			if($isRead || strlen($u) === 1) continue;
			if(soy2_strpos($uri, $u) === 0) {
				$isRead = true;
				$id = $pageId;
				continue;
			}
		}

		if(!$isRead) return false;

		// ブログページの方の設定を調べる
		if(!isset($this->configPerBlog{$id})) return true;	// ←ブログページでは無いことがわかる

		/** @ToDo ブログページのURIが空、トップページのURIが空の対策 */
		$blogUriList = self::_getBlogUriList();
		if(!isset($blogUriList[$id])) return false;

		$blogUri = str_replace("//", "/", "/".soycms_get_blog_page_object($id)->getUri()."/");
		foreach($blogUriList[$id] as $u){
			if($blogUri === $u){
				if($uri === $u) return true;
			}else{
				if(soy2_strpos($uri, $u) === 0) return true;
			}
		}

		return false;
	}

	/**
	 * @return array
	 */
	private function _getUriList(){
		static $l;
		if(is_array($l)) return $l;
		
		$l = array();
		if(!is_array($this->configPerPage) || !count($this->configPerPage)) return $l;

		
		try{
			$res = soycms_get_hash_table_dao("page")->executeQuery("SELECT id, uri FROM Page WHERE id IN (".implode(",", array_keys($this->configPerPage)).")");
		}catch(Exception $e){
			$res = array();
		}

		if(!count($res)) return $l;
		
		foreach($res as $v){
			$l[(int)$v["id"]] = "/".trim($v["uri"]);
		}
		return $l;
	}

	/**
	 * @return array
	 */
	private function _getBlogUriList(){
		static $l;
		if(is_array($l)) return $l;
		
		$l = array();
		if(!count($this->configPerBlog)) return $l;

		try{
			$res = soycms_get_hash_table_dao("page")->executeQuery("SELECT id, uri, page_config FROM Page WHERE page_type = 200 AND id IN (".implode(",", array_keys($this->configPerBlog)).")");
		}catch(Exception $e){
			$res = array();
		}

		if(!count($res)) return $l;

		$uriList = array();
		foreach($res as $v){
			$cnf = soy2_unserialize($v["page_config"]);
			if(isset($this->configPerBlog[$v["id"]]["_top_"]) && (int)$this->configPerBlog[$v["id"]]["_top_"] === 1){
				$uriList[] = str_replace("//", "/", "/".$v["uri"]."/".(string)$cnf->topPageUri."/");
			}

			if(isset($this->configPerBlog[$v["id"]]["_month_"]) && (int)$this->configPerBlog[$v["id"]]["_month_"] === 1){
				$uriList[] = str_replace("//", "/", "/".$v["uri"]."/".(string)$cnf->monthPageUri."/");
			}

			if(isset($this->configPerBlog[$v["id"]]["_category_"]) && (int)$this->configPerBlog[$v["id"]]["_category_"] === 1){
				$uriList[] = str_replace("//", "/", "/".$v["uri"]."/".(string)$cnf->categoryPageUri."/");
			}

			if(isset($this->configPerBlog[$v["id"]]["_entry_"]) && (int)$this->configPerBlog[$v["id"]]["_entry_"] === 1){
				$uriList[] = str_replace("//", "/", "/".$v["uri"]."/".(string)$cnf->entryPageUri."/");
			}

			if(count($uriList)) $l[(int)$v["id"]] = $uriList;
		}

		return $l;
	}

	/**
	 * すでにバックアップファイルを生成済みか？
	 * @param string
	 * @return bool
	 */
	private function _isExistedBackupFile(string $url){
		static $backupDir;
		if(is_null($backupDir)) {
			$backupDir = self::getBackupDirectory() . UserInfoUtil::getSite()->getSiteId() . "/";
			if(!file_exists($backupDir)) mkdir($backupDir);
		}

		$uri = str_replace(UserInfoUtil::getSitePublishURL(), "", $url);
		return (file_exists(self::_getBackupFilePath($backupDir, $uri)));
	}

	function compress(){
		// @ToDo zipArchive版も作成する必要があるかも
		chdir(self::_backupDirectory());
		exec("zip -r ".self::_backupZipFileName()." .");
	}

	function download(){
		$zip = self::_backupZipFileName();
		header('Content-Type: application/force-download;');
		header('Content-Length: '.filesize($zip));
		header('Content-Disposition: attachment; filename="'.self::ZIP_FILE_NAME.'"');
		readfile($zip);

		//サーバー内のzipを削除
		unlink($zip);
	}

	function getBackupZipFilePath(){
		return self::_backupZipFileName();
	}

	function getBackupZipFileUrl(){
		return str_replace($_SERVER["DOCUMENT_ROOT"], "", self::_backupZipFileName());
	}

	private function _backupZipFileName(){
		if(defined("_SITE_ROOT_")){
			$siteRoot = rtrim(_SITE_ROOT_, "/")."/";
		}else{
			$siteRoot = UserInfoUtil::getSiteDirectory();
		}
		$dir = $siteRoot.self::ZIP_DIRECTORY_NAME."/";
		if(!file_exists($dir)) mkdir($dir);

		if(!file_exists($dir.".htaccess")) file_put_contents($dir.".htaccess", "Deny from all");

		return $dir.self::ZIP_FILE_NAME;
	}

	function setConfigPerPage(array $configPerPage){
		$this->configPerPage = $configPerPage;
	}
	function setConfigPerBlog(array $configPerBlog){
		$this->configPerBlog = $configPerBlog;
	}
}