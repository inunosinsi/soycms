<?php

class GoogleAnalyticsConfigFormPage extends WebPage{

	private $config;

	function __construct(){
		SOY2DAOFactory::importEntity("SOYShop_DataSets");
		SOY2::import("module.plugins.parts_google_analytics.util.GoogleAnalyticsUtil");
	}

	function doPost(){

		if(soy2_check_token()){
			if(isset($_POST["google_analytics"])){
				GoogleAnalyticsUtil::saveConfig($_POST["google_analytics"]);

				$cnfs = (isset($_POST["display_config"])) ? $_POST["display_config"] : array();
				GoogleAnalyticsUtil::savePageDisplayConfig($cnfs);
				$this->config->redirect("updated");
			}
		}
	}

	function execute(){
		parent::__construct();

		$code = GoogleAnalyticsUtil::getConfig();

		$this->addForm("form");

		$this->addTextArea("tracking_code", array(
			"value" => $code["tracking_code"],
			"name"  => "google_analytics[tracking_code]"
		));

		//nameがinsert_to_headなのは歴史的経緯による
		$this->addCheckBox("insert_to_head", array(
			"value" => GoogleAnalyticsUtil::INSERT_INTO_THE_BEGINNING_OF_HEAD,
			"selected" => ($code["insert_to_head"] == GoogleAnalyticsUtil::INSERT_INTO_THE_BEGINNING_OF_HEAD),
			"name"  => "google_analytics[insert_to_head]",
			"label" => "<head>タグの直後に挿入する"
		));

		$this->addCheckBox("insert_before_end_head", array(
			"value" => GoogleAnalyticsUtil::INSERT_INTO_THE_END_OF_HEAD,
			"selected" => ($code["insert_to_head"] == GoogleAnalyticsUtil::INSERT_INTO_THE_END_OF_HEAD),
			"name"  => "google_analytics[insert_to_head]",
			"label" => "</head>タグの直前に挿入する"
		));

		$this->addCheckBox("insert_to_body", array(
			"value" => GoogleAnalyticsUtil::INSERT_INTO_THE_BEGINNING_OF_BODY,
			"selected" => ($code["insert_to_head"] == GoogleAnalyticsUtil::INSERT_INTO_THE_BEGINNING_OF_BODY),
			"name"  => "google_analytics[insert_to_head]",
			"label" => "<body>タグの直後に挿入する"
		));

		$this->addCheckBox("insert_to_tail", array(
			"value" => GoogleAnalyticsUtil::INSERT_INTO_THE_END_OF_BODY,
			"selected" => ($code["insert_to_head"] == GoogleAnalyticsUtil::INSERT_INTO_THE_END_OF_BODY),
			"name"  => "google_analytics[insert_to_head]",
			"label" => "</body>タグの直前に挿入する"
		));

		$this->addCheckBox("insert_to_the_end_of_body", array(
			"value" => GoogleAnalyticsUtil::INSERT_AFTER_THE_END_OF_BODY,
			"selected" => ($code["insert_to_head"] == GoogleAnalyticsUtil::INSERT_AFTER_THE_END_OF_BODY),
			"name"  => "google_analytics[insert_to_head]",
			"label" => "</body>タグの直後に挿入する"
		));

		$this->addCheckBox("insert_to_the_end_of_html", array(
			"value" => GoogleAnalyticsUtil::INSERT_INTO_THE_END_OF_HTML,
			"selected" => ($code["insert_to_head"] == GoogleAnalyticsUtil::INSERT_INTO_THE_END_OF_HTML),
			"name"  => "google_analytics[insert_to_head]",
			"label" => "HTMLの末尾に追加"
		));

		SOY2::import("component.Plugin.PageListComponent");
		$this->createAdd("page_list", "PageListComponent", array(
			"list" => soyshop_get_page_list(),
			"displayConfig" => GoogleAnalyticsUtil::getPageDisplayConfig()
		));
	}

	function setConfigObj($obj) {
		$this->config = $obj;
	}
}
