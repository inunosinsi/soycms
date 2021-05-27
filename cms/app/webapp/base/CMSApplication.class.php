<?php

class CMSApplication {


	/**
	 * ルートを出力
	 */
	public static function getRoot(){
		$self = CMSApplication::getInstance();
		return $self->root;
	}

	/**
	 * SOY Shopのパスを出力
	 */
	public static function getShopRoot(){
		$self = CMSApplication::getInstance();
		return str_replace("app/","soyshop/",$self->root);
	}

	/**
	 * 現在読み込んでいるアプリケーションのルートを出力
	 */
	public static function getApplicationRoot(){
		$self = CMSApplication::getInstance();
		return CMSApplication::getRoot() . 'index.php/'. $self->applicationId . "/";
	}

	/**
	 * 引数を渡す
	 */
	public static function getArguments(){
		$self = CMSApplication::getInstance();
		return $self->arguments;
	}

	/**
	 * タイトルを取得
	 */
	public static function getTitle(){
		$self = CMSApplication::getInstance();
		$title = (strlen($self->title) > 0) ? $self->title . " - " : "";
		$title .= (isset($self->properties["title"])) ? $self->properties["title"] : "";
		$title .= (strlen($title)>0) ? " | " : "";
		$cmsName = (defined("SOYCMS_CMS_NAME")) ? SOYCMS_CMS_NAME : "SOY CMS";
		$title .= $cmsName . " Application";
		return $title;
	}

	/**
	 * アプリケーションの名前を取得
	 */
	public static function getApplicationName(){
		$self = CMSApplication::getInstance();
		$title = (isset($self->properties["title"])) ? $self->properties["title"] : "";
		return $title;
	}

	/**
	 * アプリケーションの名前の横にページ毎から出力した内容を表示する
	 */
	public static function getApplicationNameAdding(){
		$self = CMSApplication::getInstance();
		if(isset($self->adding) && strlen($self->adding)){
			return $self->adding;
		}
		return "";
	}

	/**
	 * アプリ操作者の際にアカウントの編集画面を表示するか？
	 */
	public static function getDisplayAccountEditPanelConfig(){
		$self = CMSApplication::getInstance();
		if(!isset($self->properties["display_edit_panel"])) return true;	//設定が特になければ出力する
		return ($self->properties["display_edit_panel"] == "Yes");
	}

	/**
	 * ショップIDを取得する
	 */
	public static function getShopId(){
		$self = CMSApplication::getInstance();
		$shopid = (isset($self->properties["shopid"])) ? $self->properties["shopid"] : "shop";
		return $shopid;
	}

	/**
	 * Scriptを表示
	 */
	public static function printScript(){
		$self = CMSApplication::getInstance();

		$scripts = $self->scripts;
		foreach($scripts as $script){
			echo $script . "\n";
		}
	}

	/**
	 * CSSを表示
	 */
	public static function printLink(){
		$self = CMSApplication::getInstance();

		$links = $self->links;
		foreach($links as $link){
			echo $link . "\n";
		}
	}

	/**
	 * 上部メニューを表示
	 */
	public static function printUpperMenu(){
		$self = CMSApplication::getInstance();
		$menus = $self->menus;

		$html = "";

		foreach($menus as $key => $menu){
			if(!is_numeric($key)){
				$id = $key;
			}else{
				$id = $self->applicationId . "_menu_" . $key;
			}

			if(isset($menu["label"]) && strlen($menu["label"]) > 0){
				$label = "[" . $menu["label"] . "]";
			}else{
				continue;
			}

			$href = (isset($menu["href"])) ? ' href="'.htmlspecialchars($menu["href"],ENT_QUOTES).'" ' : ' href="javascript:void(0);" ';
			$onclick = (isset($menu["onclick"])) ? ' onclick="'.htmlspecialchars($menu["onclick"],ENT_QUOTES) . '" ' : "";

			$html .= '<a'.$href.$onclick.'>' . $label . '</a>' . "\n";
		}

		echo $html;
	}

	/**
	 * 上部メニューは持ってるかな？
	 */
	public static function hasUpperMenu(){
		$self = CMSApplication::getInstance();
		return count($self->menus);
	}

	/**
	 * タブを表示
	 */
	public static function printTabs(){
		$self = CMSApplication::getInstance();
		$tabs = $self->tabs;

		$properties = $self->properties;

		//modeプロパティにはどのテンプレートを使うか？の値が格納されている。modeプロパティはrun関数で定義されている
		$isThreeTemp = ($self->mode == "three" || $self->mode == "analyzer");	//SOY CMS3系
		$isCustomTemp = (!$isThreeTemp && $self->mode !== "template" && $self->mode !== "wide" && $self->mode !== "plain");

		$html = "";
		$isActive = (is_null($self->activeTab));

		if($isCustomTemp) $html .= "<ul class=\"clearfix\">\n";
		if($isThreeTemp) $html .= "<ul class=\"nav\" id=\"side-menu\">\n";
		foreach($tabs as $key => $tab){
			if(isset($tab["visible"]) && $tab["visible"] === false)continue;
			if(!is_numeric($key)){
				$id = $key;
			}else{
				$id = $self->applicationId . "_tab_" . $key;
			}

			if(isset($tab["label"]) && strlen($tab["label"]) > 0){
				$label = $tab["label"];
			}else{
				continue;
			}

			$href = (isset($tab["href"])) ? ' href="'.htmlspecialchars($tab["href"],ENT_QUOTES).'" ' : ' href="javascript:void(0);" ';
			$onclick = (isset($tab["onclick"])) ? ' onclick="'.htmlspecialchars($tab["onclick"],ENT_QUOTES) . '" ' : "";

			if($isThreeTemp){
				$className = "tab_inactive";
			}else{
				$className = "menu";
			}

			if($isActive){
				if(!$isThreeTemp || !$isCustomTemp) $className .= " menu_active";
				if($isThreeTemp) $className = "tab_active";
				$isActive = false;
			}else if($key == $self->activeTab){
				if(!$isThreeTemp || !$isCustomTemp) $className .= " menu_active";
				if($isThreeTemp) $className = "tab_inactive";
			}

			if($isThreeTemp || $isCustomTemp) $html .= "<li>\n";
			if($isThreeTemp){	//3系
				//iconは各Appのadmin.phpで持つ。https://fontawesome.com のアイコンを使用する。指定時はfa-***の***の個所のみ指定
				$icon = (isset($tab["icon"]) && strlen($tab["icon"])) ? trim($tab["icon"]) : "angle-right";
				$html .= '<a class="'.$className.'"'.$href.$onclick.'><i class="fa fa-' . $icon . ' fa-fw"></i>' . '<span class="'.$className.'">' . $label . '</span></a>' . "\n";
			}else{
				$html .= '<a class="'.$className.'"'.$href.$onclick.'>' . '<div class="'.$className.'" id="'.$id.'">' . $label . '</div></a>' . "\n";
			}
			if($isThreeTemp || $isCustomTemp) $html .= "</li>\n";
		}


		$isIframe = false;
		$h = 400;	//iframeの高さ	後で読み込むiframe.phpがあれば、hの指定も可能
		if($isThreeTemp){
			$iframeDir = SOY2HTMLConfig::PageDir() . "Iframe/";
			$isIframe = (file_exists($iframeDir) && is_dir($iframeDir));	//pagesディレクトリにIframeがある場合
			// アプリ内ではIframeディレクトリがあっても、ないと見なしたい時があるのでその対応
			// /ルート/CMSインストールディレクトリ/app/webapp/APPLICATION_ID/iframe.phpに特別な設定を記述できる iframe.phpには$isIframeをbool値で指定 hでiframeの高さの指定
			if($isIframe && file_exists(dirname(dirname(__FILE__)) . "/" . APPLICATION_ID . "/iframe.php")) include_once(dirname(dirname(__FILE__)) . "/" . APPLICATION_ID . "/iframe.php");
			unset($iframeDir);
		}

		if($isThreeTemp) {
			if($isIframe) $html .= "<li><iframe id=\"soyapp_iframe\" src=\"" . SOY2PageController::createLink(APPLICATION_ID . ".Iframe?mode=print") . "\"></iframe></li>";
			if( isset($_COOKIE["app-hide-side-menu"]) && $_COOKIE["app-hide-side-menu"] == "true" ){
				$html .= "<li class=\"hidden-xs\"><a href=\"#\" id=\"toggle-side-menu\" class=\"text-right\"><i class=\"fa fa-fw fa-angle-right\"></i><span>&nbsp;</span></a></li>";
			}else{
				$html .= "<li class=\"hidden-xs\"><a href=\"#\" id=\"toggle-side-menu\" class=\"text-right\"><i class=\"fa fa-fw fa-angle-left\"></i><span>&nbsp;</span></a></li>";
			}
		}
		if($isThreeTemp || $isCustomTemp) $html .= "</ul>\n";

		//soyapp_iframeのCSS
		if($isIframe) $html .= "<style>#soyapp_iframe{width:100%; height:" . $h . "px; border:1px solid #FFFFFF;}</style>";

		echo $html;
	}

	/**
	 * Applicationのメインを表示
	 */
	public static function printApplication(){
		$self = CMSApplication::getInstance();
		echo $self->application;
	}

	/**
	 * StandAlone版かどうか
	 */
	public static function isStandalone(){
		return false;
	}

	/**
	 * jump
	 */
	public static function jump($path = "", $array = array()){
		$self = CMSApplication::getInstance();
		$path = $self->applicationId . "." . $path;
		SOY2PageController::jump($path);
	}

	public static function createLink($path = "", $isAbsoluteUrl = false){
		$self = CMSApplication::getInstance();
		$path = $self->applicationId . "." . $path;
		return SOY2PageController::createLink($path, $isAbsoluteUrl);
	}

	/* CMSApplication */
	private $root;
	private $applicationId;
	private $appMain;
	private $arguments = array();
	private $tabs = array();
	private $activeTab = "";
	private $menus = array();
	private $scripts = array();
	private $links = array();
	private $adding;
	private $application;
	private $mode = "template";
	private $title = "";
	private $properties = array();

	private function __construct(){}

	/**
	 * singleton
	 */
	private static function getInstance(){
		static $_instance;
		if(!$_instance)$_instance = new CMSApplication();

		return $_instance;
	}

	/**
	 * 実行
	 */
	public static function run(){
		$self = CMSApplication::getInstance();
		$self->root = SOY2PageController::createRelativeLink("./");

		//pathinfoからアプリケーションIDを取得
		$pathinfo = (isset($_SERVER["PATH_INFO"])) ? $_SERVER["PATH_INFO"] : "";

		if(strlen($pathinfo)<1){
			SOY2PageController::redirect("../admin/");
			exit;
		}

		$paths = array_values(array_diff(explode("/",$pathinfo),array("")));
		if(count($paths)<1){
			SOY2PageController::redirect("../admin/");
			exit;
		}
		$self->applicationId = $paths[0];

		$self->arguments = array_slice($paths,1);

		//キャッシュディレクトリの指定
		$cacheDir = dirname(dirname(dirname(__FILE__)))."/cache/".$self->applicationId."/";
		if(!file_exists($cacheDir)){
			if(!@mkdir($cacheDir,0777,true)){
				throw new SOY2HTMLException("Cannot create Application Cache Directory: ".$cacheDir);
			}
		}
		SOY2HTMLConfig::CacheDir($cacheDir);
		SOY2DAOConfig::DaoCacheDir($cacheDir);

		//soycms設定を読み込む
		if(file_exists(CMS_COMMON . "config/custom.config.php")){
			include_once(CMS_COMMON . "config/custom.config.php");
		}else{
			include_once(CMS_COMMON . "soycms.config.php");
		}

		//ログインチェックを行う
		if(!self::checkLogin($self->applicationId)){
			SOY2PageController::redirect("../admin/?r=".rawurlencode(SOY2PageController::createRelativeLink($_SERVER["REQUEST_URI"])));
			exit;
		}

		//アプリケーションのチェック
		$base = dirname(dirname(__FILE__)) . "/";
		if(!file_exists($base . $self->applicationId)
			|| !file_exists($base . $self->applicationId . "/admin.php")
		){
			SOY2PageController::redirect("../admin/");
			exit;
		}

		//ApplicationIdの登録
		if(!defined("APPLICATION_ID"))define("APPLICATION_ID",$self->applicationId);

		//設定ファイルの読み込み
		$self->properties = (file_exists($base . $self->applicationId . "/application.ini")) ? parse_ini_file($base . $self->applicationId . "/application.ini") : array();

		//カスタムテンプレートを利用する
		$properties = $self->properties;
		if(isset($properties["template"])){
			//エクストラモードの指定がある場合、IS_EXT_MODE定数の値を確認する
			if(defined("IS_EXT_MODE")){
				if(IS_EXT_MODE){
					$self->mode = $properties["template"];
				}
			//エクストラモードの指定がない場合は、条件なしでiniファイルに記載されてるテンプレートの種類を見る
			}else{
				$self->mode = $properties["template"];
			}
		}

		//アプリケーションの読み込み
		include_once($base . $self->applicationId . "/admin.php");

		//appMain設定されてるかな？
		if(is_null($self->appMain)){
			SOY2PageController::redirect("../admin/");
			exit;
		}

		//実行
		$self->application = call_user_func($self->appMain);

		//アプリ名の横にページごとに指定したHTMLを表示したい admin.phpで$appにクラスのインスタンスを入れている場合のみ使用可
		if(isset($app) && is_object($app)){
			$className = get_class($app);
			if(class_exists($className) && method_exists($className, "add")){
				$self->adding = $className::add();
			}
		}
	}

	/**
	 * 出力する
	 */
	public static function display(){
		$self = CMSApplication::getInstance();

		if(isset($_GET["mode"]) && $_GET["mode"] == "print"){
			include_once(dirname(__FILE__) . "/print.php");
		}else{
			$backgroundColor = (isset($self->properties["background-color"]) && strlen($self->properties["background-color"]) === 6) ? $self->properties["background-color"] : "ffeaef";
			//隠しモードロゴ usage：/cmsインストールディレクトリ/app/webapp/{アプリケーションID}/logo/以下に画像ファイルを一枚配置する
			$logoDir = dirname(dirname(__FILE__)) . "/" . $self->applicationId . "/logo/";
			$fileName = null;
			if(file_exists($logoDir) && is_dir($logoDir)){
				foreach(glob($logoDir . "*") as $f){
    				if(is_file($f)){
						$fileName = trim(substr($f, strrpos($f, "/")), "/");
						break;
    				}
				}
			}

			if(isset($fileName)){
				$logoPath = self::getRoot() . "webapp/" . $self->applicationId . "/logo/" . $fileName;
			}else{
				$logoPath = self::getRoot() . "css/images/main/logo.png";
			}

			//サイドバーを折りたたむか？
			if($self->mode == "three"){
				$hideSideMenu = ( isset($_COOKIE["app-hide-side-menu"]) && $_COOKIE["app-hide-side-menu"] == "true" );
			}else{
				$hideSideMenu = null;
			}

			include_once(dirname(__FILE__) . "/" . $self->mode . ".php");
		}
	}

	/* 以下、設定系 */

	/**
	 * アプリケーションのメイン処理を追加します
	 */
	public static function main($func){
		$obj = CMSApplication::getInstance();
		$obj->appMain = $func;
	}

	/**
	 * JavaScriptを追加します
	 */
	public static function addScript($scriptUrl, $scriptBody = ""){
		$obj = CMSApplication::getInstance();

		if(strlen($scriptUrl) > 0){
			$obj->scripts[] = '<script type="text/javascript" src="'.htmlspecialchars($scriptUrl, ENT_QUOTES).'"></script>';
		}else if(strlen($scriptBody)){
			$obj->scripts[] = '<script type="text/javascript">'.$scriptBody.'</script>';
		}

	}

	/**
	 * CSSを追加します
	 */
	public static function addLink($cssUrl){
		$obj = CMSApplication::getInstance();

		if(strlen($cssUrl) > 0){
			$obj->links[] = '<link rel="stylesheet" href="'.htmlspecialchars($cssUrl, ENT_QUOTES).'" />';
		}
	}


	/**
	 * タブメニューを設定します
	 *
	 * @example CMSApplication::setTabs(array(
	 * 	arrray(
	 * 		"label" => "タブ１",
	 * 		"href" => SOY2PageController::createLink("hoge.fuga"),
	 * 		"onclick" => "alert('test');"
	 * 	)
	 * ));
	 */
    public static function setTabs($tabs){
    	$obj = CMSApplication::getInstance();
    	$obj->tabs = $tabs;
    }

    /**
     * 有効なタブを設定します
     */
    public static function setActiveTab($id){
    	$obj = CMSApplication::getInstance();
    	$obj->activeTab = $id;
    }

    /**
	 * 上部メニューを設定します
	 *
	 * @example CMSApplication::setUpperMenu(array(
	 * 	arrray(
	 * 		"label" => "メニュー１",
	 * 		"href" => SOY2PageController::createLink("hoge.fuga"),
	 * 		"onclick" => "alert('test');"
	 * 	)
	 * ));
	 */
    public static function setUpperMenu($menus){
    	$obj = CMSApplication::getInstance();
    	$obj->menus = $menus;
    }

    /**
     * 出力のモードを指定します。
     */
    public static function setMode($mode){
    	$obj = CMSApplication::getInstance();
    	$obj->mode = $mode;
    }

    /**
     * タイトルを設定します。
     */
    public static function setTitle($title){
    	$obj = CMSApplication::getInstance();
    	$obj->title = $title;
    }

	/**
	 * バージョン番号を取得します。
	 */
	public static function getVersion(){
		$obj = CMSApplication::getInstance();
		return isset($obj->properties["version"]) && strlen($obj->properties["version"]) ? $obj->properties["version"] : "" ;
	}

    /**
     * common以下のファイルをimportします
     */
    public static function import($class,$extension = ".class.php"){
    	$old = SOY2::RootDir();
    	SOY2::RootDir(CMS_COMMON);
    	SOY2::import($class,$extension);
    	SOY2::RootDir($old);
    }

    /**
     * switch root
     */
    public static function switchRoot($to = null){
		$old = SOY2::RootDir();
		if($to){
			SOY2::RootDir($to);
		}else{
    		SOY2::RootDir(CMS_COMMON);
		}
    	return $old;
    }

    /**
     * switch dao
     */
    public static function switchDomain($to = null){
		$old = SOY2DAOConfig::DaoDir();

		if($to){
			SOY2DAOConfig::DaoDir($to);
			SOY2DAOConfig::EntityDir($to);
		}else{

			SOY2DAOConfig::DaoDir(CMS_COMMON . "domain/");
			SOY2DAOConfig::EntityDir(CMS_COMMON . "domain/");
		}

		return $old;
    }

    /* 以下、SOYCMSとの連携 */
    public static function page($page,$arguments = array()){
    	$self = CMSApplication::getInstance();

    	$self->arguments = $arguments;
		$self->root = SOY2PageController::createRelativeLink("./");

		if($self->appMain){
			call_user_func($self->appMain,$page);
		}
    }

    public static function isDirectLogin(){
     	//ログインしていない
		$self = CMSApplication::getInstance();
     	if(!self::checkLogin($self->applicationId)) return false;

     	$only_one = SOY2ActionSession::getUserSession()->getAttribute("hasOnlyOneRole");

     	return ($only_one == true);
    }

    //サイト側のデータベースを使っているかを調べる
    public static function checkUseSiteDb(){

    	$res = false;
    	$useSiteDb = false;

    	//SOY InquiryかSOY Mailのどちらかがサイト側のデータベースを使うことができる
    	if(APPLICATION_ID == "inquiry"){
    		$useSiteDb = (defined("SOYINQUIRY_USE_SITE_DB") && SOYINQUIRY_USE_SITE_DB);
    	}elseif(APPLICATION_ID == "mail"){
    		$useSiteDb = (defined("SOYMAIL_USE_SITE_DB") && SOYMAIL_USE_SITE_DB);
    	}

    	if($useSiteDb){
    		$session = SOY2ActionSession::getUserSession();
    		$site = $session->getAttribute("Site");

    		//サイトIDが取得できれば、ログインしていることになる
    		if(!is_null($site->getId())){
    			$res = true;
    		}
    	}

    	return $res;
    }

	//権限を持つサイトが一つのみ。SOY InquiryとSOY Mailはサイト側にデータベースを持つ設定
	public static function checkAuthWithSiteOnly(){
		static $res;
		if(is_null($res)){
			$res = false;
			$session = SOY2ActionSession::getUserSession();
			if($session->getAttribute("isdefault")){	//初期管理者の場合は強制的にtrue
				$res = true;
			}else{
				self::switchAdminMode();
				$userId = $session->getAttribute("userid");
				$app_res = SOY2Logic::createInstance("logic.admin.Application.ApplicationLogic")->getLoginableApplications($userId);

				//SOY Appの画面を開きつつ、SOY Appの権限が無い場合はサイト側にデータベースを持つ設定になっている
				$res = (count($app_res));

				self::switchAppMode();
			}
		}

		return $res;
	}

    //サイト側のデータベースを使っている時にどのサイトにログインしているか？を調べる
    public static function getLoginedSiteId(){
    	$uri = null;

    	$session = SOY2ActionSession::getUserSession();
    	$site = $session->getAttribute("Site");

    	SOY2::import("admin.Site");
    	$siteType = $site->getSiteType();

    	if(!is_null($site->getId())){
    		//CMS側のサイトの場合:SOY CMSのサイトは1で、単純にサイトIDを返す
	    	if($siteType == Site::TYPE_SOY_CMS){
	    		$uri = (int)$site->getId();

	    	//Shop側のサイトの場合:SOY Shopのサイトは2で、サイトIDを0にした後、GETの値でサイトを指定する
	    	}elseif($siteType == Site::TYPE_SOY_SHOP){
	    		$uri = "0?site_id=" . $site->getSiteId();
	    	}
    	}

    	return $uri;
    }

    /* 以下、内部仕様のメソッド */

    /**
     * ログインしているかどうかをチェックする
     */
    private static function checkLogin($appId){

    	//ログインしているかどうか
    	if(!UserInfoUtil::isLoggined()){
    		return false;
    	}

    	//ディフォルトユーザなら無条件にtrue
    	if(UserInfoUtil::isDefaultUser()){
    		return true;
    	}

		//App権限情報を取得
		$appAuth = UserInfoUtil::getAppAuth();

    	//セッションに情報が無い場合、DBから取得する
    	if(is_null($appAuth)){
			CMSApplication::import("config.db.".SOYCMS_DB_TYPE, ".php");

			SOY2DAOConfig::Dsn(ADMIN_DB_DSN);
			SOY2DAOConfig::user(ADMIN_DB_USER);
			SOY2DAOConfig::pass(ADMIN_DB_PASS);

			//必要クラスの読み込み
			CMSApplication::import("domain.admin.AppRoleDAO");
			CMSApplication::import("domain.admin.AppRole");

			//App権限をセッションに保持
			UserInfoUtil::loginApp();

			//App権限情報を再度取得
			$appAuth = UserInfoUtil::getAppAuth();
    	}

    	return (in_array($appId,$appAuth));

    }

    /**
     * SOY CMS Adminモード
     */
    public static function switchAdminMode(){

		//Root
		self::RestoreSOY2RootDir();
		SOY2::RootDir(CMS_COMMON);

		//DAO, Entity
		self::switchDomain(SOY2::RootDir() . "domain/");

		//DB
		include_once(CMS_COMMON . "config/db/".SOYCMS_DB_TYPE.".php");
		SOY2DAOConfig::Dsn(ADMIN_DB_DSN);
		SOY2DAOConfig::user(ADMIN_DB_USER);
		SOY2DAOConfig::pass(ADMIN_DB_PASS);
    }

    /**
     * SOY Appモード
     */
    public static function switchAppMode(){

    	//Root
		self::RestoreSOY2RootDir();

    	//DAO, Entity
    	self::switchDomain(SOY2::RootDir() . "domain/");

    	//DB
		if(SOYCMS_DB_TYPE == "sqlite"){
			SOY2DAOConfig::Dsn("sqlite:" . CMS_COMMON . "db/".APPLICATION_ID.".db");
		}else{
			include_once(CMS_COMMON . "config/db/".SOYCMS_DB_TYPE.".php");
			SOY2DAOConfig::Dsn(ADMIN_DB_DSN);
			SOY2DAOConfig::user(ADMIN_DB_USER);
			SOY2DAOConfig::pass(ADMIN_DB_PASS);
		}
    }

	/**
	 * SOY2::RootDir()を保持し復元する
	 */
    private static function RestoreSOY2RootDir(){
    	static $rootDir;
    	if(!$rootDir) $rootDir = SOY2::RootDir();
    	SOY2::RootDir($rootDir);
    }

    /**
     * アプリ管理者かどうか
     */
    public static function checkAuthSuperUser(){
		self::import("domain.admin.AppRole");
    	$sessioin = SOY2ActionSession::getUserSession();

    	//ディフォルトユーザなら無条件にtrue
    	if($sessioin->getAttribute("isdefault")){
    		return true;
    	}

    	//権限レベル
    	$level = self::getAppAuthLevel();

    	//App管理者
    	if(AppRole::APP_SUPER_USER == $level){
    		return true;
    	}

    	//App操作者
    	if(AppRole::APP_USER == $level){
    		return true;
    	}

    	return false;
    }

    /**
     * CMSの初期管理者かどうか
     */
    public static function checkAuthDefaultUser(){
    	$session = SOY2ActionSession::getUserSession();

    	//ディフォルトユーザなら無条件にtrue
    	if($session->getAttribute("isdefault")){
    		return true;
    	}

    	return false;
    }

    /**
     * ログイン中のSOY Appの権限レベルを取得
     */
    public static function getAppAuthLevel(){
    	$session = SOY2ActionSession::getUserSession();

    	//ディフォルトユーザの場合は無条件でSUPER USERにする
    	if($session->getAttribute("isdefault")){
    		return 1;	//SUPER USER
    	}

     	//権限レベル
    	$level = $session->getAttribute("app_auth_level");
    	if(isset($level[APPLICATION_ID])){
    		return $level[APPLICATION_ID];
    	}

    }

    /**
     * ログイン中のSOY Appの権限設定を取得
     */
    public static function getAppAuthConfig(){
    	$session = SOY2ActionSession::getUserSession();
     	//権限レベル
    	$level = $session->getAttribute("app_auth_config");
    	if(isset($level[APPLICATION_ID])){
    		return $level[APPLICATION_ID];
    	}

    }

    /**
     * ログイン中のSOY Appの権限の一覧を取得
     * @return Array AppRole userId@index
     */
    public static function getAppAuthList(){
    	self::switchAdminMode();

    	$appRoleDAO = SOY2DAOFactory::create("admin.AppRoleDAO");

    	try{
    		$auths = $appRoleDAO->getByAppId(APPLICATION_ID);
    	}catch(Exception $e){
			$auths = array();
    	}

    	self::switchAppMode();
    	return $auths;
    }

    /**
     * ログイン中のSOY Appの権限を持つユーザの一覧を取得
     * @return Array Administrator userId@index
     */
    public static function getAppUserList(){

    	$auths = self::getAppAuthList();
		$auths = array_keys($auths);

    	self::switchAdminMode();

    	$adminDAO = SOY2DAOFactory::create("admin.AdministratorDAO");

		$users = array();
		foreach($auths as $auth){

	    	try{
	    		$users[$auth] = $adminDAO->getById($auth);
	    	}catch(Exception $e){

	    	}
		}

    	self::switchAppMode();
    	return $users;
    }
}
