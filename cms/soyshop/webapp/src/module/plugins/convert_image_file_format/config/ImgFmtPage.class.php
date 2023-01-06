<?php

class ImgFmtPage extends WebPage{

	private $configObj;

	function __construct(){
		SOY2::import("module.plugins.convert_image_file_format.util.ImgFmtUtil");
	}

	function doPost(){
		if(soy2_check_token()){
			// モード webp or avif
			$fmtMode = (isset($_POST["format_mode"])) ? $_POST["format_mode"] : ImgFmtUtil::FMT_TYPE_EMPTY;
			ImgFmtUtil::saveImageFormat($fmtMode);

			$cnfs = (isset($_POST["display_config"]) && is_array($_POST["display_config"])) ? $_POST["display_config"] : array();
			ImgFmtUtil::savePageDisplayConfig($cnfs);

			// appページ
			foreach(array(ImgFmtUtil::APP_TYPE_CART, ImgFmtUtil::APP_TYPE_MYPAGE) as $idx){
				$on = (isset($_POST["app_display_config"][$idx])) ? (int)$_POST["app_display_config"][$idx] : ImgFmtUtil::OFF;
				ImgFmtUtil::saveAppPageDisplayConfig($idx, $on);
			}
			
			$this->configObj->redirect("updated");
		}
	}

	function execute(){
		parent::__construct();

		$this->addForm("form");

		$isWebp = function_exists("imagewebp");
		$isAvif = function_exists("imageavif");
		DisplayPlugin::toggle("unusable", (!$isWebp && !$isAvif));
		DisplayPlugin::toggle("usable", $isWebp || $isAvif);

		DisplayPlugin::toggle("webp", $isWebp);
		DisplayPlugin::toggle("avif", $isAvif);

		$fmt = ImgFmtUtil::getImageFormat();
		
		$this->addCheckBox("webp", array(
			"name" => "format_mode",
			"value" => "webp",
			"selected" => ($fmt == ImgFmtUtil::FMT_TYPE_WEBP || $fmt == ImgFmtUtil::FMT_TYPE_EMPTY),
			"label" => "WebP"
		));
		
		$this->addCheckBox("avif", array(
			"name" => "format_mode",
			"value" => "avif",
			"selected" => ($fmt == ImgFmtUtil::FMT_TYPE_AVIF),
			"label" => "AVIF"
		));

		SOY2::import("module.plugins.x_html_cache.component.PageListComponent");
		$this->createAdd("page_list", "PageListComponent", array(
			"list" => soyshop_get_page_list(),
			"displayConfig" => ImgFmtUtil::getPageDisplayConfig()
		));

		//アプリケーションページ
		foreach(array("cart", "mypage") as $idx => $typ){
			$this->addCheckBox($typ."_page", array(
				"name" => "app_display_config[".$idx."]",
				"value" => 1,
				"selected" => ImgFmtUtil::getAppPageDisplayConfig($idx),
				"label" => ($typ == "cart") ? "カートページ" : "マイページ"
			));
		}
	}

	function setConfigObj($configObj){
		$this->configObj = $configObj;
	}
}
