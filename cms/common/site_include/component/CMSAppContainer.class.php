<?php

class CMSAppContainer extends SOY2HTML{

	//出力はApplicationに任せるので何もしない
	const SOY_TYPE = SOY2HTML::SKIP_BODY;

	private $page;
	private $config = array();

	function setPage($page){
		$this->page = $page;
	}

	function getObject(){
		$applicationIds = $this->getApplicationIds();

		if(count($applicationIds)){
			//定数の作成
			if(!defined("CMS_APPLICATION_ROOT_DIR")){
				define("CMS_APPLICATION_ROOT_DIR", dirname(SOY2::RootDir()) . "/app/");
			}
			if(!defined("CMS_COMMON")){
				define("CMS_COMMON", SOY2::RootDir());
			}

			if(is_readable(CMS_APPLICATION_ROOT_DIR . "webapp/base/CMSApplication.class.php")){
				//読み込み
				include_once(CMS_APPLICATION_ROOT_DIR . "webapp/base/CMSApplication.class.php");

				//保険
				$this->saveSOY2Config();

				//実行
				try{
					foreach($applicationIds as $applicationId){
						$pagePHP = CMS_APPLICATION_ROOT_DIR . "webapp/" . $applicationId . "/page.php";
						if(strlen($applicationId) && file_exists($pagePHP)){
							include_once($pagePHP);
							CMSApplication::page($this->page,$this->page->arguments);
						}
					}
				}catch(Exception $e){
					//復帰
					$this->restoreSOY2Config();
					throw $e;
				}

				//復帰
				$this->restoreSOY2Config();
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

	private function saveSOY2Config(){
		$this->config = array(
			"RootDir" => SOY2::RootDir(),
			"PageDir" => SOY2HTMLConfig::PageDir(),
			"CacheDir" => SOY2HTMLConfig::CacheDir(),
			"DaoDir" => SOY2DAOConfig::DaoDir(),
			"EntityDir" => SOY2DAOConfig::EntityDir(),
			"Dsn" => SOY2DAOConfig::Dsn(),
			"User" => SOY2DAOConfig::user(),
			"Pass" => SOY2DAOConfig::pass(),
		);
	}

	private function restoreSOY2Config(){
		SOY2::RootDir($this->config["RootDir"]);
		SOY2HTMLConfig::PageDir($this->config["PageDir"]);
		SOY2HTMLConfig::CacheDir($this->config["CacheDir"]);
		SOY2DAOConfig::DaoDir($this->config["DaoDir"]);
		SOY2DAOConfig::EntityDir($this->config["EntityDir"]);
		SOY2DAOConfig::Dsn($this->config["Dsn"]);
		SOY2DAOConfig::user($this->config["User"]);
		SOY2DAOConfig::pass($this->config["Pass"]);
	}

}
