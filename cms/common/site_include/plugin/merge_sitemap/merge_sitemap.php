<?php
MergeSitemapPlugin::register();

class MergeSitemapPlugin{

	const PLUGIN_ID = "merge_sitemap";

	private $urls = array();

	function getId(){
		return self::PLUGIN_ID;
	}

	function init(){
		CMSPlugin::addPluginMenu(self::PLUGIN_ID,array(
			"name"=>"サイトマップ結合プラグイン",
			"description"=>"複数のサイトマップxmlを統合して、一枚の静的なXMLファイルを生成します。定期的に新しいXMLが生成されて上書きします。",
			"author"=>"齋藤毅",
			"url"=>"http://saitodev.co",
			"mail"=>"tsuyoshi@saitodev.co",
			"version"=>"0.3"
		));
		CMSPlugin::addPluginConfigPage(self::PLUGIN_ID,array(
			$this,"config_page"
		));

		if(CMSPlugin::activeCheck(self::PLUGIN_ID)){
			CMSPlugin::setEvent("onSiteAccess",$this->getId(),array($this,"onSiteAccess"));
		}
	}

	function config_page(){

		SOY2::import("site_include.plugin.merge_sitemap.config.MergeSitemapConfigFormPage");
		$form = SOY2HTMLFactory::createInstance("MergeSitemapConfigFormPage");
		$form->setPluginObj($this);
		$form->execute();
		return $form->getObject();
	}

	function onSiteAccess($args){

		//設定がなければ動作しない
		if(!isset($this->urls) || !strlen(trim($this->urls[0]))) return;

		//URLの末尾に.xmlがあるページでは動作しない
		if(strpos($_SERVER["REQUEST_URI"], ".xml")) return;

		$logic = SOY2Logic::createInstance("site_include.plugin." . self::PLUGIN_ID . ".logic.MergeSitemapLogic");
		$xmlPath = $logic->getMergeXMLFilePath();

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
			$logic->createMergeMap($this->urls);
		}
	}

	function getUrls(){
		return $this->urls;
	}

	function setUrls($urls){
		$this->urls = $urls;
	}

	public static function register(){

		$obj = CMSPlugin::loadPluginConfig(self::PLUGIN_ID);
		if(!$obj){
			$obj = new MergeSitemapPlugin();
		}

		CMSPlugin::addPlugin(self::PLUGIN_ID, array($obj, "init"));
	}
}
