<?php

class GoogleAnalyticsConfigFormPage extends WebPage{

	private $config;

	function GoogleAnalyticsConfigFormPage(){
		SOY2DAOFactory::importEntity("SOYShop_DataSets");
		SOY2::import("module.plugins.parts_google_analytics.util.GoogleAnalyticsUtil");
	}

	function doPost(){

		if(isset($_POST["google_analytics"])){
			GoogleAnalyticsUtil::saveConfig($_POST["google_analytics"]);
			
			GoogleAnalyticsUtil::savePageDisplayConfig($_POST["display_config"]);
			$this->config->redirect("updated");
		}
	}

	function execute(){
		WebPage::WebPage();

		$code = GoogleAnalyticsUtil::getConfig();

		$this->addTextArea("tracking_code", array(
			"value" => $code["tracking_code"],
			"name"  => "google_analytics[tracking_code]"
		));

		//nameがinsert_to_headなのは歴史的経緯による
		$this->addCheckBox("insert_before_end_head", array(
			"value" => 2,
			"selected" => ($code["insert_to_head"] == 2),
			"name"  => "google_analytics[insert_to_head]",
			"label" => "</head>タグの直前に挿入する"
		));

		$this->addCheckBox("insert_to_head", array(
			"value" => 1,
			"selected" => ($code["insert_to_head"] == 1),
			"name"  => "google_analytics[insert_to_head]",
			"label" => "<body>タグの直後に挿入する"
		));

		$this->addCheckBox("insert_to_tail", array(
			"value" => 0,
			"selected" => ($code["insert_to_head"] == 0),
			"name"  => "google_analytics[insert_to_head]",
			"label" => "</body>タグの直前に挿入する"
		));
		
		include_once(dirname(dirname(__FILE__)) . "/component/PageListComponent.class.php");
		$this->createAdd("page_list", "PageListComponent", array(
			"list" => $this->getPageList(),
			"displayConfig" => GoogleAnalyticsUtil::getPageDisplayConfig()
		));

		$this->addModel("updated", array(
			"visible" => isset($_GET["updated"])
		));
	}
	
	function getPageList(){
		$pageDao = SOY2DAOFactory::create("site.SOYShop_PageDAO");
		try{
			$pages = $pageDao->get();
		}catch(Exception $e){
			return array();	
		}
		
		$list = array();
		foreach($pages as $page){
			if(is_null($page->getId())) continue;
			$list[$page->getId()] = $page->getName();
		}
		return $list;
	}

	function setConfigObj($obj) {
		$this->config = $obj;
	}
}
?>