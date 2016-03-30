<?php
class CommonPriceCheckConfig extends SOYShopConfigPageBase{

	/**
	 * @return string
	 */
	function getConfigPage(){
		
		$form = SOY2HTMLFactory::createInstance("CommonPriceCheckConfigFormPage");
		$form->setConfigObj($this);
		$form->execute();
		return $form->getObject();
	}

	/**
	 * @return string
	 * 拡張設定に表示されたモジュールのタイトルを表示する
	 */
	function getConfigPageTitle(){
		return "購入最低金額設定";
	}
}
SOYShopPlugin::extension("soyshop.config", "common_price_check", "CommonPriceCheckConfig");

class CommonPriceCheckConfigFormPage extends WebPage{
	
	private $config;
	
	function CommonPriceCheckConfigFormPage(){
		SOY2DAOFactory::importEntity("SOYShop_DataSets");
		SOY2::import("module.plugins.common_price_check.common.CommonPriceCheckCommon");
	}
	
	function doPost(){
		
		if(soy2_check_token() && isset($_POST["Config"])){
			$config = $_POST["Config"];
			$config["price"] = soyshop_convert_number($config["price"], 0);
			
			SOYShop_DataSets::put("common_price_check", $config);
			$this->config->redirect("updated");
		}
	}
	
	function execute(){
		
		$config = CommonPriceCheckCommon::getConfig();
		
		WebPage::WebPage();
		
		$this->createAdd("updated","HTMLModel", array(
			"visible" => (isset($_GET["updated"]))
		));
		
		$this->addForm("form");
		
		$this->addInput("price", array(
			"name" => "Config[price]",
			"value" => (isset($config["price"])) ? $config["price"] : "",
			"style" => "width:15%;text-align:right;"
		));
		$this->addTextArea("error_text", array(
			"name" => "Config[error]",
			"value" => (isset($config["error"])) ? $config["error"] : ""
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