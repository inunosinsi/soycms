<?php

class ItemReviewConfigFormPage extends WebPage{

    function __construct() {
    	SOY2DAOFactory::importEntity("SOYShop_DataSets");
    	SOY2::import("module.plugins.item_review.util.ItemReviewUtil");
    }

    function doPost(){

    	if(soy2_check_token()){
    		$config = (isset($_POST["Config"])) ? $_POST["Config"] : array();

    		$config["code"] = mb_convert_kana($config["code"], "a");
    		$config["code"] = str_replace("#", "", $config["code"]);
    		if(!preg_match("/^([a-fA-F0-9])/", $config["code"])){
    			$config["code"] = "ffff00";
    		}

    		$config["login"] = (isset($config["login"])) ? 1 : null;
    		$config["publish"] = (isset($config["publish"])) ? 1 : null;
    		$config["edit"] = (isset($config["edit"])) ? 1 : null;
    		$config["captcha"] = (isset($config["captcha"])) ? trim($config["captcha"]) : "";
    		$config["captcha_img"] = (isset($config["captcha_img"])) ? 1 : null;
			$config["evaluation_star"] = (isset($config["evaluation_star"])) ? 1 : null;

    		$config["point"] = (isset($config["point"]) && is_numeric($config["point"])) ? (int)$config["point"] : 0;

    		SOYShop_DataSets::put("item_review.config", $config);
    		$this->config->redirect("updated");
    	}
    }

    function execute(){
    	$config = ItemReviewUtil::getConfig();

    	parent::__construct();

    	$this->addModel("updated", array(
    		"visible" => (isset($_GET["updated"]))
    	));

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
    }

    function setConfigObj($obj) {
		$this->config = $obj;
	}
}
