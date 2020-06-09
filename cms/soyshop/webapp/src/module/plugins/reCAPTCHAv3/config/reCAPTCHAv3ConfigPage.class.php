<?php

class reCAPTCHAv3ConfigPage extends WebPage {

	private $configObj;

	function __construct(){
		SOY2::import("module.plugins.reCAPTCHAv3.util.reCAPTCHAUtil");
	}

	function doPost(){
		if(soy2_check_token()){
			reCAPTCHAUtil::saveConfig($_POST["Config"]);
			$this->configObj->redirect("updated");
		}
	}

	function execute(){
		parent::__construct();

		$config = reCAPTCHAUtil::getConfig();

		$this->addForm("form");

		$this->addInput("site_key", array(
			"name" => "Config[site_key]",
			"value" => (isset($config["site_key"])) ? $config["site_key"] : ""
		));

		$this->addInput("secret_key", array(
			"name" => "Config[secret_key]",
			"value" => (isset($config["secret_key"])) ? $config["secret_key"] : ""
		));

		//お問い合わせページを登録する
		$this->addSelect("page_id", array(
			"name" => "Config[page_id]",
			"options" => self::_getPageList(),
			"selected" => (isset($config["page_id"])) ? $config["page_id"] : false
		));
	}

	private function _getPageList(){
		try{
			$pages = SOY2DAOFactory::create("site.SOYShop_PageDAO")->get();
		}catch(Exception $e){
			$pages = array();
		}
		if(!count($pages)) return array();

		$list = array();
		$types = SOYShop_Page::getTypeTexts();
		foreach($pages as $page){
			$list[$page->getId()] = $page->getName() . "（" . $types[$page->getType()] . "）";
		}
		return $list;
	}

	function setConfigObj($configObj){
		$this->configObj = $configObj;
	}
}
