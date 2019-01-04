<?php

class SitemapConfigFormPage extends WebPage{

	private $pluginObj;

	function __construct(){
		SOY2::imports("site_include.plugin.sitemap.component.*");
	}

	function doPost(){
		if(isset($_POST["config_per_page"])){
			$this->pluginObj->config_per_page = $_POST["config_per_page"];
		}
		if(isset($_POST["config_per_blog"])){
			$this->pluginObj->config_per_blog = $_POST["config_per_blog"];
		}

		if(isset($_POST["ssl_per_page"])){
			$this->pluginObj->ssl_per_page = $_POST["ssl_per_page"];
		}
		if(isset($_POST["ssl_per_blog"])){
			$this->pluginObj->ssl_per_blog = $_POST["ssl_per_blog"];
		}

		//URLの追加
		if(isset($_POST["Url"])){
			$newUrl = htmlspecialchars($_POST["Url"], ENT_QUOTES, "UTF-8");

			//すでに登録されていないか？調べる
			$existed = false;
			if(count($this->pluginObj->urls)){
				foreach($this->pluginObj->urls as $url){
					if(isset($url["url"]) && $url["url"] === $newUrl){
						$existed = true;
						break;
					}
				}
			}

			if(!$existed){
				$values = array();
				$values["url"] = $newUrl;
				$values["lastmod"] = time();
				$this->pluginObj->urls[] = $values;
			}
		}

		CMSUtil::notifyUpdate();
		CMSPlugin::savePluginConfig(SitemapPlugin::PLUGIN_ID,$this->pluginObj);
		CMSPlugin::redirectConfigPage();
	}

	function execute(){
		parent::__construct();

		//削除
		if(isset($_GET["remove"]) && is_numeric($_GET["remove"]) && soy2_check_token()){
			self::remove($_GET["remove"]);
		}

		//挿入するページの指定
		SOY2::import('site_include.CMSPage');
		SOY2::import('site_include.CMSBlogPage');
		//SOY2HTMLFactory::importWebPage("CMSBlogPage");

		$this->createAdd("page_list","PageListComponent",array(
			"list"  => self::getPages(),
			"pluginObj" => $this->pluginObj
		));

		$this->createAdd("ssl_list","SSLListComponent",array(
			"list"  => self::getPages(),
			"pluginObj" => $this->pluginObj
		));

		self::buildUrlForm();
	}

	private function remove($index){
    	if(isset($this->pluginObj->urls[$index])){
			unset($this->pluginObj->urls[$index]);
			$this->pluginObj->urls = array_values($this->pluginObj->urls);  //配列のインデックスを詰める
			CMSUtil::notifyUpdate();
			CMSPlugin::savePluginConfig(SitemapPlugin::PLUGIN_ID,$this->pluginObj);
			CMSPlugin::redirectConfigPage();
		}
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

	private function buildUrlForm(){
		$this->addForm("url_form");

		$this->addInput("url", array(
			"name" => "Url",
			"value" => "",
			"attr:placeholder" => UserInfoUtil::getSitePublishURL(),
			"attr:pattern" => "http(s)?://([\w-]+\.)+[\w-]+(/[\w- ./?%&=]*)?",
			"attr:required" => "required"
		));

		DisplayPlugin::toggle("is_urls", count($this->pluginObj->urls));

		$this->createAdd("url_list", "UrlListComponent", array(
			"list" => $this->pluginObj->urls
		));
	}

	function setPluginObj($pluginObj){
		$this->pluginObj = $pluginObj;
	}
}
