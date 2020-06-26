<?php

class IndexPage extends WebPage{

	function doPost(){

		if(soy2_check_token() && isset($_POST["Config"])){
			$config["siteId"] = (int)$_POST["Config"]["siteId"];

			$dao = SOY2DAOFactory::create("SOYMail_SOYShopConnectorDAO");

			$obj = new SOYMail_SOYShopConnector();
			$obj->setConfig($config);

			$dao->update($obj);

			SOY2PageController::jump("mail.Connect?updated");
		}
	}

	function __construct(){
		//SUPER USER以外には表示させない
    	if(CMSApplication::getAppAuthLevel() != 1)CMSApplication::jump("");

		parent::__construct();

		DisplayPlugin::toggle("updated", isset($_GET["updated"]));
		DisplayPlugin::toggle("check_version", !self::checkVersion());
		DisplayPlugin::toggle("edit", self::checkVersion());


		$this->addForm("form");

		$config = self::getMailConfig();
		$this->addSelect("sites",array(
			"name" => "Config[siteId]",
			"options" => self::getSOYShopSiteList(),
			"selected" => (isset($config["siteId"])) ? $config["siteId"] : false
		));
	}

	private function getMailConfig(){
		try{
			return SOY2DAOFactory::create("SOYMail_SOYShopConnectorDAO")->get()->getConfig();
		}catch(Exception $e){
			return array();
		}
	}

	private function getSOYShopSiteList(){
		$old = SOYMailUtil::switchConfig();

		$sites = array();
		if(self::checkVersion()){
			if(file_exists(SOY2::RootDir()."domain/SOYShop_SiteDAO.class.php")){
				try{
					//SOY Shopがインストールされていない可能性がある
					$sites = SOY2DAOFactory::create("SOYShop_SiteDAO")->get();
				}catch(Exception $e){
					$sites = array();
				}
			}
		}

		SOYMailUtil::resetConfig($old);
		if(!count($sites)) return array();

		$list = array();
		foreach($sites as $site){
			$list[$site->getId()] = $site->getName();
		}

		return $list;
	}

	/**
	 * SOY Shopのバージョンを調べる
	 * 1.8.0以降ならばtrueを返す
	 */
	private function checkVersion(){
		static $res;
		if(isset($res) && is_bool($res)) return $res;
		$res = false;

		$text = file_get_contents(dirname(SOY2::RootDir())."/application.ini");
		preg_match('/version = \"(.*)\"/',$text,$tmp);

		$version = $tmp[1];
		if($version === "SOYSHOP_VERSION"){
			$res = true;
		}else{
			$majorVersion = self::convertNumber(substr($version,0,strpos($version,".")));
			$minorVersion = self::convertNumber(substr($version,strpos($version,"."),strrpos($version,".")));

			$compareVersion = $majorVersion * 10 + $minorVersion;

			//バージョンが1.8.0以降であることを確認する
			$res = ($compareVersion > 17) ? true : false;
		}

		return $res;
	}

	private function convertNumber($int){
		return (int)str_replace(".","",$int);
	}
}
