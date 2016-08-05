<?php
/**
 * ページ表示
 * 公開側のコントローラ
 */
class SOYMock_PageApplication{

	private $page;
	private $serverConfig;

	function init(){
		CMSApplication::main(array($this, "main"));		
	}
	
	function prepare(){
		include_once(dirname(__FILE__) . "/config.php");
		$this->initDatabase();
	}
	
	function initDatabase(){
		//DBの初期化を行う。データベースを使用したい場合はコメントアウトを外してください。
		if(!file_exists(CMS_COMMON . "db/".APPLICATION_ID.".db")){
			$logic = SOY2Logic::createInstance("logic.InitLogic");
			$logic->init();
		}
	}

	function main($page){
		
		/** ここからはSOY Appのお約束の処理 **/
		
		$this->page = $page;
		
		//SOY2::RootDir()の書き換え
		$oldRooDir = SOY2::RootDir();
		$oldPagDir = SOY2HTMLConfig::PageDir();
		$oldCacheDir = SOY2HTMLConfig::CacheDir();
		$oldDaoDir = SOY2DAOConfig::DaoDir();
		$oldEntityDir = SOY2DAOConfig::EntityDir();
		$oldDsn = SOY2DAOConfig::Dsn();
		$oldUser = SOY2DAOConfig::user();
		$oldPass = SOY2DAOConfig::pass();
		
		//設定ファイルの読み込み
		$this->prepare();
		
		$arguments = CMSApplication::getArguments();
		
		/** ここまではSOY Appのお約束の処理 **/


		/*
		 * app:id="soymock"
		 * SOY CMSでAppページを作成して、<!-- app:id="soymock" -->を記述する
		 * アプリの表示箇所を示すタグを作成、SOYMock_ItemComponentクラスのexecuteメソッド内に具体的な内容を記載する
		 */
		$this->page->createAdd("soymock","SOYMock_ItemComponent",array(
			"application" => $this,
			"page" => $page,
			"soy2prefix" => "app"
		));
		
		
		/** ここからはSOY Appのお約束の処理 **/
				
		//元に戻す
		SOY2::RootDir($oldRooDir);
		SOY2HTMLConfig::PageDir($oldPagDir);
		SOY2HTMLConfig::CacheDir($oldCacheDir);
		SOY2DAOConfig::DaoDir($oldDaoDir);
		SOY2DAOConfig::EntityDir($oldEntityDir);
		SOY2DAOConfig::Dsn($oldDsn);
		SOY2DAOConfig::user($oldUser);
		SOY2DAOConfig::pass($oldPass);
	}
}

class SOYMock_ItemComponent extends SOYBodyComponentBase{
	
	private $page;
	private $application;
	
	function setPage($page){
		$this->page = $page;
	}
	
	function execute(){
		
		/*
		 * ここではアプリにさせたい処理を記載する
		 */
	
		//<!-- cms:id="label" -->の作成。文字列に置換される
		$this->createAdd("label", "HTMLLabel", array(
			"soy2prefix" => "cms",
			"text" => "表示したい文字列"
		));	
	
		parent::execute();
	}

	function getApplication(){
		return $this->application;
	}
	
	function setApplication($application){
		$this->application = $application;
	}
}

$app = new SOYMock_PageApplication();
$app->init();
?>