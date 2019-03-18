<?php
SOY2::import("domain.admin.Site");
class IndexPage extends CMSWebPageBase{

	function doPost(){

		if(soy2_check_token()){

			if(isset($_POST["cache_clear"])){
				set_time_limit(0);

				SOY2Logic::createInstance("logic.cache.CacheLogic")->clearCache();

				$this->addMessage("ADMIN_DELETE_CACHE");

				$this->jump("?cache_cleared");
				exit;
			}

			$this->jump("");
		}
	}

	function __construct($arg){
		//バージョンアップ時のキャッシュの自動削除
		$cacheLogic = SOY2Logic::createInstance("logic.cache.CacheLogic");
		if($cacheLogic->checkCacheVersion()) $cacheLogic->clearCache();

		parent::__construct();

		/*
		 * データベースのバージョンチェック
		 * ここまででDataSetsを呼び出していないこと ← そのうち破綻する気がする
		 * @TODO 初期管理者以外ではバージョンアップを促す文言を出すとか
		 */
		$this->run("Database.CheckVersionAction");
		$this->run("Administrator.CheckAdminVersionAction");

		//ユーザに割り当てられたサイト/Appが１つのときは、そのサイトにログイン(redirect)するようにする。
		$this->run("SiteRole.DefaultLoginAction");

		//ファイルDB更新、キャッシュの削除
		$this->addForm("file_form");
		$this->addForm("cache_form");

		//バージョン番号
		$this->addLabel("version",array(
				"text" => "version: ".SOYCMS_VERSION,
		));

		$this->addLabel("cms_name", array(
			"text" => CMSUtil::getCMSName()
		));

		//現在のユーザーがログイン可能なサイトのみを表示する
		$loginableSiteList = SOY2Logic::createInstance("logic.admin.Site.SiteLogic")->getLoginableSiteListByUserId(UserInfoUtil::getUserId());
		$this->createAdd("list", "_common.Site.SiteListComponent", array(
			"list" => $loginableSiteList
		));

		$this->addModel("no_site",array(
			"visible" => (count($loginableSiteList) < 1)
		));

		$this->addLink("create_link", array(
			"link"=>SOY2PageController::createLink("Site.Create")
		));

		$this->addLink("addAdministrator", array(
			"link"=>SOY2PageController::createLink("Administrator.Create")
		));

		//アプリケーション ログイン可能なアプリケーションを読み込む
		$applications = SOY2Logic::createInstance("logic.admin.Application.ApplicationLogic")->getLoginiableApplicationLists();
		$this->createAdd("application_list", "_common.Application.ApplicationListComponent", array(
			"list" => $applications
		));

		$this->addModel("application_list_wrapper", array(
			"visible" => (count($applications) > 0)
		));

		$this->addModel("allow_php", array(
			"visible" => (defined("SOYCMS_ALLOW_PHP_SCRIPT") && SOYCMS_ALLOW_PHP_SCRIPT)
		));
	}
}
