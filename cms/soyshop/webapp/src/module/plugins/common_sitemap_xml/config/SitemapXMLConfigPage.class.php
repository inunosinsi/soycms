<?php

class SitemapXMLConfigPage extends WebPage{

  private $configObj;

  function __construct(){
		SOY2::import("module.plugins.common_sitemap_xml.util.SitemapXMLUtil");
    SOY2::import("module.plugins.common_sitemap_xml.component.UrlListComponent");
	}

	function doPost(){
    if(soy2_check_token()){
      if(isset($_POST["add"]) && strlen($_POST["new"])){
        $configs = SitemapXMLUtil::getConfig();
        $newUrl = htmlspecialchars($_POST["new"], ENT_QUOTES, "UTF-8");
        //すでに登録されていないか？調べる
        $existed = false;
        if(count($configs)){
          foreach($configs as $config){
            if(isset($config["url"]) && $config["url"] === $newUrl){
              $existed = true;
              break;
            }
          }
        }

        if(!$existed){
          $values = array();
          $values["url"] = $newUrl;
          $values["lastmod"] = time();
          $configs[] = $values;
          SitemapXMLUtil::saveConfig($configs);
          $this->configObj->redirect("updated");
        }
      }
    }

    $this->configObj->redirect("error");
	}

	function execute(){
		WebPage::__construct();

    if(isset($_GET["remove"]) && is_numeric($_GET["remove"]) && soy2_check_token()){
      self::remove($_GET["remove"]);
    }

    DisplayPlugin::toggle("error", isset($_GET["error"]));

    self::buildForm();
	}

  private function remove($index){
    $config = SitemapXMLUtil::getConfig();
    if(isset($config[$index])){
      unset($config[$index]);
      $config = array_values($config);  //配列のインデックスを詰める
      SitemapXMLUtil::saveConfig($config);
      $this->configObj->redirect("updated");
    }
  }

  private function buildForm(){
    $this->addForm("form");

    $this->createAdd("url_list", "UrlListComponent", array(
      "list" => SitemapXMLUtil::getConfig()
    ));
  }

	function setConfigObj($configObj) {
		$this->configObj = $configObj;
	}
}
