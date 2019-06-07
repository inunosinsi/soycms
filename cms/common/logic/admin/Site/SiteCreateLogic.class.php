<?php

class SiteCreateLogic extends SOY2LogicBase{

	const CONTROLLER_FILENAME = "index.php";
	const IMAGE_CONVERTER_FILENAME = "im.php";
	const CLOSED_DIR_PERMISSION = 0700;
	const OPEN_DIR_PERMISSION   = 0777;
	const SITE_DIR_PERMISSION   = 0755;

	var $siteDirPath;
	var $dbDirPath;
	var $cacheDirPath;
	var $templateDirPath;
	var $pluginDirPath;
	var $entryTemplateDirPath;
	var $uploadDirPath;

	private $tmpLogFile;
	private $logFile;

	var $charset;

	//DBの接続先
	public $dsn;
	var $user;
	var $pass;

	/**
	 * サイトの初期化
	 * ファイルとディレクトリの作成とテーブル作成
	 *
	 * @param $siteId サイトのID
	 */
	public function createNewSite($siteId, $dbType = SOYCMS_DB_TYPE){

		//ディレクトリとパスを決める
		$this->prepare($siteId);
		$this->log("Create a new site [$siteId]");

		//DSNが空なら入れておく。MySQLのアカウントも。
		$this->setDefaultDBInfo($siteId, $dbType);
		$this->log("DSN: {$this->dsn}; USER: {$this->user}; PASSWORD: ----");

		//ディレクトリを作る
		$this->log("Create site directory: {$this->siteDirPath}");
		CMSUtil::makeDir($this->siteDirPath, self::SITE_DIR_PERMISSION);

		$this->makeDirs();

		//ファイルを作る（既存のファイルはバックアップを取った上で上書き、）
		$this->createHtaccess($siteId, false);//新規サイトはルートサイトではない
		$this->createController($siteId, $dbType);
		$this->copy_im_php();

		//デフォルトのアップロード先を作る
		$this->createDefaultUploadDir();

		//css, js, imagesディレクトリも作る
		$this->createUserDir();

		//データベース作成
		$this->initDB($dbType);

		//同梱テンプレートをコピー
		$this->addTemplatePack();

		$this->log("Site created");
		$this->move_log();
	}

	/**
	 * パスの設定
	 */
	private function prepare($siteId){
		$dirPath = SOYCMS_TARGET_DIRECTORY . $siteId ."/";

		$this->siteDirPath          = $dirPath;
		$this->dbDirPath            = $dirPath .".db/";
		$this->cacheDirPath         = $dirPath .".cache/";
		$this->templateDirPath      = $dirPath .".template/";
		$this->pluginDirPath        = $dirPath .".plugin/";
		$this->entryTemplateDirPath = $dirPath .".entry_template/";

		$this->uploadDirPath        = $dirPath ."files/";

		$this->logFile = $this->dbDirPath."soycms.log";
		SOY2::import("util.ServerInfoUtil");
		if(ServerInfoUtil::sys_get_writable_temp_dir()) $this->tmpLogFile = tempnam(ServerInfoUtil::sys_get_writable_temp_dir(), 'soycms');
	}

	/**
	 * SOY Shop用の設定
	 */
	private  function prepareSOYShop($site){
		define("SOYSHOP_ID",$site->getSiteId());
		define("SOYSHOP_SITE_DIRECTORY",$site->getPath());
		$webappDir = str_replace("src/","",SOYSHOP_COMMON_DIR);
		define("SOYSHOP_WEBAPP",$webappDir);
		define("SOYSHOP_SITE_CONFIG_FILE",$webappDir . "conf/shop/" . SOYSHOP_ID . ".conf.php");
	}

	/**
	 * サイト用ディレクトリ作成
	 */
	private function makeDirs(){
		$closed_dirs = array(
				$this->dbDirPath,
				$this->cacheDirPath,
				$this->templateDirPath,
				$this->pluginDirPath,
				$this->entryTemplateDirPath
		);
		foreach($closed_dirs as $dir){
			$this->log("Create directory: ".basename($dir));
			CMSUtil::makeDir($dir, self::CLOSED_DIR_PERMISSION);
		}
	}

	/**
	 * htaccessとcontrollerの設定
	 */
	public function rebuild($id){
		$site = SOY2DAOFactory::create("admin.SiteDAO")->getById($id);
		$siteId = $site->getSiteId();

		//@TODO rebuild for SOY Shop
		if($site->getSiteType() == Site::TYPE_SOY_CMS){
			$this->prepare($siteId);
			$this->log("Rebuild index.php, im.php, .htaccess");
		}else{
			$this->prepareSOYShop($site);

		}

		$this->dsn = $site->getDataSourceName();

		if(SOYCMS_DB_TYPE == "mysql"){
			$this->user = ADMIN_DB_USER;
			$this->pass = ADMIN_DB_PASS;
		}

		if($site->getSiteType() == Site::TYPE_SOY_CMS){
			$this->createHtaccess($siteId, $site->getIsDomainRoot());
			$this->createController($siteId);
			$this->copy_im_php();
		}else{
			$this->createSOYShopController();
		}

	}

	/**
	 * サイト設定の初期化
	 * @param String $siteName
	 * @param int $encoding
	 */
	public function initSiteConfig($siteName,$encoding){

		$siteConfig = new SiteConfig();
		//ラベルの階層化を有効にする
		$siteConfig->setUseLabelCategory(true);
		$cnf = $siteConfig->getSiteConfig();

		$pdo = $this->getSitePDO();
		$sql = "insert into SiteConfig(name,charset,siteConfig) values (:name,:encoding,:siteConfig)";
		$stmt = $pdo->prepare($sql);
		$stmt->bindParam(":name",$siteName);
		$stmt->bindParam(":encoding",$encoding);
		$stmt->bindParam(":siteConfig",$cnf);

		$stmt->execute();

	}

	/**
	 * トップページとエラーページの作成
	 *
	 * @param string siteUrl サイトのURL
	 */
	public function initDefaultPage($siteUrl){


		$sql = "insert into Page(title,uri,template,page_type,udate) values(:title,:uri,:template,:pagetype,:udate)";
		$pdo = $this->getSitePDO();
		$stmt = $pdo->prepare($sql);

		SOY2::import("domain.cms.Page");

		/*
		 //TOPページ
		 $uri = "";
		 $title = "TOP";
		 $template = file_get_contents(dirname(__FILE__)."/TopPage.html");
		 $pageType = Page::PAGE_TYPE_NORMAL;

		 $stmt->bindParam(":uri",$uri);
		 $stmt->bindParam(":title",$title);
		 $stmt->bindParam(":template",$template);
		 $stmt->bindParam(":pagetype",$pageType);
		 $stmt->bindParam(":udate",time());
		 $stmt->execute();

		 */

		//404ページ
		$uri = "_notfound";
		$title = (defined("SOYCMS_LANGUAGE") && SOYCMS_LANGUAGE=="ja") ? "ページが見つかりません":"404 Not Found";
		$template =  file_get_contents(dirname(__FILE__)."/404.html");
		$template = str_replace("@@SITE_LINK;",$siteUrl,$template);
		$pageType = Page::PAGE_TYPE_ERROR;
		$time = time();

		$stmt->bindParam(":uri",$uri);
		$stmt->bindParam(":title",$title);
		$stmt->bindParam(":template",$template);
		$stmt->bindParam(":pagetype",$pageType);
		$stmt->bindParam(":udate",$time);
		$stmt->execute();

	}

	private function initDB($dbType = SOYCMS_DB_TYPE){
		$this->log("Init database: "."init_site_".$dbType.".sql");

		$pdo = $this->getSitePDO();

		$sql = file_get_contents(CMS_SQL_DIRECTORY . "init_site_".$dbType.".sql");
		$sqls = explode(";",$sql);

		foreach($sqls as $sql){
			try{
				$pdo->exec($sql);
			}catch(Exception $e){
				//Table exists
			}
		}

		/*
		 * データベース（site）のバージョンを保存する
		 */
		$logic = SOY2LogicContainer::get("logic.db.DBVersionLogic", array(
				"target" => "site"
		));
		//DNS切り替え
		SOY2DAOConfig::Dsn($this->dsn);
		$logic->registerCurrentSQLVersion();
		//戻す
		SOY2DAOConfig::Dsn(ADMIN_DB_DSN);
	}

	private function &getSitePDO($dbType = SOYCMS_DB_TYPE){

		static $pdo;

		if(!$pdo){

			switch($dbType){
				case "mysql":
					$pdo = new PDO($this->dsn,$this->user,$this->pass,array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION));

					try{
						$pdo->exec("set names 'utf8'");
					}catch(Exception $e){
						//for mysql 4.0
					}
					break;
				case "sqlite":
				default:
					$pdo = new PDO($this->dsn,$this->user,$this->pass,array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION));
					break;
			}
		}

		return $pdo;
	}

	/**
	 * MySQL版のみ
	 * データベースの作成
	 */
	public function createDataBase($siteId, $dbType = SOYCMS_DB_TYPE){

		if($dbType != "mysql")return;

		$dbExists = false;

		$dao = new SOY2DAO();
		try{
			$res = $dao->executeUpdateQuery("create database ".$this->getDatabaseName($siteId)." CHARACTER SET utf8",array());
			return true;
		}catch(SOY2DAOException $e){
			if(stripos($e->getPDOExceptionMessage(), "database exists") !== false){
				//すでに存在するならそれを使う
				return true;
			}else{
				throw $e;
			}
		}catch(Exception $e){
			throw $e;
		}

		if(!$dbExists && !$res){
			throw new Exception("Failed to create a new datebase.");
		}
	}

	/**
	 * データベース名：MySQL用
	 */
	public function getDatabaseName($siteId){
		return "soycms_".$siteId;
	}

	private function createHtaccess($siteId, $isRoot = false){
		$this->log("Create an htaccess file for mod_rewrite");

		$htaccess = $this->getHtaccess($siteId, $isRoot);

		$filename = $this->siteDirPath.".htaccess";
		CMSUtil::createBackup($filename);
		file_put_contents($filename, $htaccess);

		$this->createDenyAccessHtaccess($this->dbDirPath);
		$this->createDenyAccessHtaccess($this->cacheDirPath);
		$this->createDenyAccessHtaccess($this->templateDirPath);
		$this->createDenyAccessHtaccess($this->pluginDirPath);
		$this->createDenyAccessHtaccess($this->entryTemplateDirPath);
	}

	public function getHtaccess($siteId, $isRoot = false){
		$tmp = array();

		$tmp[] = "# @generated by SOY CMS at " . date("Y-m-d H:i:s");
		$tmp[] = "RewriteEngine on";

		if($isRoot){
			//ルートサイト設定済であれば実ファイルがなければルートに飛ばす
			$tmp[] = "RewriteCond %{REQUEST_FILENAME} !-f";
			$tmp[] = 'RewriteRule ^.*$ /$0 [R=301,L,NE]';//NEでurlencodeされないようにする
		}else{
			$tmp[] = "RewriteBase /".$siteId;
			$tmp[] = "";
			$tmp[] = "# 常にhttpsでアクセスさせる（httpでのアクセスをhttpsにリダイレクトする）";
			$tmp[] = "#RewriteCond %{HTTPS} =off";
			$tmp[] = "#RewriteRule ^(.*)$ https://%{HTTP_HOST}%{REQUEST_URI} [R=301,L,QSA]";
			$tmp[] = "";
			$tmp[] = "RewriteCond %{IS_SUBREQ} =false";
			$tmp[] = "RewriteCond %{REQUEST_FILENAME} !-f";
			$tmp[] = "RewriteCond %{REQUEST_FILENAME}/".self::CONTROLLER_FILENAME." !-f";
			$tmp[] = "RewriteCond %{REQUEST_FILENAME}/index.html !-f";
			$tmp[] = "RewriteCond %{REQUEST_FILENAME}/index.htm !-f";
			$tmp[] = "RewriteCond %{REQUEST_URI} !^/server-status";# Apacheのmod_statusへの配慮
			$tmp[] = "RewriteCond %{REQUEST_URI} !^/server-info";  # Apacheのmod_infoへの配慮
			$tmp[] = "RewriteCond %{REQUEST_URI} !/".self::CONTROLLER_FILENAME."/";

			if(defined("SOYCMS_PHP_CGI_MODE") && SOYCMS_PHP_CGI_MODE == true){
				$tmp[] = 'RewriteRule ^(.*)$ '.self::CONTROLLER_FILENAME.'?pathinfo=$1&%{QUERY_STRING} [L]';
			}else{
				$tmp[] = 'RewriteRule ^(.*)$ '.self::CONTROLLER_FILENAME.'/$1 [L]';
			}
		}

		return implode("\n",$tmp);
	}

	/**
	 * アクセスを拒否する.htaccessを指定ディレクトリに作成する
	 */
	private function createDenyAccessHtaccess($dir){
		$this->log("Create an htaccess file for denying access: ".basename($dir));

		if(file_exists($dir) && is_dir($dir) && is_writable($dir)){
			if($dir[strlen($dir)-1] != "/") $dir .= "/";
			file_put_contents($dir.".htaccess", "Deny from all");
		}
	}

	private function createController($siteId, $dbType = SOYCMS_DB_TYPE){
		$this->log("Create the front controller");

		$filename = $this->siteDirPath.self::CONTROLLER_FILENAME;

		CMSUtil::createBackup($filename);
		file_put_contents($filename, $this->getController($siteId, $dbType));

		$this->makeExecutableForCGI($filename);
	}

	/**
	 * @return String
	 */
	public function getController($siteId, $dbType = SOYCMS_DB_TYPE){

		//DB情報が必要
		if(strlen($this->dsn) == 0){
			//$this->dsn = $site->getDataSourceName()
			$this->setDefaultDBInfo($siteId, $dbType);
		}

		return $this->__getController($this->dsn, $this->user, $this->pass, $dbType);
	}

	/**
	 * @return String
	 */
	public function getControllerForRenew($site, $dbType = SOYCMS_DB_TYPE){

		//DB情報が必要
		if(strlen($this->dsn) == 0){
			//DSNは現在保存されているものを使う
			$this->dsn = $site->getDataSourceName();
			//User, Password
			$this->setDefaultDBInfo($site->getSiteId(), $dbType);
		}

		return $this->__getController($this->dsn, $this->user, $this->pass, $dbType);
	}

	/**
	 * @return String
	 */
	private function __getController($dsn, $dbUser, $dbPassword, $dbType = SOYCMS_DB_TYPE){

		$controller = array();
		$controller[] = "<?php ";
		$controller[] = "/* @generated by SOY CMS at " . date("Y-m-d H:i:s") . "*/";

		if(defined("SOYCMS_PHP_CGI_MODE") && SOYCMS_PHP_CGI_MODE == true){
			$controller[] = 'if(isset($_GET["pathinfo"])){';
			$controller[] = '$_SERVER["PATH_INFO"] = "/" . $_GET["pathinfo"];';
			$controller[] = 'unset($_GET["pathinfo"]);';
			$controller[] = '}';
		}

		$controller[] = 'define("_SITE_ROOT_",dirname(__FILE__));';
		$controller[] = 'define("_SITE_DSN_","'.$dsn.'");';
		$controller[] = 'define("_SITE_DB_FILE_",_SITE_ROOT_."/.db/'.$dbType.'.db");';
		if(strlen($dbUser))$controller[] = 'define("_SITE_DB_USER_",    "'.$dbUser.'");';
		if(strlen($dbPassword))$controller[] = 'define("_SITE_DB_PASSWORD_","'.$dbPassword.'");';
		$controller[] = 'define("_CMS_COMMON_DIR_", "'.dirname(CMS_SITE_INCLUDE).'");';
		$controller[] = 'include(_CMS_COMMON_DIR_."/site.func.php");';
		$controller[] = 'execute_site();';
		$controller[]  = "";

		return implode("\n",$controller);
	}

	//SOY ShopのInitLogic.class.phpのメソッドを読み込む
	private function createSOYShopController(){
		$htaccessPath = SOYSHOP_SITE_DIRECTORY . ".htaccess";
		CMSUtil::createBackup($htaccessPath);
		$filename = SOYSHOP_SITE_DIRECTORY . "index.php";
		CMSUtil::createBackup($filename);

		include(SOYSHOP_COMMON_DIR . "logic/init/InitLogic.class.php");
		$logic = new InitLogic();

		$logic->initController(true);
	}

	/**
	 * デフォルトのアップロード先を作る
	 */
	private function createDefaultUploadDir(){
		$this->log("Create the default uploading directory: ".basename($this->uploadDirPath));

		CMSUtil::makeDir($this->uploadDirPath,self::OPEN_DIR_PERMISSION);

		//TODO ディレクトリの説明のreadmeがあってもよいかも
	}

	/**
	 * ユーザーのためのディレクトリを作る
	 * css, js, image
	 */
	private function createUserDir(){
		$this->log("Create user's directories: css, js, image");

		//TODO ディレクトリの説明のreadmeがあってもよいかも

		$dirs = array(
				$this->siteDirPath."css",
				$this->siteDirPath."js",
				$this->siteDirPath."image",
		);
		foreach($dirs as $dir){
			CMSUtil::makeDir($dir, self::OPEN_DIR_PERMISSION);
		}
	}

	private function copy_im_php(){
		$this->log("Copy im.php");

		$imphp = array();
		$imphp[] = '<?php';
		$imphp[] = '$site_root = dirname(__FILE__);';
		$imphp[] = 'include_once("'.dirname(CMS_SITE_INCLUDE).'/im.inc.php'.'");';
		$imphp[] = '';

		$filename = $this->siteDirPath.self::IMAGE_CONVERTER_FILENAME;

		CMSUtil::createBackup($filename);
		file_put_contents($filename,implode("\n",$imphp));

		$this->makeExecutableForCGI($filename);
	}

	/**
	 * サイトが作成されているかどうかを確認する。なければ例外を投げる
	 * @throw
	 */
	public function checkIfSiteCreated(){
		//DB
		$pdo = $this->getSitePDO();
		$stmt = $pdo->prepare("select * from SiteConfig");
		$stmt->execute();

		//サイトのディレクトリ
		if(!file_exists($this->siteDirPath)){
			$this->log("Failed to create the directory of the site. {$this->siteDirPath}");
			throw new Exception("Failed to create the directory of the site. {$this->siteDirPath}");
		}
	}

	/**
	 * CGIモードのときはファイルに実行権限を付与する
	 */
	private function makeExecutableForCGI($filename){
		if(defined("SOYCMS_PHP_CGI_MODE") && SOYCMS_PHP_CGI_MODE == true){
			if(!is_executable($filename)){
				$perms = fileperms($filename) | 0100;
				$res = @chmod($filename, $perms);

				$this->log("CGI Mode. Make ".basename($filename)." executable. ".( $res ? "Success" : "Failed"));
			}
		}
	}

	/**
	 * ログを出力
	 * 事前にprepareが必要
	 */
	public function log($text){
		$logfile = file_exists($this->logFile) ? $this->logFile : $this->tmpLogFile;
		@file_put_contents($logfile, date(DATE_RFC2822)." {$text}.\r\n", FILE_APPEND);
	}

	/**
	 * テンポラリディレクトリのログを.db/soycms.logに移動する
	 */
	public function move_log(){
		if(file_exists($this->tmpLogFile)){
			@file_put_contents($this->logFile, file_get_contents($this->tmpLogFile), FILE_APPEND);
			unlink($this->tmpLogFile);
		}
	}

	/**
	 * ログを全体のログに移動する
	 */
	public function move_log_to_common_log(){
		if(file_exists($this->tmpLogFile)){
			error_log(file_get_contents($this->tmpLogFile));
		}
		if(file_exists($this->logFile)){
			error_log(file_get_contents($this->logFile));
		}
	}

	/**
	 * MySQL版のときは$this->userと$this->
	 * SQLite版のときは"sqlite:".$this->dbDirPath."sqlite.db"
	 */
	private function setDefaultDBInfo($siteId, $dbType = SOYCMS_DB_TYPE){
		switch($dbType){
			case "mysql":
				$this->user = ADMIN_DB_USER;
				$this->pass = ADMIN_DB_PASS;
				break;
			case "sqlite":
			default:
		}
		if(strlen($this->dsn) == 0){
			switch($dbType){
				case "mysql":
					$this->dsn = preg_replace('/dbname=([^;=]+)/',"dbname=".$this->getDatabaseName($siteId),ADMIN_DB_DSN);
					break;
				case "sqlite":
				default:
					$this->dsn = "sqlite:".$this->dbDirPath."sqlite.db";
					break;
			}
		}
	}

	/**
	 * 同梱テンプレートパックをサイトにコピー
	 */
	private function addTemplatePack(){
		$templateDir = dirname(__FILE__)."/TemplatePack";

		if(is_dir($templateDir)){
			$this->log("Copy template packs");
			$this->log("     from $templateDir to {$this->templateDirPath}");
			$this->log("     to {$this->templateDirPath}");

			$files = scandir($templateDir);
			foreach($files as $file){
				if($file[0] == ".")continue;
				if(!is_file($templateDir."/".$file))continue;
				copy($templateDir."/".$file, $this->templateDirPath."/".$file);
				$this->log("Copied: ".$file);
			}
		}
	}

}
