<?php

class ShopLogic extends SOY2LogicBase{

	private $siteDao;
	private $siteRoleDao;
	private $appRoleDao;

	/** データの登録周り **/

	function updateShopSite($shopSite){

		$site = $this->getSite($shopSite->getSiteId());

		//SOY2::RootDir()の書き換え
		$old = ShopUtil::switchConfig();
		ShopUtil::setShopSiteDsn($shopSite);

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

	function updateAppRole($accounts){
		$old = ShopUtil::switchConfig();
		ShopUtil::setCMSDsn();

		$appId = "shop";

		$res = true;

		$dao = SOY2DAOFactory::create("admin.AppRoleDAO");

		//SOY Shopで生成されたサイトの権限を削除する
		try{
			$dao->deleteByAppId($appId);
		}catch(Exception $e){
		}

		foreach($accounts as $key => $value){
			//管理権限をapp操作者以上にしている場合、データベースに値をインサートする
			if($value!=0){
				$obj = new AppRole();
				$obj->setUserId($key);
				$obj->setAppId($appId);
				$obj->setAppRole($value);
				try{
					$dao->insert($obj);
				}catch(Exception $e){
				}
			}

			//@appの設定した権限に合わせて、サイトの権限を変更する
			switch($value){
				//app権限が権限なしの場合は、関連するサイトの管理権限もなしにする
				case 0:
					$res = $this->deleteChainSiteRole($key);
					break;
				//app権限をapp運営者にした場合、すべてのサイトの管理権限を一般管理者にする
				case 1:
					$res = $this->updateChainSiteRole($key);
					break;
				//app権限をapp操作者にした場合、何もしない
				case 2;
				default;
					$res = true;;
			}
		}

		ShopUtil::resetConfig($old);

		return $res;
	}


	function updateChainSiteRole($userId){
		$siteDao = SOY2DAOFactory::create("admin.SiteDAO");
		$sites = $siteDao->getBySiteType(Site::TYPE_SOY_SHOP);

		$siteRoleDao = SOY2DAOFactory::create("admin.SiteRoleDAO");

		foreach($sites as $site){
			$siteRoleDao->deleteSiteRole($userId,$site->getId());
			$obj = new SiteRole();
			$obj->setUserId($userId);
			$obj->setSiteId($site->getId());
			$obj->setIsLimitUser(1);
			try{
				$siteRoleDao->insert($obj);
				$res = true;
			}catch(Exception $e){
				$res = false;
			}
		}

		return $res;
	}

	function deleteChainSiteRole($userId){
		$siteDao = SOY2DAOFactory::create("admin.SiteDAO");
		$sites = $siteDao->getBySiteType(Site::TYPE_SOY_SHOP);

		$siteRoleDao = SOY2DAOFactory::create("admin.SiteRoleDAO");

		foreach($sites as $site){
			try{
				$siteRoleDao->deleteSiteRole($userId,$site->getId());
			}catch(Exception $e){
			}
		}

		return true;
	}

	function updateSiteRole($accounts,$siteId){
		$old = ShopUtil::switchConfig();
		ShopUtil::setCMSDsn();

		$dao = SOY2DAOFactory::create("admin.SiteRoleDAO");

		//shopのsiteIdからsiteのsiteIdに切り替える
		$siteId = $this->getSiteId($siteId);

		try{
			$dao->deleteBySiteId($siteId);
		}catch(Exception $e){
			$res = false;
		}

		foreach($accounts as $key => $value){
			if($value==0)continue;
			$obj = new SiteRole();
			$obj->setUserId($key);
			$obj->setSiteId($siteId);
			$obj->setIsLimitUser($value);
			try{
				$dao->insert($obj);
				$res = true;
			}catch(Exception $e){
				$res = false;
			}
		}

		ShopUtil::resetConfig($old);

		return $res;
	}


	/** 他のDBから値を取得 **/

	function getSite($siteId){
		$old = ShopUtil::switchConfig();
		ShopUtil::setCMSDsn();

		if(!$this->siteDao){
			$this->siteDao = SOY2DAOFactory::create("admin.SiteDAO");
		}
		try{
			$site = $this->siteDao->getById($this->getSiteId($siteId));
		}catch(Exception $e){
			$site = new Site();
		}

		ShopUtil::resetConfig($old);

		return $site;
	}

	function getAccounts($type="site",$siteId=null){

		//SOY2::RootDir()の書き換え
		$old = ShopUtil::switchConfig();
		ShopUtil::setCMSDsn();

		$dao = SOY2DAOFactory::create("admin.AdministratorDAO");
		try{
			$users = $dao->get();
		}catch(Exception $e){
			$users = array();
		}

		$accounts = array();
		foreach($users as $user){
			$value["id"] = $user->getId();
			$value["userId"] = $user->getUserId();
			$value["isDefaultUser"] = $user->getIsDefaultUser();
			$value["site_role"] = ($type=="site") ? $this->getSiteRole($user->getId(),$siteId) : null;
			$value["app_role"] = $this->getAppRole($user->getId());
			$accounts[] = (object)$value;
		}

		ShopUtil::resetConfig($old);

		return $accounts;

	}

	function getSiteRole($userId,$siteId){

		if(!$this->siteRoleDao){
			$this->siteRoleDao = SOY2DAOFactory::create("admin.SiteRoleDAO");
		}

		try{
			$siteRole = $this->siteRoleDao->getSiteRole($this->getSiteId($siteId),$userId);
		}catch(exception $e){
			$siteRole = new SiteRole();
		}

		return $siteRole->getIsLimitUser();
	}

	function getAppRole($userId){
		$appId = "shop";

		if(!$this->appRoleDao){
			$this->appRoleDao = SOY2DAOFactory::create("admin.AppRoleDAO");
		}

		try{
			$appRole = $this->appRoleDao->getRole($appId,$userId);
		}catch(exception $e){
			$appRole = new AppRole();
		}

		return $appRole->getAppRole();
	}

	/**
	 * shopのsiteIdからsiteのsiteIdに切り替える
	 */
	function getSiteId($siteId){
		if(!$this->siteDao){
			$this->siteDao = SOY2DAOFactory::create("admin.SiteDAO");
		}

		try{
			$site = $this->siteDao->getBySiteId($siteId);
		}catch(Exception $e){
			$site = new Site();
		}

		return $site->getId();
	}

	//ショップ用に特別に配列を準備
	function getSiteRoleArray(){
		return array(
			"0" => "権限なし",
			"1" => "一般管理者",
			"2" => "受注管理者",
			"3" => "管理制限者",
			"10" => "商品管理のみ"
		);
	}

	function getAppRoleArray(){
		$old = ShopUtil::switchConfig();
		ShopUtil::setCMSDsn();
		$array = AppRole::getRoleLists(true);
		ShopUtil::resetConfig($old);
		return $array;
	}


	/** フロントコントローラーの再生成周り **/

	function createSOYShopController($site){

		$old = ShopUtil::switchConfig();
		ShopUtil::setCMSDsn();

		//Shop関連の定数設定
		$webappDir = ShopUtil::setShopSiteConfig($site);

		SOY2::import("util.CMSUtil");

		$htaccessPath = SOYSHOP_SITE_DIRECTORY . ".htaccess";
	   	CMSUtil::createBackup($htaccessPath);
	   	$filename = SOYSHOP_SITE_DIRECTORY . "index.php";
	   	CMSUtil::createBackup($filename);

		ShopUtil::resetConfig($old);

		include($webappDir . "src/logic/init/InitLogic.class.php");
		$logic = new InitLogic();

		try{
			$logic->initController(true);
			return true;
		}catch(Exception $e){
			return false;
		}
	}

	/**
	 * ルート設定されたサイトがあるか
	 * @return boolean
	 */
	function checkHasRootSite(){
		$old = ShopUtil::switchConfig();
		ShopUtil::setCMSDsn();

		if(!$this->siteDao){
			$this->siteDao = SOY2DAOFactory::create("admin.SiteDAO");
		}
		try{
			$site = $this->siteDao->getDomainRootSite();
			$res = true;
		}catch(Exception $e){
			$res = false;
		}

		ShopUtil::resetConfig($old);

		return $res;
	}

	/**
	 * 自身のサイトがルート設定しているか
	 * @return boolean
	 */
	function checkIsRootSite($siteId){
		$old = ShopUtil::switchConfig();
		ShopUtil::setCMSDsn();

		$siteId = $this->getSiteId($siteId);

		if(!$this->siteDao){
			$this->siteDao = SOY2DAOFactory::create("admin.SiteDAO");
		}
		try{
			$site = $this->siteDao->getById($siteId);
		}catch(Exception $e){
			$site = new Site();
		}
		ShopUtil::resetConfig($old);

		return ($site->getIsDomainRoot()==1) ? true : false;
	}

	function updateDomainRootSite($site, $htaccess = null){
		$old = ShopUtil::switchConfig();
		ShopUtil::setCMSDsn();

		$dao = SOY2DAOFactory::create("admin.SiteDAO");
		$dao->begin();
		$dao->resetDomainRootSite();
		$dao->updateDomainRootSite($site->getId());
		$dao->commit();

		try{
			$logic = SOY2Logic::createInstance("logic.admin.Site.DomainRootSiteLogic");
			$logic->create();
			if($htaccess)$logic->createHtaccess($htaccess);
			$res = true;
		}catch(Exception $e){
			$res = false;
		}

		//キャッシュ削除
		SOY2::import("util.CMSUtil");
		$SiteLogic = SOY2Logic::createInstance("logic.admin.Site.SiteLogic");
		$sites = $SiteLogic->getSiteList();
		foreach($sites as $site){
			CMSUtil::unlinkAllIn($site->getPath().".cache/");
		}

		ShopUtil::resetConfig($old);

		return $res;
	}

	function detachDomainRootSite($id,$htaccess = null){
		$old = ShopUtil::switchConfig();
		ShopUtil::setCMSDsn();

		$dao = SOY2DAOFactory::create("admin.SiteDAO");
		$dao->begin();
		$dao->resetDomainRootSite();
		$dao->commit();

		try{
			$logic = SOY2Logic::createInstance("logic.admin.Site.DomainRootSiteLogic");
			$logic->delete();

			if($htaccess)$logic->createHtaccess($htaccess);
			$res = true;
		}catch(Exception $e){
			$res = false;
		}

		//キャッシュ削除
		SOY2::import("util.CMSUtil");
		$SiteLogic = SOY2Logic::createInstance("logic.admin.Site.SiteLogic");
		$site = $SiteLogic->getById($id);
		if($site){
			CMSUtil::unlinkAllIn($site->getPath().".cache/");
		}

		ShopUtil::resetConfig($old);

		return $res;
	}


	/** サイトの削除 **/

	function remove($site){
		$old = ShopUtil::switchConfig();
		ShopUtil::setCMSDsn();

		$siteId = $this->getSiteId($site->getSiteId());

		if(!$this->siteDao){
			$this->siteDao = SOY2DAOFactory::create("admin.SiteDAO");
		}

		if(!$this->siteRoleDao){
			$this->siteRoleDao = SOY2DAOFactory::create("admin.SiteRoleDAO");
		}

		$webappDir = ShopUtil::setShopSiteConfig($site);

		try{
			$this->siteDao->delete($siteId);
			$this->siteRoleDao->deleteBySiteId($siteId);
			unlink($webappDir . "conf/shop/" . SOYSHOP_ID . ".conf.php");
			unlink($webappDir . "conf/shop/" . SOYSHOP_ID . ".admin.conf.php");
			$this->removeDirectory(SOYSHOP_SITE_DIRECTORY);
			$res = true;
		}catch(Exception $e){
			$res = false;
		}

		ShopUtil::resetConfig($old);

		return $res;
	}

	function removeDirectory($dir){
		if ($handle = opendir("$dir")){
			while (false !== ($item = readdir($handle))){
				if($item != "." && $item != ".."){
					if(is_dir("$dir/$item")){
						self::removeDirectory("$dir/$item");
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
?>
