<?php

class reCAPTCHAConfigPage extends WebPage {

	private $pluginObj;

	function __construct(){
		//サイトマップから持ってくる
		SOY2::imports("site_include.plugin.sitemap.component.*");
	}

	function doPost(){
		if(soy2_check_token()){
			if(isset($_POST["config_per_page"])){
				$this->pluginObj->config_per_page = $_POST["config_per_page"];
			}
			if(isset($_POST["config_per_blog"])){
				$this->pluginObj->config_per_blog = $_POST["config_per_blog"];
			}

			$this->pluginObj->setSiteKey(trim($_POST["site_key"]));
			$this->pluginObj->setSecretKey(trim($_POST["secret_key"]));

			CMSPlugin::savePluginConfig(reCAPTCHAv3Plugin::PLUGIN_ID,$this->pluginObj);
			CMSPlugin::redirectConfigPage();
		}
	}

	function execute(){
		parent::__construct();

		$this->addForm("form");

		$this->addInput("site_key", array(
			"name" => "site_key",
			"value" => $this->pluginObj->getSiteKey()
		));

		$this->addInput("secret_key", array(
			"name" => "secret_key",
			"value" => $this->pluginObj->getSecretKey()
		));

		SOY2::import('site_include.CMSPage');
		SOY2::import('site_include.CMSBlogPage');

		$this->createAdd("page_list","PageListComponent",array(
			"list"  => self::getPages(),
			"pluginObj" => $this->pluginObj
		));
	}

	private function getPages(){
    	$result = SOY2ActionFactory::createInstance("Page.PageListAction",array(
    		"offset" => 0,
    		"count"  => 1000,
    		"order"  => "cdate"
    	))->run();

    	$list = $result->getAttribute("PageList");// + $result->getAttribute("RemovedPageList");

    	return $list;

	}

	function setPluginObj($pluginObj){
		$this->pluginObj = $pluginObj;
	}
}
