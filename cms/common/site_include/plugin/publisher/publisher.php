<?php

PublisherPlugin::register();

class PublisherPlugin{
	
	const PLUGIN_ID = "publisher"; 
	
	
	function getId(){
		return self::PLUGIN_ID;	
	}
	
	function init(){
		CMSPlugin::addPluginMenu(self::PLUGIN_ID,array(
			"name"=>"静的化プラグイン",
			"description"=>"",
			"author"=>"齋藤毅",
			"url"=>"http://saitodev.co",
			"mail"=>"tsuyoshi@saitodev.co",
			"version"=>"0.1"
		));
//		CMSPlugin::addPluginConfigPage(self::PLUGIN_ID,array(
//			$this,"config_page"	
//		));
		
		if(CMSPlugin::activeCheck(self::PLUGIN_ID)){
			CMSPlugin::setEvent('onOutput',self::PLUGIN_ID, array($this,"onOutput"), array("filter"=>"all"));
			
			CMSPlugin::setEvent('onEntryUpdate', self::PLUGIN_ID, array($this, "onEntryUpdate"));
			CMSPlugin::setEvent('onEntryCreate', self::PLUGIN_ID, array($this, "onEntryUpdate"));
		}
	}
	
	function onOutput($arg){
		$html = &$arg["html"];
		
		//トップページである
		if(!strlen($arg["page"]->getUri())){
			
			if($arg["page"]->getPageType() == 200){
				//PATH_INFOがある場合はトップではないとみなす
				/**
				 * @ToDo もっときれいな書き方を検討する
				 */
				if(isset($_SERVER["PATH_INFO"])) return $html;
			}
			
			//ルート直下
			if(file_exists($_SERVER["DOCUMENT_ROOT"] . "/index.php") && !file_exists($_SERVER["DOCUMENT_ROOT"] . "/index.html")){
				file_put_contents($_SERVER["DOCUMENT_ROOT"] . "/index.html", $html);
			}else{
				if(!file_exists(_SITE_ROOT_ . "/index.html")){
					file_put_contents(_SITE_ROOT_ . "/index.html", $html);
				}
			}
		}
		
		return $html;
	}
	
	function onEntryUpdate($arg){
		
		//記事を更新した時にルート直下のindex.htmlを削除する
		if(file_exists($_SERVER["DOCUMENT_ROOT"] . "/index.html")){
			unlink($_SERVER["DOCUMENT_ROOT"] . "/index.html");
		}else{
			if(file_exists(UserInfoUtil::getSiteDirectory(true) . "index.html")){
				unlink(UserInfoUtil::getSiteDirectory(true) . "index.html");
			}
		}
	}
	
//	function config_page(){
//
//		include_once(dirname(__FILE__) . "/config/SitemapConfigFormPage.class.php");
//		$form = SOY2HTMLFactory::createInstance("SitemapConfigFormPage");
//		$form->setPluginObj($this);
//		$form->execute();
//		return $form->getObject();
//	}
	
	public static function register(){
		
		$obj = CMSPlugin::loadPluginConfig(self::PLUGIN_ID);
		if(!$obj){
			$obj = new PublisherPlugin();
		}
			
		CMSPlugin::addPlugin(self::PLUGIN_ID, array($obj, "init"));
	}
}
?>