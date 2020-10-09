<?php

class DetailPage extends SOYShopWebPage{

	private $id;

	function doPost(){

		//通過しない
		// if(soy2_check_token() && isset($_POST["Site"])){
		// 	$site = SOY2::cast(ShopUtil::getSiteById($this->id), $_POST["Site"]);
		//
		// 	/**
		// 	 * shopディレクトリにあるsqlite.dbのショップ名の書き換え
		// 	 * shop.dbの書き換え
		// 	 * cms.dbの書き換え
		// 	 */
		// 	if(SOY2Logic::createInstance("logic.ShopLogic")->updateShopSite($site)){
		// 		CMSApplication::jump("Site.Detail." . $this->id . "?updated");
		// 	}else{
		// 		CMSApplication::jump("Site.Detail." . $this->id . "?error");
		// 	}
		// }
	}

    function __construct($args) {
    	$this->id = (isset($args[0])) ? (int)$args[0] : null;

    	parent::__construct();

		foreach(array("success", "detach", "created", "updated", "error") as $t){
			DisplayPlugin::toggle($t, isset($_GET[$t]));
		}

    	self::_buildForm();
    }

    private function _buildForm(){
    	$site = ShopUtil::getSiteById($this->id);

    	$this->addForm("form");

    	$this->addLabel("site_id", array(
    		"text" => $site->getSiteId()
    	));

    	$this->addInput("site_name", array(
    		"name" => "Site[name]",
    		"value" => $site->getSiteName(),
    		"style" => "width:95%",
			"readonly" => true
    	));

    	$this->addLabel("site_db", array(
    		"text" => ($site->getIsMysql())? "MySQL": "SQLite"
    	));

    	$logic = SOY2Logic::createInstance("logic.RootLogic");
		$checkHasRootSite = $logic->checkHasRootSite();	//ルート設定されたサイトがあるか？
		$checkIsRootSite = ($checkHasRootSite) ? $logic->checkIsRootSite($site->getId()) : false;


		$rootUrl = self::_createRootLink($site);
    	$this->addLink("site_root_link", array(
    		"link" => $rootUrl,
			"text" => $rootUrl,
			"target" => "_blank",
			"visible" => ($checkIsRootSite)
    	));

		DisplayPlugin::toggle("is_root", $checkIsRootSite);
		$this->addLink("site_url", array(
			"link" => $site->getUrl(),
			"text" => $site->getUrl(),
			"target" => "_blank"
		));

		$this->addInput("site_url_input", array(
			"name" => "Site[url]",
			"value" => $site->getUrl(),
			"id" => "site_url",
			"style" => "width:95%"
		));

    	include_once(str_replace("common", "soyshop", CMS_COMMON) . "webapp/conf/shop/" . $site->getSiteId() . ".conf.php");
    	$this->addLabel("default_url", array(
    		"text" => SOYSHOP_SITE_URL,
    		"id" => "default_url"
    	));

		$this->addActionLink("controller_link", array(
			"link" => SOY2PageController::createLink(APPLICATION_ID . ".Site.CreateController." . $site->getId()),
			"text" => "再生成を実行"
		));

		$this->addModel("config_root", array(
			"visible" => (!$checkHasRootSite)
		));

		$this->addActionLink("set_root_link", array(
			"link" => SOY2PageController::createLink(APPLICATION_ID . ".Site.SiteRoot." . $site->getId()),
			"text" => "ルート設定を行う",
			"visible" => (!$checkIsRootSite),
			"onclick"=> 'return confirm("ドメインルートに設定します。よろしいですか？");',
		));
		$this->addActionLink("detach_root_link", array(
			"link" => SOY2PageController::createLink(APPLICATION_ID . ".Site.SiteRootDetach." . $site->getId()),
			"text" => "ルート設定を解除する",
			"visible" => ($checkIsRootSite),
			"onclick"=> 'return confirm("ドメインルートの設定を解除します。");',
		));

		$this->addLink("detail_link", array(
			"link" => SOY2PageController::createLink(APPLICATION_ID . ".Config.Detail." . $site->getId()),
			"text" => "詳細"
		));
    }

    private function _createRootLink(Site $site){
		$url = rtrim($site->getUrl(), "/");
    	return str_replace($site->getSiteId(), "", $url);
    }
}
