<?php
SOY2::import("domain.admin.Site");

class ConfirmPage extends CMSUpdatePageBase{

	function doPost(){

		/* バージョンアップ実行 */
		if(soy2_check_token()){
			$logic = SOY2LogicContainer::get("logic.db.UpdateDBLogic", array(
				"target" => "site"
			));
	
			$sites = $this->getSiteOnly();
			foreach($sites as $site){
				//切り替え
				SOY2DAOConfig::Dsn($site->getDataSourceName());
				//実行（バージョン番号も入る）
				$logic->update();
			}
			//戻す
			SOY2DAOConfig::Dsn(ADMIN_DB_DSN);
	
			SOY2PageController::jump("Site.Upgrade.Complete");
		}
	}

	function ConfirmPage(){

		//初期管理者のみ
		if(!UserInfoUtil::isDefaultUser()){
			SOY2PageController::jump("");
		}

		/*
		 * アップグレード対象のサイトだけ抽出
		 */
		$logic = SOY2LogicContainer::get("logic.db.UpdateDBLogic", array(
			"target" => "site"
		));

		$sites = $this->getSiteOnly();
		foreach($sites as $id => $site){
			//切り替え
			SOY2DAOConfig::Dsn($site->getDataSourceName());

			if(!$logic->hasUpdate()){
				unset($sites[$id]);
			}
		}
		//戻す
		SOY2DAOConfig::Dsn(ADMIN_DB_DSN);

		//なければサイト菅理へ
		if(count($sites)<1){
			SOY2PageController::jump("Site");
		}

		WebPage::WebPage();

		$this->createAdd("list", "SiteList", array(
			"list" => $sites
		));

		//実行フォーム・ボタン
		$this->addForm("upgrade_form");
	}

	/**
	 * 「サイト」のみを取得
	 */
	function getSiteOnly(){
		$SiteLogic = SOY2Logic::createInstance("logic.admin.Site.SiteLogic");
		return $SiteLogic->getSiteOnly();
	}
}

class SiteList extends HTMLList{

	function replaceTooLongHost($url){

		$array = parse_url($url);

		$host = $array["host"];
		if(isset($array["port"]))$host .=   ":" . $array["port"];

		if(strlen($host) > 30){
			$host = mb_strimwidth($host, 0, 30, "...");
		}

		$url = $array["scheme"] . "://" . $host . $array["path"];

		return $url;

	}

	protected function populateItem($entity){

		$siteName = $entity->getSiteName();
		if($entity->getIsDomainRoot()){
			$siteName = "*" . $siteName;
		}

		$this->addLabel("site_name", array(
			"text" => $siteName
		));

		$siteLink = (isset($_SERVER["HTTPS"]) ? "https://" : "http://") . $_SERVER['HTTP_HOST'] . '/' . $entity->getSiteId();
		$this->addLink("site_link", array(
			"link" => $entity->getUrl(),
			"text" => $this->replaceTooLongHost($entity->getUrl())
		));

		$rootLink = UserInfoUtil::getSiteURLBySiteId("");
		$this->addLink("domain_root_site_url", array(
			"link" => $rootLink,
			"text" => $this->replaceTooLongHost($rootLink),
			"visible" => $entity->getIsDomainRoot()
		));

		$this->addLink("site_detail_link", array(
			"link" => SOY2PageController::createLink("Site.Detail." . $entity->getId()),
			"visible" => ($entity->getSiteType() != Site::TYPE_SOY_SHOP)
		));
	}
}
?>