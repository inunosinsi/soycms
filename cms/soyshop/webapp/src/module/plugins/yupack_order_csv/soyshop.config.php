<?php
include_once(dirname(__FILE__) . "/common/common.php");

class YupackOrderCSVConfig extends SOYShopConfigPageBase{

	/**
	 * @return string
	 */
	function getConfigPage(){
		$form = SOY2HTMLFactory::createInstance("YupackOrderCSVConfigFormPage");
		$form->setConfigObj($this);
		$form->execute();
		return $form->getObject();
	}

	/**
	 * @return string
	 */
	function getConfigPageTitle(){
		return "ゆうパックプリントR対応CSV出力の設定";
	}

}
SOYShopPlugin::extension("soyshop.config","yupack_order_csv","YupackOrderCSVConfig");

class YupackOrderCSVConfigFormPage extends WebPage{

	private $config;

	function __construct(){
		SOY2DAOFactory::importEntity("SOYShop_DataSets");
	}

	function doPost(){
		if(soy2_check_token()){
			$config = $_POST["config"];
			$this->saveConfig($config);
			$this->config->redirect("updated");
		}
	}

	function execute(){
		parent::__construct();

		$this->addForm("form");

		$config = $this->getConfig();

		foreach($config as $key => $value){
			$this->addInput($key, array(
					"value" => $value,
					"name" => "config[".$key."]",
			));

		}
	}

	function getTemplateFilePath(){
		return dirname(__FILE__) . "/soyshop.config.html";
	}

	public function setConfigObj($obj) {
		$this->config = $obj;
	}

	private function getConfig(){
		$util = new YupackOutputCSV();
		return $util->getConfig();
	}

	private function saveConfig($config){
		$util = new YupackOutputCSV();
		$util->saveConfig($config);
	}
}
