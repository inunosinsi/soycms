<?php
PageInfoXmlPlugin::register();

class PageInfoXmlPlugin{
	
	const PLUGIN_ID = "page_info_xml";
	
	private $urls = array();
	private $removeStrings = array();
		
	function getId(){
		return self::PLUGIN_ID;	
	}
	
	function init(){
		CMSPlugin::addPluginMenu(self::PLUGIN_ID,array(
			"name"=>"ページ情報XML作成プラグイン",
			"description"=>"サイトマップXMLからページの情報を取得して、1枚のxmlファイルとして生成する",
			"author"=>"齋藤毅",
			"url"=>"http://saitodev.co",
			"mail"=>"tsuyoshi@saitodev.co",
			"version"=>"0.1"
		));
		CMSPlugin::addPluginConfigPage(self::PLUGIN_ID,array(
			$this,"config_page"	
		));
		
		if(CMSPlugin::activeCheck(self::PLUGIN_ID)){
			CMSPlugin::setEvent("onSiteAccess",$this->getId(),array($this,"onSiteAccess"));
		}	
	}
	
	function config_page(){

		SOY2::import("site_include.plugin.page_info_xml.config.PageInfoXmlConfigFormPage");
		$form = SOY2HTMLFactory::createInstance("PageInfoXmlConfigFormPage");
		$form->setPluginObj($this);
		$form->execute();
		return $form->getObject();
	}
	
	function onSiteAccess($args){

		//設定がなければ動作しない
		if(!isset($this->urls) || !strlen(trim($this->urls[0]))) return;
		
		//URLの末尾に.xmlがあるページでは動作しない
		if(strpos($_SERVER["REQUEST_URI"], ".xml")) return;
		
		$logic = SOY2Logic::createInstance("site_include.plugin." . self::PLUGIN_ID . ".logic.CreatePageInfoLogic");
		$xmlPath = $logic->getPageInfoXMLFilePath();
		
		//xmlファイルを更新するか調べる
		if(file_exists($xmlPath)){
			$stat = stat($xmlPath);
			//更新時間を調べて、本日でない場合はファイルを削除する
			if($stat["mtime"] < strtotime("-1 day", time())){
				unlink($xmlPath);				
			}
		}
				
		//merge.xmlがなければ作成する
		if(!file_exists($xmlPath)){
			$logic->createPageInfoXml($this->urls);
		}

	}
	
	function getUrls(){
		return $this->urls;
	}
	
	function setUrls($urls){
		$this->urls = $urls;
	}
	
	function getRemoveStrings(){
		return $this->removeStrings;
	}
	function setRemoveStrings($removeStrings){
		$this->removeStrings = $removeStrings;
	}
	
	public static function register(){
		
		$obj = CMSPlugin::loadPluginConfig(self::PLUGIN_ID);
		if(!$obj){
			$obj = new PageInfoXmlPlugin();
		}
			
		CMSPlugin::addPlugin(self::PLUGIN_ID, array($obj, "init"));
	}
}
?>