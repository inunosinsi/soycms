<?php
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
		return "ゆうパックの設定";
	}	

}
SOYShopPlugin::extension("soyshop.config","yupack_order_csv","YupackOrderCSVConfig");

class YupackOrderCSVConfigFormPage extends WebPage{
	
	private $config;
	
	function YupackOrderCSVConfigFormPage(){
		SOY2DAOFactory::importEntity("SOYShop_DataSets");
	}
	
	function doPost(){
		
		if(soy2_check_token()){
			$config = $_POST["config"];
			
			SOYShop_DataSets::put("yupack_order_csv", $config);
			
			$this->config->redirect("updated");
		}
		
		
	}
	
	function execute(){
		WebPage::WebPage();
		
		$this->addForm("form");
		
		$config = $this->getConfig();
		
		$this->addInput("yupack_item_name", array(
			"value" => (isset($config["name"])) ? $config["name"] : "",
			"name" => "config[name]"
		));
		
		$this->addModel("updated", array(
			"visible" => isset($_GET["updated"])
		));
		
	}
	
	function getTemplateFilePath(){
		return dirname(__FILE__) . "/soyshop.config.html";
	}

	function setConfigObj($obj) {
		$this->config = $obj;
	}
	
	function getConfig(){
		return SOYShop_DataSets::get("yupack_order_csv", array(
				"name" => ""
		));
	}
}
?>