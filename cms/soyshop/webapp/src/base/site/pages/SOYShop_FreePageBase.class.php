<?php

class SOYShop_FreePageBase extends SOYShopPageBase{

	function build($args){
		$page = $this->getPageObject();
		if(!$page instanceof SOYShop_Page) throw new Exception("failed SOYShop_Page Object on SOYShop_FreePageBase");
		$obj = $page->getPageObject();

		$this->addLabel("free_title", array(
			"text" => $obj->getTitle(),
			"soy2prefix" => SOYSHOP_SITE_PREFIX
		));

		$this->addLabel("free_content", array(
			"html" => $obj->getContent(),
			"soy2prefix" => SOYSHOP_SITE_PREFIX
		));

		/**
		 * SOY Appを複数呼び出す
		 * 記述例
		 * <!-- cms:id="apps" cms:app="inquiry mail catalog" /-->
		 */
		$this->createAdd("apps", "SOYShop_AppContainer", array(
			"page" => $this,
			"soy2prefix" => "cms"
		));
	}
}

/**
 * SOY Appを呼び出す
 */
class SOYShop_AppContainer extends SOY2HTML{

	private $page;

	function setPage($page){
		$this->page = $page;
	}

	function getObject(){
		$applicationIds = $this->getApplicationIds();

		if(count($applicationIds)){
			//定数の作成
			if(!defined("CMS_APPLICATION_ROOT_DIR")){
				define("CMS_APPLICATION_ROOT_DIR", dirname(dirname(dirname(SOY2::RootDir()))) . "/app/");
			}
			if(!defined("CMS_COMMON")){
				define("CMS_COMMON", dirname(dirname(dirname(SOY2::RootDir()))) . "/common/");
			}

			//読み込み
			include_once(CMS_COMMON . "soycms.config.php");
			include_once(CMS_APPLICATION_ROOT_DIR . "webapp/base/CMSApplication.class.php");

			//MySQL版の場合はCMS本体のmysqlの設定ファイルを確認する必要がある
			if(SOYCMS_DB_TYPE == "mysql" && !defined("ADMIN_DB_DSN")){
				$mysqlFilePath = CMS_COMMON . "config/db/mysql.php";
				if(file_exists($mysqlFilePath)) include_once($mysqlFilePath);
			}

			//保険
			$oldRooDir = SOY2::RootDir();
			$oldPagDir = SOY2HTMLConfig::PageDir();
			$oldCacheDir = SOY2HTMLConfig::CacheDir();
			$oldDaoDir = SOY2DAOConfig::DaoDir();
			$oldEntityDir = SOY2DAOConfig::EntityDir();
			$oldDsn = SOY2DAOConfig::Dsn();
			$oldUser = SOY2DAOConfig::user();
			$oldPass = SOY2DAOConfig::pass();

			try{

				foreach($applicationIds as $applicationId){
					$pagePHP = CMS_APPLICATION_ROOT_DIR . "webapp/" . $applicationId . "/page.php";
					if(strlen($applicationId) && file_exists($pagePHP)){
						include_once($pagePHP);

						//実行
						CMSApplication::page($this->page, $this->page->getArguments());
					}
				}

			}catch(Exception $e){
				SOY2::RootDir($oldRooDir);
				SOY2HTMLConfig::PageDir($oldPagDir);
				SOY2HTMLConfig::CacheDir($oldCacheDir);
				SOY2DAOConfig::DaoDir($oldDaoDir);
				SOY2DAOConfig::EntityDir($oldEntityDir);
				SOY2DAOConfig::Dsn($oldDsn);
				SOY2DAOConfig::user($oldUser);
				SOY2DAOConfig::pass($oldPass);

	    		throw $e;
	    	}
		}

		return $this->getInnerHTML();

	}

	private function getApplicationIds(){
		$appIds = trim($this->getAttribute("cms:app"));

		$applicationIds = array($appIds);
		if(strpos($appIds, " ") !== false){
			$applicationIds = explode(" ", $appIds);
		}elseif(strpos($appIds, ";") !== false){
			$applicationIds = explode(";", $appIds);
		}elseif(strpos($appIds, ":") !== false){
			$applicationIds = explode(":", $appIds);
		}elseif(strpos($appIds, ",") !== false){
			$applicationIds = explode(",", $appIds);
		}
		return $applicationIds;
	}
}
