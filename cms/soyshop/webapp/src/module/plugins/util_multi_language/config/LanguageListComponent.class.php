<?php

class LanguageListComponent extends HTMLList{

	private $config;

	function populateItem($entity, $lang){
		$lngUri = (is_string($lang)) ? self::getLanguageUri($lang) : "";

		$this->addLink("create_pc_page_link", array(
			"link" => (isset($this->config[$lang]["prefix"])) ? SOY2PageController::createLink("Config.Detail?plugin=util_multi_language&create=" . $this->config[$lang]["prefix"] . "&carrier=pc") : null,
			"visible" => ($this->displayCreatePageLink($lang))
		));

		$this->addLink("create_i_page_link", array(
			"link" => (isset($this->config[$lang]["prefix"])) ? SOY2PageController::createLink("Config.Detail?plugin=util_multi_language&create=" . $this->config[$lang]["prefix"] . "&carrier=i") : null,
			"visible" => ($this->displayCreatePageLink($lang) && SOYShopPluginUtil::checkIsActive("util_mobile_check"))
		));


		$this->addModel("display_mobile_check_config", array(
			"visible" => ($lang != "jp" && $this->checkMobileCheckInstalled())
		));


		$this->addLabel("language_type", array(
			"text" => $entity
		));

		$this->addHidden("no_use_hidden", array(
			"name" => "Config[" . $lang . "][is_use]",
			"value" => UtilMultiLanguageUtil::NO_USE
		));

		$this->addCheckBox("is_use_checkbox", array(
			"name" => "Config[" . $lang . "][is_use]",
			"value" => UtilMultiLanguageUtil::IS_USE,
			"selected" => ($this->checkIsUse($lang)),
			"label" => " " . $entity . "サイトを表示する"
		));

		$this->addInput("prefix_input", array(
			"name" => "Config[" . $lang . "][prefix]",
			"value" => $lngUri
		));

		$this->addLabel("prefix_text", array(
			"text" => (strlen($lngUri)) ? "/" . $lngUri : ""
		));

		$iPrefix = (string)$this->getSmartPhonePrefix();
		$this->addLabel("smartphone_prefix_text", array(
			"text" => (strlen($iPrefix)) ? "/" . $iPrefix : ""
		));

		$this->addLabel("user_cart_id", array(
			"text" => (strlen($lngUri)) ? $this->getPcCartId() . "_" . $lngUri : $this->getPcCartId()
		));

		$this->addLabel("user_mypage_id", array(
			"text" => (strlen($lngUri)) ? $this->getPcMypageId() . "_" . $lngUri : $this->getPcMypageId()
		));

		$this->addLabel("user_sp_cart_id", array(
			"text" => (strlen($lngUri)) ? $this->getSpCartId() . "_" . $lngUri : $this->getSpCartId()
		));

		$this->addLabel("user_sp_mypage_id", array(
			"text" => (strlen($lngUri)) ? $this->getSpMypageId() . "_" . $lngUri : $this->getSpMypageId()
		));

		$this->addLabel("domain", array(
			"text" => $_SERVER["HTTP_HOST"]
		));

		$this->addLabel("shop_id", array(
			"text" => (SOYSHOP_IS_ROOT) ? "" : "/" . SOYSHOP_ID
		));
	}

	function displayCreatePageLink($lang){
		if(!isset($this->config[$lang]["prefix"])) return false;
		if(strlen($this->config[$lang]["prefix"]) === 0) return false;
		if(!isset($this->config[$lang]["is_use"])) return false;
		if($this->config[$lang]["is_use"] == UtilMultiLanguageUtil::NO_USE) return false;

		return true;
	}

	function checkIsUse($lang){
		if($lang == UtilMultiLanguageUtil::LANGUAGE_JP) return true;

		return (isset($this->config[$lang]["is_use"]) && $this->config[$lang]["is_use"] == UtilMultiLanguageUtil::IS_USE);
	}

	function checkMobileCheckInstalled(){
		static $check = null;
		if(is_null($check)){
			$check = (class_exists("SOYShopPluginUtil") && (SOYShopPluginUtil::checkIsActive("util_mobile_check")));
		}
		return $check;
	}

	function getSmartPhonePrefix(){
		static $prefix = null;

		if(is_null($prefix) && $this->checkMobileCheckInstalled()){
			SOY2::import("module.plugins.util_mobile_check.util.UtilMobileCheckUtil");
			$config = UtilMobileCheckUtil::getConfig();
			$prefix = (isset($config["prefix_i"])) ? $config["prefix_i"] : "";
		}

		return $prefix;
	}

	function getPcCartId(){
		static $pcCartId = null;
		if(is_null($pcCartId)){
			$pcCartId = SOYShop_DataSets::get("config.cart.cart_id", "bryon");
		}
		return $pcCartId;
	}

	function getSpCartId(){
		static $spCartId = null;
		if(is_null($spCartId)){
			$spCartId = SOYShop_DataSets::get("config.cart.smartphone_cart_id", "smart");
		}
		return $spCartId;
	}

	function getPcMypageId(){
		static $pcMypageId = null;
		if(is_null($pcMypageId)){
			$pcMypageId = SOYShop_DataSets::get("config.mypage.id", "bryon");
		}
		return $pcMypageId;
	}

	function getSpMypageId(){
		static $spMypageId = null;
		if(is_null($spMypageId)){
			$spMypageId = SOYShop_DataSets::get("config.mypage.smartphone.id", "smart");
		}
		return $spMypageId;
	}

	private function getLanguageUri(string $lng){
		if($lng !== "jp"){
			return (isset($this->config[$lng]["prefix"])) ? (string)$this->config[$lng]["prefix"] : $lng;
		}else{
			return (isset($this->config[$lng]["prefix"])) ? (string)$this->config[$lng]["prefix"] : "";
		}
	}


	function setConfig($config){
		$this->config = $config;
	}
}
