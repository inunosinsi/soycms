<?php

/**
 * データベースのバージョンを確認して、アップデートがあればUpgradeにリダイレクト
 */
class CheckVersionAction extends SOY2Action{

	function execute($req,$form,$res) {

		//初期管理者のみ
		if( ! UserInfoUtil::isDefaultUser()){
			return SOY2Action::FAILED;
		}

		/*
		 * adminのDBバージョンチェック
		 */
		if($this->hasUpdateForAdminDb()){
			SOY2PageController::jump("Upgrade.Admin");
		}

		/*
		 * 各サイトのDBバージョンチェック（ショップは除く）
		 */
		if($this->hasUpdateForAllSiteDb()){
			SOY2PageController::jump("Site.Upgrade");
		}

		//アップデートの必要なし
		return SOY2Action::SUCCESS;
	}

	/**
	 * adminのDBのバージョンを確認してアップデートがあるかどうか
	 */
	function hasUpdateForAdminDb(){
		$logic = SOY2LogicContainer::get("logic.db.UpdateDBLogic", array(
			"target" => "admin"
		));
		return $logic->hasUpdate();
	}

	/**
	 * 全siteのDBのバージョンを確認して、一つでもアップデートが必要なサイトがあるかどうか
	 */
	function hasUpdateForAllSiteDb(){
		$hasUpdate = false;
		$siteLogic = SOY2LogicContainer::get("logic.admin.Site.SiteLogic");
		$sites = $siteLogic->getSiteOnly();
		foreach($sites as $site){
			//DNS切り替え
			SOY2DAOConfig::Dsn($site->getDataSourceName());
			if($this->hasUpdateForSiteDb()){
				$hasUpdate = true;
				break;
			}
		}
		//戻す
		SOY2DAOConfig::Dsn(ADMIN_DB_DSN);
		return $hasUpdate;
	}

	/**
	 * siteのDBのバージョンを確認してアップデートがあるかどうか
	 */
	function hasUpdateForSiteDb(){
		$logic = SOY2LogicContainer::get("logic.db.UpdateDBLogic", array(
			"target" => "site"
		));
		return $logic->hasUpdate();
	}
}
