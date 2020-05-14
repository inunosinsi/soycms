<?php
class GoogleShoppingRssConfig extends SOYShopConfigPageBase{

	/**
	 * @return string
	 */
	function getConfigPage(){

		$form = SOY2HTMLFactory::createInstance("GoogleShoppingRssConfigFormPage");
		$form->setConfigObj($this);
		$form->execute();
		return $form->getObject();
	}

	/**
	 * @return string
	 * 拡張設定に表示されたモジュールのタイトルを表示する
	 */
	function getConfigPageTitle(){
		return "Googleショッピング用Feed出力設定";
	}

}
SOYShopPlugin::extension("soyshop.config", "google_shopping_rss", "GoogleShoppingRssConfig");

class GoogleShoppingRssConfigFormPage extends WebPage{

	function __construct(){
		SOY2DAOFactory::importEntity("SOYShop_DataSets");
	}

	function doPost(){
		SOY2DAOFactory::importEntity("SOYShop_DataSets");

		if(isset($_POST["config"]) and soy2_check_token()){

			if($this->checkValidate($_POST["config"]["count"])){
				SOYShop_DataSets::put("google_shopping_rss.config", $_POST["config"]);
				$this->config->redirect("updated");
			}
		}
		$this->config->redirect("error");
	}

	function checkValidate($str){
		return (preg_match("/^[0-9]+$/", $str)) ? true : false;
	}

	function execute(){
		$config = $this->getRssConfig();

		parent::__construct();

		$this->addModel("error", array(
			"visible" => isset($_GET["error"])
		));

		$this->addForm("form");

		$this->addInput("count", array(
			"name" => "config[count]",
			"value" => (isset($config["count"])) ? (int)$config["count"] : ""
		));
	}

	function getRssConfig(){

		return SOYShop_DataSets::get("google_shopping_rss.config", array(
			"count" => "10"
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
