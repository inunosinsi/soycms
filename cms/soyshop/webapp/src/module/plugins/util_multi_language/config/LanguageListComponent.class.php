<?php

class LanguageListComponent extends HTMLList{

	private $config;

	function populateItem($entity, $lng){
		if(is_null($lng)) $lng = "jp";
		$lngUri = (is_string($lng)) ? self::getLanguageUri($lng) : "";

		$this->addLink("create_pc_page_link", array(
			"link" => (isset($this->config[$lng]["prefix"])) ? SOY2PageController::createLink("Config.Detail?plugin=util_multi_language&create=" . $this->config[$lng]["prefix"] . "&carrier=pc") : null,
			"visible" => (is_string($lng) && self::_displayCreatePageLink($lng))
		));

		$this->addLink("create_i_page_link", array(
			"link" => (isset($this->config[$lng]["prefix"])) ? SOY2PageController::createLink("Config.Detail?plugin=util_multi_language&create=" . $this->config[$lng]["prefix"] . "&carrier=i") : null,
			"visible" => (is_string($lng) && self::_displayCreatePageLink($lng) && SOYShopPluginUtil::checkIsActive("util_mobile_check"))
		));


		$this->addModel("display_mobile_check_config", array(
			"visible" => ($lng != "jp" && $this->checkMobileCheckInstalled())
		));


		$this->addLabel("language_type", array(
			"text" => $entity
		));

		$this->addHidden("no_use_hidden", array(
			"name" => "Config[" . $lng . "][is_use]",
			"value" => UtilMultiLanguageUtil::NO_USE
		));

		$this->addCheckBox("is_use_checkbox", array(
			"name" => "Config[" . $lng . "][is_use]",
			"value" => UtilMultiLanguageUtil::IS_USE,
			"selected" => (is_string($lng) && self::_checkIsUse($lng)),
			"label" => " " . $entity . "サイトを表示する"
		));

		$this->addInput("prefix_input", array(
			"name" => "Config[" . $lng . "][prefix]",
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

	private function _displayCreatePageLink(string $lng){
		if(!isset($this->config[$lng]["prefix"])) return false;
		if(strlen($this->config[$lng]["prefix"]) === 0) return false;
		if(!isset($this->config[$lng]["is_use"])) return false;
		if($this->config[$lng]["is_use"] == UtilMultiLanguageUtil::NO_USE) return false;

		return true;
	}

	private function _checkIsUse(string $lng){
		if($lng == UtilMultiLanguageUtil::LANGUAGE_JP) return true;
		return (isset($this->config[$lng]["is_use"]) && $this->config[$lng]["is_use"] == UtilMultiLanguageUtil::IS_USE);
	}

	function checkMobileCheckInstalled(){
		static $check;
		if(is_null($check)) $check = (class_exists("SOYShopPluginUtil") && (SOYShopPluginUtil::checkIsActive("util_mobile_check")));
		return $check;
	}

	function getSmartPhonePrefix(){
		static $prefix;
		if(is_null($prefix) && $this->checkMobileCheckInstalled()){
			SOY2::import("module.plugins.util_mobile_check.util.UtilMobileCheckUtil");
			$cnf = UtilMobileCheckUtil::getConfig();
			$prefix = (isset($cnf["prefix_i"])) ? $cnf["prefix_i"] : "";
		}
		return $prefix;
	}

	function getPcCartId(){
		static $pcCartId;
		if(is_null($pcCartId)) $pcCartId = SOYShop_DataSets::get("config.cart.cart_id", "bryon");
		return $pcCartId;
	}

	function getSpCartId(){
		static $spCartId;
		if(is_null($spCartId)) $spCartId = SOYShop_DataSets::get("config.cart.smartphone_cart_id", "smart");
		return $spCartId;
	}

	function getPcMypageId(){
		static $pcMypageId;
		if(is_null($pcMypageId)) $pcMypageId = SOYShop_DataSets::get("config.mypage.id", "bryon");
		return $pcMypageId;
	}

	function getSpMypageId(){
		static $spMypageId;
		if(is_null($spMypageId)) $spMypageId = SOYShop_DataSets::get("config.mypage.smartphone.id", "smart");
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
