<?php

class ItemReviewConfigFormPage extends WebPage{

    function __construct() {
    	SOY2DAOFactory::importEntity("SOYShop_DataSets");
    	SOY2::import("module.plugins.item_review.util.ItemReviewUtil");
    }

    function doPost(){

    	if(soy2_check_token() && isset($_POST["Config"])){
			ItemReviewUtil::saveConfig($_POST["Config"]);
    		$this->config->redirect("updated");
    	}
		$this->config->redirect("failed");
    }

    function execute(){

    	parent::__construct();

		DisplayPlugin::toggle("failed", isset($_GET["failed"]));

		$config = ItemReviewUtil::getConfig();

    	$this->addForm("form");

    	$this->addInput("code", array(
    		"name" => "Config[code]",
    		"value" => (isset($config["code"])) ? $config["code"] : ""
    	));

    	$this->addInput("nickname", array(
    		"name" => "Config[nickname]",
    		"value" => (isset($config["nickname"])) ? htmlspecialchars($config["nickname"],ENT_QUOTES,"UTF-8") : ""
    	));

    	$this->addCheckBox("login_mode", array(
    		"name" => "Config[login]",
    		"value" => 1,
    		"selected" => (isset($config["login"]) && $config["login"] == 1),
    		"label" => "ログインしている時だけ投稿を許可する"
    	));

    	$this->addCheckBox("publish_mode", array(
    		"name" => "Config[publish]",
    		"value" => 1,
    		"selected" => (isset($config["publish"]) && $config["publish"] == 1),
    		"label" => "投稿されたレビューを常に許可する"
    	));

    	$this->addCheckBox("edit_review", array(
    		"name" => "Config[edit]",
    		"value" => 1,
    		"selected" => (isset($config["edit"]) && $config["edit"] == 1),
    		"label" => "ユーザがマイページでレビューの編集を許可する"
    	));

    	DisplayPlugin::toggle("display_point_form", class_exists("SOYShopPluginUtil") && (SOYShopPluginUtil::checkIsActive("common_point_base")));
    	$this->addInput("point", array(
    		"name" => "Config[point]",
    		"value" => (isset($config["point"])) ? (int)$config["point"] : 0
    	));

    	$this->addModel("captcha_img_not_usable", array(
    		"visible" => (!function_exists("imagejpeg"))
    	));
    	$this->addInput("captcha", array(
    		"name" => "Config[captcha]",
    		"value" => (isset($config["captcha"])) ? $config["captcha"] : ""
    	));

    	$this->addModel("captcha_img_usable", array(
    		"visible" => (function_exists("imagejpeg"))
    	));
    	$this->addCheckBox("captcha_img", array(
    		"name" => "Config[captcha_img]",
    		"value" => 1,
    		"selected" => (isset($config["captcha_img"]) && $config["captcha_img"] == 1),
    		"label" => "画像認証による投稿制限を行う"
    	));

		$this->addCheckBox("edit_evaluation_star", array(
			"name" => "Config[evaluation_star]",
			"value" => 1,
			"selected" => (isset($config["evaluation_star"]) && $config["evaluation_star"] == 1),
			"label" => "マイページでのレビュー変更時の評価の変更を5つ星をクリックして選択する形式にする"
		));

		$this->addCheckBox("active_other_page", array(
			"name" => "Config[active_other_page]",
			"value" => 1,
			"selected" => (isset($config["active_other_page"]) && $config["active_other_page"] == 1),
			"label" => "フリーページに設けたレビュー一覧ページを使用する"
		));


		$this->addSelect("review_page_id", array(
			"name" => "Config[review_page_id]",
			"options" => self::_getPageList(),
			"selected" => (isset($config["review_page_id"]) && is_numeric($config["review_page_id"])) ? $config["review_page_id"] : null,
			"property" => "name"
		));

		$this->addInput("review_count", array(
			"name" => "Config[review_count]",
			"value" => (isset($config["review_count"]) && strlen($config["review_count"])) ? (int)$config["review_count"] : "",
			"style" => "width:80px;"
		));
    }

	private function _getPageList(){
		try{
			return SOY2DAOFactory::create("site.SOYShop_PageDAO")->getByType(SOYShop_Page::TYPE_FREE);
		}catch(Exception $e){
			return array();
		}
	}

    function setConfigObj($obj) {
		$this->config = $obj;
	}
}
