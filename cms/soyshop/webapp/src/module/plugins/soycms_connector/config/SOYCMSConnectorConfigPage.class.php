<?php

class SOYCMSConnectorConfigPage extends WebPage{

  private $configObj;

  function __construct(){
    SOY2::import("module.plugins.soycms_connector.util.SOYCMSConnectorUtil");
    SOY2::import("util.SOYAppUtil");
  }

  function doPost(){
    if(soy2_check_token() && isset($_POST["Config"])){
      SOYCMSConnectorUtil::saveConfig($_POST["Config"]);
      $this->configObj->redirect("updated");
    }
    $this->configObj->redirect("error");
  }

  function execute(){
    WebPage::__construct();

    $config = SOYCMSConnectorUtil::getConfig();
    
    $this->addForm("form");

    $this->addSelect("site_list", array(
      "name" => "Config[siteId]",
      "options" => self::getSiteList(),
      "selected" => $config["siteId"]
    ));
  }

  private function getSiteList(){
    $old = array();

		$old["root"] = SOY2::RootDir();
		$old["dao"] = SOY2DAOConfig::DaoDir();
		$old["entity"] = SOY2DAOConfig::EntityDir();
		$old["dsn"] = SOY2DAOConfig::Dsn();
		$old["user"] = SOY2DAOConfig::user();
		$old["pass"] = SOY2DAOConfig::pass();

		if(!defined("CMS_COMMON")){
			$common = str_replace("/soyshop/", "/common/", SOYSHOP_ROOT);
			define ("CMS_COMMON", $common);
		}
		$entity = CMS_COMMON . "domain/";

		SOY2::RootDir(CMS_COMMON);
		SOY2DAOConfig::DaoDir($entity);
		SOY2DAOConfig::EntityDir($entity);

		//MySQL版
		if(file_exists(dirname(SOYSHOP_ROOT) . "/common/config/db/mysql.php")){
			include_once(dirname(SOYSHOP_ROOT) . "/common/config/db/mysql.php");

		//SQLite版
		}else{
			include_once(dirname(SOYSHOP_ROOT) . "/common/config/db/sqlite.php");
		}

		SOY2DAOConfig::Dsn(ADMIN_DB_DSN);
		SOY2DAOConfig::user(ADMIN_DB_USER);
		SOY2DAOConfig::pass(ADMIN_DB_PASS);

    $dao = SOY2DAOFactory::create("admin.SiteDAO");
		try{
			$sites = $dao->getBySiteType(Site::TYPE_SOY_CMS);
		}catch(Exception $e){
			$sites = array();
		}

    SOY2::RootDir($old["root"]);
		SOY2DAOConfig::DaoDir($old["dao"]);
		SOY2DAOConfig::EntityDir($old["entity"]);
		SOY2DAOConfig::Dsn($old["dsn"]);
		SOY2DAOConfig::user($old["user"]);
		SOY2DAOConfig::pass($old["pass"]);

    if(!count($sites)) return array();

    $list = array();
    foreach($sites as $site){
      $list[$site->getSiteId()] = $site->getSiteName();
    }

    return $list;
  }

  function setConfigObj($configObj){
    $this->configObj = $configObj;
  }
}
