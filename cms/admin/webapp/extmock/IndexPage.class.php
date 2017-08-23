<?php
SOY2::import("domain.admin.Site");
class IndexPage extends CMSWebPageBase{

	function doPost(){

    	if(soy2_check_token()){

			if(isset($_POST["file_db_update"])){

				SOY2::import("util.CMSFileManager");

				CMSFileManager::deleteAll();

				set_time_limit(0);

				$sites = $this->getSiteList();
				foreach($sites as $site){
					CMSFileManager::setSiteInformation($site->getId(), $site->getUrl(), $site->getPath());
					CMSFileManager::insertAll($site->getPath());
				}
				$this->jump("?file_db_updated");
				exit;

			}

			if(isset($_POST["cache_clear"])){
				set_time_limit(0);

				$root = dirname(SOY2::RootDir());
				CMSUtil::unlinkAllIn($root . "/admin/cache/");
				CMSUtil::unlinkAllIn($root . "/soycms/cache/");
				CMSUtil::unlinkAllIn($root . "/app/cache/", true);

				$sites = $this->getSiteList();
				foreach($sites as $site){
					CMSUtil::unlinkAllIn($site->getPath() . ".cache/", true);
				}

				$this->jump("?cache_cleared");
				exit;
			}

			$this->jump("");
    	}
	}

	function IndexPage($arg){
		WebPage::WebPage();

		/*
		 * データベースのバージョンチェック
		 * ここまででDataSetsを呼び出していないこと ← そのうち破綻する気がする
		 * @TODO 初期管理者以外ではバージョンアップを促す文言を出すとか
		 */
		$this->run("Database.CheckVersionAction");

		//ユーザに割り当てられたサイト/Appが１つのときは、そのサイトにログイン(redirect)するようにする。
		$this->run("SiteRole.DefaultLoginAction");

		$this->addModel("file_db_massage", array(
			"visible" => (isset($_GET["file_db_updated"]))
		));

		$this->addModel("cache_clear_massage", array(
			"visible" => (isset($_GET["cache_cleared"]))
		));


		//ファイルDB更新、キャッシュの削除
		$this->addForm("file_form");
		$this->addForm("cache_form");

		//現在のユーザーがログイン可能なサイトのみを表示する
		$loginableSiteList = $this->getLoginableSiteList();
		$this->createAdd("list", "SiteList", array(
			"list" => $loginableSiteList
		));

		$this->addModel("no_site",array(
			"visible" => (count($loginableSiteList) < 1)
		));

		if(!UserInfoUtil::isDefaultUser()){
			DisplayPlugin::hide("only_default_user");
		}

		$this->addLink("create_link", array(
			"link"=>SOY2PageController::createLink("Site.Create")
		));
		
		$this->addLink("addAdministrator",
			array("link"=>SOY2PageController::createLink("Administrator.Create")
		));

		//アプリケーション
		$applications = $this->getLoginiableApplicationLists();
		$this->createAdd("application_list", "ApplicationList", array(
			"list" => $applications
		));

		$this->addModel("application_list_wrapper", array(
			"visible" => (count($applications) > 0)
		));

		$this->addModel("allow_php", array(
			"visible" => (defined("SOYCMS_ALLOW_PHP_SCRIPT") && SOYCMS_ALLOW_PHP_SCRIPT)
		));
	}

	/**
	 * サイト一覧
	 */
	function getSiteList(){
		$SiteLogic = SOY2Logic::createInstance("logic.admin.Site.SiteLogic");
		return $SiteLogic->getSiteList();
	}

	/**
	 * 現在のユーザIDからログイン可能なサイトオブジェクトのリストを取得する
	 */
	function getLoginableSiteList(){
		$SiteLogic = SOY2Logic::createInstance("logic.admin.Site.SiteLogic");
		return $SiteLogic->getSiteByUserId(UserInfoUtil::getUserId());
	}

	/**
	 * 2008-07-24 ログイン可能なアプリケーションを読み込む
	 */
	function getLoginiableApplicationLists(){
		$appLogic = SOY2Logic::createInstance("logic.admin.Application.ApplicationLogic");
		if(UserInfoUtil::isDefaultUser()){
			return $appLogic->getApplications();
		}else{
			return $appLogic->getLoginableApplications(UserInfoUtil::getUserId());
		}
	}
}

class SiteList extends HTMLList{

	function replaceTooLongHost($url){


		$array = parse_url($url);


		$host = $array["host"];
		if(isset($array["port"]))$host .=   ":" . $array["port"];

		if(strlen($host) > 30){
			$host = mb_strimwidth($host, 0, 30, "...");
		}

		$url = $array["scheme"] . "://" . $host . $array["path"];

		return $url;

	}

	protected function populateItem($entity){

		$siteName = $entity->getSiteName();
		if($entity->getIsDomainRoot()){
			$siteName = "*" . $siteName;
		}

		$this->addLabel("site_name" ,array(
			"text" => $siteName
		));

		/**
		 * ログイン後の転送先（$_GET["r"]）があれば再度$_GET["r"]に入れておく
		 */
		$param = array();
		if(isset($_GET["r"]) && strlen($_GET["r"]) && strpos($_GET["r"],"/soycms/")) $param["r"] = $_GET["r"];
		$this->addLink("login_link", array(
			"link" => $entity->getLoginLink($param)
		));

		$this->addLink("site_link", array(
			"link" => $entity->getUrl(),
			"text" => $this->replaceTooLongHost($entity->getUrl())
		));

		$rootLink = UserInfoUtil::getSiteURLBySiteId("");
		$this->addLink("domain_root_site_url", array(
			"link" => $rootLink,
			"text" => $this->replaceTooLongHost($rootLink),
			"visible" => $entity->getIsDomainRoot()
		));

		$this->addLink("auth_link", array(
			"link" => SOY2PageController::createLink("Site.SiteRole." . $entity->getId())
		));

		$this->addLink("root_site_link" ,array(
			"link" => SOY2PageController::createLink("Site.SiteRoot.".$entity->getId())
		));

		$this->addLink("remove_link",array(
			"link" => SOY2PageController::createLink("Site.Remove.".$entity->getId()),
			"onclick" => 'javascript:return confirm("' . CMSMessageManager::get("SOYCMS_CONFIRM_DELETE") . '");'
		));

	}

}


class ApplicationList extends HTMLList{
	protected function populateItem($entity, $key){
		$this->addLabel("name", array(
			"text" => $entity["title"]
		));

		/**
		 * ログイン後の転送先（$_GET["r"]）があれば再度$_GET["r"]に入れておく
		 */
		$param = array();
		if(isset($_GET["r"]) && strlen($_GET["r"]) && strpos($_GET["r"], "/app/index.php/" . $key)) $param["r"] = $_GET["r"];
		$this->addLink("login_link", array(
			"link" => SOY2PageController::createRelativeLink("../app/index.php/" . $key) . ( count($param) ? "?" . http_build_query($param) : "" )
		));

		$this->addLabel("description", array(
			"text" => $entity["description"]
		));

		$this->addLabel("version", array(
			"text" => (isset($entity["version"])) ? "ver. " . $entity["version"] : "",
			"visible" => (isset($entity["version"]))
		));
	}
}
?>