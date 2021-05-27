<?php

class SOYInquiryConnectorPlugin{

	const PLUGIN_ID = "soyinquiry_connector";

	private $siteId;
	private $pageId;
	private $uri;

	function getId(){
		return self::PLUGIN_ID;
	}

	function init(){
		CMSPlugin::addPluginMenu(self::PLUGIN_ID, array(
			"name" => "SOY Inquiry連携プラグイン",
			"description" => "SOY Inquiryと連携します",
			"author" => "齋藤毅",
			"url" => "https://saitodev.co",
			"mail" => "tsuyoshi@saitodev.co",
			"version" => "0.1"
		));
		CMSPlugin::addPluginConfigPage(self::PLUGIN_ID,array(
			$this,"config_page"
		));

		if(CMSPlugin::activeCheck(self::PLUGIN_ID)){
			CMSPlugin::setEvent('onEntryOutput', self::PLUGIN_ID, array($this, "onEntryOutput"));
		}
	}

	function onEntryOutput($arg){
		$entryId = $arg["entryId"];
		$htmlObj = $arg["SOY2HTMLObject"];

		$htmlObj->addLink("inquiry_link", array(
			"soy2prefix" => "cms",
			"link" => ($entryId > 0) ? self::_buildUrl($entryId) : ""
		));
	}

	private function _buildUrl($entryId){
		if(!is_numeric($this->siteId) || $this->siteId < 1) return "";
		if(!is_numeric($this->pageId) || $this->pageId < 1) return "";

		if(is_null($this->uri) || !strlen($this->uri)){
			$old = CMSUtil::switchDsn();
			try{
				$dsn = SOY2DAOFactory::create("admin.SiteDAO")->getById($this->siteId)->getDataSourceName();
			}catch(Exception $e){
				$dsn = null;
			}
			CMSUtil::resetDsn($old);
			if(is_null($dsn)) return array();
			
			$oldDsn = SOY2DAOConfig::dsn();
			SOY2DAOConfig::dsn($dsn);

			SOY2::import("domain.admin.Site");
			SOY2::import("util.UserInfoUtil");
			$this->uri = UserInfoUtil::getSitePublishURL();

			SOY2::import("site_include.plugin.soyinquiry_connector.util.SOYInquiryConnectorUtil");
			$list = SOYInquiryConnectorUtil::getInquiryPageList($this->siteId);
			$this->uri .= (isset($list[$this->pageId])) ? $list[$this->pageId] : "";
			unset($list);

			SOY2DAOConfig::dsn($oldDsn);
		}
		$uri = $this->uri;

		//すでにGETの値がある場合
		if(is_numeric(strpos($uri, "?"))){
			$uri .= "&entry_id=" . $entryId;
		//ない場合
		}else{
			$uri .= "?entry_id=" . $entryId;
		}

		//現在開いているサイトIDを付与
		$siteId = self::_getSiteId();
		if(is_numeric($siteId) && $siteId > 0){
			$uri .= "&site_id=" . $siteId;
		}


		//現在開いているページのIDを付与
		if(isset($_SERVER["SOYCMS_PAGE_ID"]) && is_numeric($_SERVER["SOYCMS_PAGE_ID"])){
			$uri .= "&page_id=" . $_SERVER["SOYCMS_PAGE_ID"];
		}

		return $uri;
	}

	private function _getSiteId(){
		static $siteId;
		if(is_null($siteId)){
			$siteAlias = trim(substr(_SITE_ROOT_, strrpos(_SITE_ROOT_, "/")), "/");

			$old = CMSUtil::switchDsn();
			try{
				$siteId = SOY2DAOFactory::create("admin.SiteDAO")->getBySiteId($siteAlias)->getId();
			}catch(Exception $e){
				$siteId = 0;
			}
			CMSUtil::resetDsn($old);
		}

		return $siteId;
	}


	/**
	 * 設定画面
	 */
	function config_page($message){
		SOY2::import("site_include.plugin.soyinquiry_connector.config.SOYInquiryConnectorConfigPage");
		$form = SOY2HTMLFactory::createInstance("SOYInquiryConnectorConfigPage");
		$form->setPluginObj($this);
		$form->execute();
		return $form->getObject();
	}

	function getSiteId(){
		return $this->siteId;
	}
	function setSiteId($siteId){
		$this->siteId = $siteId;
	}

	function getPageId(){
		return $this->pageId;
	}
	function setPageId($pageId){
		$this->pageId = $pageId;
	}

	public static function register(){
		$obj = CMSPlugin::loadPluginConfig(self::PLUGIN_ID);
		if(!$obj) $obj = new SOYInquiryConnectorPlugin();
		CMSPlugin::addPlugin(self::PLUGIN_ID, array($obj, "init"));
	}
}
SOYInquiryConnectorPlugin::register();
