<?php
class TwitterProductCardsConfig extends SOYShopConfigPageBase{

	/**
	 * @return string
	 */
	function getConfigPage(){

		$form = SOY2HTMLFactory::createInstance("TwitterProductCardsConfigFormPage");
		$form->setConfigObj($this);
		$form->execute();
		return $form->getObject();
	}

	/**
	 * @return string
	 * 拡張設定に表示されたモジュールのタイトルを表示する
	 */
	function getConfigPageTitle(){
		return "Twitter Cards:Product Cardの設定";
	}

}
SOYShopPlugin::extension("soyshop.config","twitter_product_cards","TwitterProductCardsConfig");

class TwitterProductCardsConfigFormPage extends WebPage{

	private $config;

	function __construct(){
		SOY2DAOFactory::importEntity("SOYShop_DataSets");
		include_once(dirname(__FILE__) . "/common.php");
	}

	function doPost(){

		if(soy2_check_token()&&isset($_POST["Config"])){
			$config = $_POST["Config"];
			$config["site"] = mb_convert_kana($config["site"], "a");
			$config["creater"] = mb_convert_kana($config["creater"], "a");

			SOYShop_DataSets::put("twitter_product_cards_config", $config);
			$this->config->redirect("updated");
		}


	}

	function execute(){
		parent::__construct();

		$config = TwitterProductCardsCommon::getConfig();


		$this->addForm("form");

		$this->addInput("site", array(
			"name" => "Config[site]",
			"value" => (isset($config["site"])) ? $config["site"] : ""
		));

		$this->addInput("creater", array(
			"name" => "Config[creater]",
			"value" => (isset($config["creater"])) ? $config["creater"] : ""
		));
	}

	function getTemplateFilePath(){
		return dirname(__FILE__) . "/soyshop.config.html";
	}

	function setConfigObj($obj) {
		$this->config = $obj;
	}
}
?>
