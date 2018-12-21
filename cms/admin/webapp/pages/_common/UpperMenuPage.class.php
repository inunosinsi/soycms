<?php

class UpperMenuPage extends CMSWebPageBase{

	/**
	 * 有効なタブとURLのパターン
	 */
	private $activeTabRules = array(
		'Index' => 'top',
		'^Site'=> 'site',
		'^Administrator'=> 'administrator',
		'^Application' => 'application'
	);

	private $activeTab;

	function __construct() {
		parent::__construct();

		//リクエストされたパスからActiveなパスを取得
		$requestPath = SOY2PageController::getRequestPath();

		foreach($this->activeTabRules as $rule => $tab){
			if(preg_match("/" . $rule . "/", $requestPath)){
				$this->activeTab = $tab;
				break;
			}
		}
	}

	function execute(){
		$this->addLink("update_link", array(
			"link" => SOY2PageController::createLink("Administrator.Detail." . UserInfoUtil::getUserId())
		));

		$this->addLabel("adminname", array(
			"text" => UserInfoUtil::getUserName(),
			"width" => 30,
			"title" => UserInfoUtil::getUserName(),
		));

		$this->addModel("biglogo", array(
			"src" => CMSUtil::getLogoFile()
		));

		/* タブの状態を設定 */
		$this->createAdd("top", "HTMLModel", array(
			"class" => $this->getMenuStatus("top")
		));

		$this->createAdd("site", "HTMLModel", array(
			"class" => $this->getMenuStatus("site")
		));

		$this->createAdd("administrator", "HTMLModel", array(
			"class" => $this->getMenuStatus("administrator")
		));

		$this->createAdd("application", "HTMLModel", array(
			"class" => $this->getMenuStatus("application")
		));

		/* タブの表示 */
		$this->addModel("show_site", array(
				"visible" => $this->hasLoginableSite(),
		));
		$this->addModel("show_app", array(
				"visible" => $this->hasLoginiableApplication(),
		));
		$this->addModel("show_admin", array(
				"visible" => UserInfoUtil::isDefaultUser(),
		));

		/* サイドバーの表示・非表示 */
		$hideSideMenu = ( isset($_COOKIE["admin-hide-side-menu"]) && $_COOKIE["admin-hide-side-menu"] == "true" );
		$this->addModel("sidebar", array(
				"class" => $hideSideMenu ? "navbar-default sidebar sidebar-narrow" : "navbar-default sidebar",
		));
		$this->addModel("toggle-arrow", array(
				"class" => $hideSideMenu ? "fa fa-fw fa-angle-right" : "fa fa-fw fa-angle-left",
		));
	}

	/**
	 * メニューの状態を設定
	 */
	private function getMenuStatus($tabName){

		if($tabName == $this->activeTab){
			return "tab_active";
		}else{
			return "tab_inactive";
		}
	}

	/**
	 * 現在のユーザIDからログイン可能なサイトオブジェクトのリストを取得する
	 */
	private function hasLoginableSite(){
		if(UserInfoUtil::isDefaultUser()){
			return true;
		}else{
			$SiteLogic = SOY2Logic::createInstance("logic.admin.Site.SiteLogic");
			return count($SiteLogic->getSiteByUserId(UserInfoUtil::getUserId()));
		}
	}

	/**
	 * ログイン可能なアプリケーションを読み込む
	 */
	private function hasLoginiableApplication(){
		if(UserInfoUtil::isDefaultUser()){
			return true;
		}else{
			$appLogic = SOY2Logic::createInstance("logic.admin.Application.ApplicationLogic");
			return count($appLogic->getLoginableApplications(UserInfoUtil::getUserId()));
		}
	}

}
