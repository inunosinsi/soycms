<?php

class SOYInquiryConnectorPlugin{

	const PLUGIN_ID = "soyinquiry_connector";

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
		if(!is_numeric($this->pageId) || $this->pageId < 1) return "";

		if(is_null($this->uri) || !strlen($this->uri)){
			SOY2::import("domain.admin.Site");
			SOY2::import("util.UserInfoUtil");
			$this->uri = UserInfoUtil::getSitePublishURL();

			SOY2::import("site_include.plugin.soyinquiry_connector.util.SOYInquiryConnectorUtil");
			$list = SOYInquiryConnectorUtil::getInquiryPageList();
			$this->uri .= (isset($list[$this->pageId])) ? $list[$this->pageId] : "";
			unset($list);
		}
		$uri = $this->uri;

		//すでにGETの値がある場合
		if(is_numeric(strpos($uri, "?"))){
			$uri .= "&amp;entry_id=" . $entryId;
		//ない場合
		}else{
			$uri .= "?entry_id=" . $entryId;
		}
		
		return $uri;
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
