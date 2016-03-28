<?php

class DetailPage extends CMSUpdatePageBase{
	
	var $id;
	
	function doPost(){
		
		if(soy2_check_token() && isset($_POST["siteUrl"])){
			
			$siteDAO = SOY2DAOFactory::create("admin.SiteDAO");
			try{
				$site = $siteDAO->getById($this->id);
			}catch(Exception $e){
				/**
				 * @ToDo エラーメッセージを追加しないと
				 */
				$this->jump("Site.Detail." . $this->id);
			}
			
			$site->setUrl($_POST["siteUrl"]);
			
			try{
				$siteDAO->update($site);
				
				//ファイルDBの更新
				$this->updateFileDB();
				
				//キャッシュ削除
				CMSUtil::unlinkAllIn($site->getPath() . ".cache/");
				
				$this->addMessage("UPDATE_SUCCESS");
				
			}catch(Exception $e){
				//
			}
			
			//サイトURLを、サイト用DB SiteConfigに挿入
			$dsn = SOY2DAOConfig::Dsn();
			SOY2DAOConfig::Dsn($site->getDataSourceName());				
			$siteConfigDao = SOY2DAOFactory::create("cms.SiteConfigDAO");

			try{				
				$siteConfig = $siteConfigDao->get();
			}catch(Exception $e){
				//
			}
			
			//ルート設定していることを考慮して、設定にあったリンク用のサイトURLを出力してサイト側のsiteConfigに放り込む
			$defaultUrl = UserInfoUtil::getSiteURLBySiteId($site->getSiteId());
			if($site->getIsDomainRoot() && strpos($defaultUrl, $site->getUrl()) !== false){
				$siteUrl = UserInfoUtil::getSiteURLBySiteId("");
			}else{
				$siteUrl = $site->getUrl();
			}
			
			$siteConfig->setConfigValue("url", $siteUrl);
			try{
				$siteConfigDao->updateSiteConfig($siteConfig);
			}catch(Exception $e){
				//
			}			
			
			SOY2DAOConfig::Dsn($dsn);
		}
		
		$this->jump("Site.Detail." . $this->id);
	}
	
	function DetailPage($args) {
    		
    	if(!UserInfoUtil::isDefaultUser() || count($args) < 1){
    		//デフォルトユーザのみ変更可能
    		$this->jump("Site");
    		exit;
    	}
    	
    	$this->id = (isset($args[0])) ? $args[0] : null;
    	
    	WebPage::WebPage();
		
		$this->addForm("update_site_form");
		
		$SiteLogic = SOY2Logic::createInstance("logic.admin.Site.SiteLogic");
		$site = $SiteLogic->getById($this->id);
		
		if(!$site){
			$this->jump("Site");
		}
		
		$this->addLabel("site_name_title", array(
			"text" => $site->getSiteName()
		));
		
		$this->addLabel("site_name", array(
			"text" => $site->getSiteName()
		));
		
		$this->addLabel("site_id", array(
			"text" => $site->getSiteId()
		));
		
		//SOY CMS
		$this->addInput("site_url_soycms", array(
			"value" => $site->getUrl(),
			"name" => "siteUrl"
		));
		
		$this->addModel("display_soycms", array(
			"visible" => ($site->getSiteType() == Site::TYPE_SOY_CMS)
		));
		
		
		//SOY Shop
		$this->addInput("site_url_shop", array(
			"value" => $site->getUrl(),
			"name" => "siteUrl",
			"disabled" => "disabled"
		));

		$this->addModel("display_soyshop", array(
			"visible" => ($site->getSiteType() == Site::TYPE_SOY_SHOP)
		));

		$this->addLabel("default_url", array(
			"text" => UserInfoUtil::getSiteURLBySiteId($site->getSiteId())
		));
		
		$messages = CMSMessageManager::getMessages();
    	$this->addLabel("message", array(
			"text" => implode($messages),
			"visible" => (count($messages) > 0)
		));
		
		$messages = CMSMessageManager::getMessages();
		$errores = CMSMessageManager::getErrorMessages();
    	$this->addLabel("message", array(
			"text" => implode($messages),
			"visible" => (count($messages) > 0)
		));
		$this->addLabel("error", array(
			"text" => implode($errores),
			"visible" => (count($errores) > 0)
		));
		
		$this->addActionLink("regenerate_link", array(
			"link"    => SOY2PageController::createLink("Site.CreateController." . $this->id),
			"visible" => UserInfoUtil::isDefaultUser()
		));
	
		$this->addLink("edit_indexphp", array(
			"link"    => SOY2PageController::createLink("Site.EditController." . $this->id),
			"visible" => UserInfoUtil::isDefaultUser()
		));

		$this->addLink("edit_htaccess", array(
			"link"    => SOY2PageController::createLink("Site.EditHtaccess." . $this->id),
			"visible" => UserInfoUtil::isDefaultUser()
		));
	}
	
	function updateFileDB(){
		SOY2::import("util.CMSFileManager");
		
		CMSFileManager::deleteAll();
		
		$SiteLogic = SOY2Logic::createInstance("logic.admin.Site.SiteLogic");
		$sites = $SiteLogic->getSiteList();
		
		foreach($sites as $site){
			$url = (UserInfoUtil::getSiteURLBySiteId($site->getId()) != $site->getUrl() ) ? $site->getUrl() : null;
			CMSFileManager::setSiteInformation($site->getId(), $url, $site->getPath());
			CMSFileManager::insertAll($site->getPath());
		}
	}
}
?>