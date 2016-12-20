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
			"version"=>"0.2"
		));
		CMSPlugin::addPluginConfigPage(self::PLUGIN_ID,array(
			$this,"config_page"	
		));
		
		if(CMSPlugin::activeCheck(self::PLUGIN_ID)){
			CMSPlugin::setEvent("onSiteAccess", $this->getId(), array($this,"onSiteAccess"));
			
			//ページの作成、公開状況の変更、削除の場合はXMLファイルを再生成する
			CMSPlugin::setEvent("onPageCreate", $this->getId(), array($this, "onPageCreate"));
			CMSPlugin::setEvent("onPageUpdate", $this->getId(), array($this, "onPageUpdate"));
			CMSPlugin::setEvent("onPageRemove", $this->getId(), array($this, "onPageRemove"));
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

		//pageinfo.xmlがなければ作成する
		if(!file_exists($logic->getPageInfoXMLFilePath())){
			$logic->createPageInfoXml($this->urls);
		}
	}
	
	function onPageCreate($arg){
		if($arg["page"]->getIsPublished()){
			self::regenerateXmlFile();
		}
	}
	
	function onPageUpdate($arg){
		$new = $arg["new_page"];
		
		try{
			$old = SOY2DAOFactory::create("cms.PageDAO")->getById($new->getId());
		}catch(Exception $e){
			return;
		}
		
		//公開状態に変更があった時
		if((int)$new->getIsPublished() !== (int)$old->getIsPublished()){
			self::regenerateXmlFile();
		}
	}
	
	function onPageRemove($arg){
		self::regenerateXmlFile();
	}
	
	private function regenerateXmlFile(){
		$logic = SOY2Logic::createInstance("site_include.plugin." . self::PLUGIN_ID . ".logic.CreatePageInfoLogic");
		$xmlPath = $logic->getPageInfoXMLFilePath();
		
		//ページの削除時にxmlファイルを再生成する
		if(file_exists($xmlPath)) {
			unlink($xmlPath);
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