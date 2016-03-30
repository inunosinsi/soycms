<?php
class B2OrderCSVConfig extends SOYShopConfigPageBase{

	/**
	 * @return string
	 */
	function getConfigPage(){
		$form = SOY2HTMLFactory::createInstance("B2OrderCSVConfigFormPage");
		$form->setConfigObj($this);
		$form->execute();
		return $form->getObject();
	}

	/**
	 * @return string
	 */
	function getConfigPageTitle(){
		return "B2の設定";
	}	

}
SOYShopPlugin::extension("soyshop.config","b2_order_csv","B2OrderCSVConfig");

class B2OrderCSVConfigFormPage extends WebPage{
	
	private $config;
	
	function B2OrderCSVConfigFormPage(){
		SOY2DAOFactory::importEntity("SOYShop_DataSets");
	}
	
	function doPost(){
		
		if(soy2_check_token()){
			$config = $_POST["config"];
			$config["number"] = mb_convert_kana($config["number"], "a");
			
			SOYShop_DataSets::put("b2_order_csv", $config);
			
			$this->config->redirect("updated");
		}
		
		
	}
	
	function execute(){
		WebPage::WebPage();
		
		$this->addForm("form");
		
		$config = $this->getConfig();
		
		$this->createAdd("b2_customer_number","HTMLInput", array(
			"value" => @$config["number"],
			"name" => "config[number]"
		));
		
		$this->createAdd("b2_item_name","HTMLInput", array(
			"value" => @$config["name"],
			"name" => "config[name]"
		));
		
		$this->createAdd("updated", "HTMLModel", array(
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
		return SOYShop_DataSets::get("b2_order_csv", array(
				"number" => "",
				"name" => ""
		));
	}
}
?>