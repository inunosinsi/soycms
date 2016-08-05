<?php

class IndexPage extends WebPage{
	
	private $checkVersion=false;
	
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
		
		WebPage::WebPage();
		
		$this->addModel("updated",array(
			"visible" => (isset($_GET["updated"]))
		));
		
		$this->addForm("form");
		
		$dao = SOY2DAOFactory::create("SOYMail_SOYShopConnectorDAO");
		try{
			$config = $dao->get()->getConfig();
		}catch(Exception $e){
			$config = array();
		}
		
		$list = $this->getSOYShopSiteList();
		
		$this->addModel("check_version",array(
			"visible" => ($this->checkVersion === false)
		));
		
		$this->addSelect("sites",array(
			"name" => "Config[siteId]",
			"options" => $list,
			"selected" => (isset($config["siteId"])) ? $config["siteId"] : false
		));
		
	}

	function getSOYShopSiteList(){
		
		$old = SOYMailUtil::switchConfig();
		
		$this->checkVersion = $this->checkVersion();
		$sites = array();
		
		if($this->checkVersion === true){
			if(file_exists(SOY2::RootDir()."domain/SOYShop_SiteDAO.class.php")){
				$siteDao = SOY2DAOFactory::create("SOYShop_SiteDAO");
				try{
					//SOY Shopがインストールされていない可能性がある
					$sites = $siteDao->get();
				}catch(Exception $e){
					$sites = array();
				}
			}
		}
		
		SOYMailUtil::resetConfig($old);
		
		$list = array();
		
		if(count($sites) > 0){
			foreach($sites as $site){
				$list[$site->getId()] = $site->getName();
			}
		}
		
		return $list;
	}
	
	/**
	 * SOY Shopのバージョンを調べる
	 * 1.8.0以降ならばtrueを返す
	 */
	function checkVersion(){
		$res = false;
		
		$text = file_get_contents(dirname(SOY2::RootDir())."/application.ini");
		preg_match('/version = \"(.*)\"/',$text,$tmp);
		
		$version = $tmp[1];
		if($version === "SOYSHOP_VERSION"){
			$res = true;
		}else{
			$majorVersion = $this->convertNumber(substr($version,0,strpos($version,".")));
			$minorVersion = $this->convertNumber(substr($version,strpos($version,"."),strrpos($version,".")));
			
			$compareVersion = $majorVersion * 10 + $minorVersion;
			
			//バージョンが1.8.0以降であることを確認する
			$res = ($compareVersion > 17) ? true : false;
		}
		
		return $res;
	}
	
	function convertNumber($int){
		return (int)str_replace(".","",$int);
	}
}

?>