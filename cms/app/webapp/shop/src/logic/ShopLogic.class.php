<?php

class ShopLogic extends SOY2LogicBase{

	private $siteDao;
	private $siteRoleDao;
	private $appRoleDao;

	/** データの登録周り **/

	function updateShopSite(Site $site){

		//$site = $this->getSite($shopSite->getSiteId());

		//SOY2::RootDir()の書き換え
		$old = ShopUtil::switchConfig();
		ShopUtil::setShopSiteDsn($site);

		if($site->getIsDomainRoot() && strpos(SOYSHOP_SITE_URL, $site->getUrl()) !== false){
			$publishUrl = ShopUtil::getSiteUrl($site);
		}else{
			$publishUrl = $site->getUrl();
		}

		//管理画面からURLのフォームを非表示しても良い様にしておく
		$siteUrl = (isset($_POST["Site"]["url"])) ? $_POST["Site"]["url"] : $publishUrl;

		try{
			$config = SOYShop_ShopConfig::load();
			$config->setShopName($site->getSiteName());
			$config->setSiteUrl($publishUrl);
			SOYShop_ShopConfig::save($config, $siteUrl);
			$res = true;
		}catch(Exception $e){
			$res = false;
		}

		ShopUtil::resetConfig($old);

		return $res;
	}

	/** サイトの削除 **/

	function remove(Site $site){
		$old = ShopUtil::switchConfig();
		ShopUtil::setCMSDsn();

		if(!$this->siteDao) $this->siteDao = SOY2DAOFactory::create("admin.SiteDAO");
		if(!$this->siteRoleDao) $this->siteRoleDao = SOY2DAOFactory::create("admin.SiteRoleDAO");

		$webappDir = ShopUtil::setShopSiteConfig($site);

		try{
			$this->siteDao->delete($site->getId());
			$this->siteRoleDao->deleteBySiteId($site->getId());
			unlink($webappDir . "conf/shop/" . SOYSHOP_ID . ".conf.php");
			unlink($webappDir . "conf/shop/" . SOYSHOP_ID . ".admin.conf.php");
			self::_removeDirectory(SOYSHOP_SITE_DIRECTORY);
			$res = true;
		}catch(Exception $e){
			$res = false;
		}

		ShopUtil::resetConfig($old);

		return $res;
	}

	private function _removeDirectory($dir){
		if ($handle = opendir("$dir")){
			while (false !== ($item = readdir($handle))){
				if($item != "." && $item != ".."){
					if(is_dir("$dir/$item")){
						self::_removeDirectory("$dir/$item");
	   				}else{
		 				unlink("$dir/$item");
					}
				}
			}
			closedir($handle);
			rmdir($dir);
		}
	}
}
