<?php

class RoleLogic extends SOY2LogicBase {

	function getAccounts($type="site",$siteId=null){
		$users = self::_getUsers();

		$accounts = array();
		foreach($users as $user){
			$value["id"] = $user->getId();
			$value["userId"] = $user->getUserId();
			$value["isDefaultUser"] = $user->getIsDefaultUser();
			$value["site_role"] = ($type=="site") ? $this->getSiteRole($user->getId(),$siteId) : null;
			$value["app_role"] = $this->getAppRole($user->getId());
			$accounts[] = (object)$value;
		}

		return $accounts;
	}

	function getAppRole($userId){
		$appId = "shop";

		$old = ShopUtil::switchConfig();
		ShopUtil::setCMSDsn();
		try{
			$appRole = SOY2DAOFactory::create("admin.AppRoleDAO")->getRole($appId,$userId);
		}catch(exception $e){
			$appRole = new AppRole();
		}
		ShopUtil::resetConfig($old);

		return $appRole->getAppRole();
	}

	function getSiteRole($userId,$siteId){

		$old = ShopUtil::switchConfig();
		ShopUtil::setCMSDsn();
		try{
			$siteRole = SOY2DAOFactory::create("admin.SiteRoleDAO")->getSiteRole($siteId, $userId);
		}catch(exception $e){
			$siteRole = new SiteRole();
		}
		ShopUtil::resetConfig($old);

		return $siteRole->getIsLimitUser();
	}

	//ショップ用に特別に配列を準備
	function getSiteRoleArray(Site $site){
		$old = ShopUtil::switchConfig();
		ShopUtil::setShopSiteDsn($site);

		$roles = array(
			"0" => "権限なし",
			"1" => "一般管理者",
			"2" => "受注管理者",
			"3" => "管理制限者",
			"10" => "商品管理のみ",
			"11" => "商品管理 + CSV"
		);

		// @ToDo ショッピングモール運営プラグインを有効にしている時のみ
		SOY2::import("util.SOYShopPluginUtil");
		if(SOYShopPluginUtil::checkIsActive("shopping_mall")){
			$roles[20] = "出店者";	//モール形式のショップサイトを運営した際に利用する→専用の各種画面を用意
		}

		ShopUtil::resetConfig($old);
		return $roles;
	}

	function getAppRoleArray(){
		$old = ShopUtil::switchConfig();
		ShopUtil::setCMSDsn();
		$array = AppRole::getRoleLists(true);
		ShopUtil::resetConfig($old);
		return $array;
	}

	function updateSiteRole($accounts, $siteId){
		$old = ShopUtil::switchConfig();
		ShopUtil::setCMSDsn();

		$dao = SOY2DAOFactory::create("admin.SiteRoleDAO");

		try{
			$dao->deleteBySiteId($siteId);
		}catch(Exception $e){
			$res = false;
		}

		foreach($accounts as $key => $value){
			if($value == 0) continue;
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
		$sites = ShopUtil::getSites();

		$old = ShopUtil::switchConfig();
		ShopUtil::setCMSDsn();

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
		ShopUtil::resetConfig($old);

		return $res;
	}

	function deleteChainSiteRole($userId){
		$sites = ShopUtil::getSites();

		$old = ShopUtil::switchConfig();
		ShopUtil::setCMSDsn();

		$siteRoleDao = SOY2DAOFactory::create("admin.SiteRoleDAO");
		foreach($sites as $site){
			try{
				$siteRoleDao->deleteSiteRole($userId,$site->getId());
			}catch(Exception $e){
				//
			}
		}
		ShopUtil::resetConfig($old);

		return true;
	}

	private function _getUsers(){
		//SOY2::RootDir()の書き換え
		$old = ShopUtil::switchConfig();
		ShopUtil::setCMSDsn();
		try{
			$users = SOY2DAOFactory::create("admin.AdministratorDAO")->get();
		}catch(Exception $e){
			$users = array();
		}
		ShopUtil::resetConfig($old);
		return $users;
	}
}
