<?php
class CommonNewItemConfig extends SOYShopConfigPageBase{

	/**
	 * @return string
	 */
	function getConfigPage(){
		
		$form = SOY2HTMLFactory::createInstance("CommonNewItemConfigFormPage");
		$form->setConfigObj($this);
		$form->execute();
		return $form->getObject();
	}

	/**
	 * @return string
	 * 拡張設定に表示されたモジュールのタイトルを表示する
	 */
	function getConfigPageTitle(){
		return "新着商品設定";
	}

}
SOYShopPlugin::extension("soyshop.config","common_new_item","CommonNewItemConfig");

class CommonNewItemConfigFormPage extends WebPage{
	
	private $config;
	
	function CommonNewItemConfigFormPage(){
		SOY2DAOFactory::importEntity("SOYShop_DataSets");
	}
	
	function doPost(){
		
		if(soy2_check_token()&&isset($_POST["Config"])){
			$config = $_POST["Config"];
			$config["count"] = (isset($config["count"])) ? mb_convert_kana($config["count"], "a") : 0;
			if(!is_numeric($config["count"]))$config["count"] = 0;
			
			SOYShop_DataSets::put("common_new_item", $config);
			$this->config->redirect("updated");
		}
		
		
	}
	
	function execute(){
		
		$config = $this->getConfig();
		
		WebPage::WebPage();
		
		$this->createAdd("updated","HTMLModel", array(
			"visible" => (isset($_GET["updated"]))
		));
		
		$this->addForm("form");
		
		$this->createAdd("count","HTMLInput", array(
			"name" => "Config[count]",
			"value" => @$config["count"],
			"style" => "text-align:right;ime-mode:inactive;"
		));
		
	}
	
	function getTemplateFilePath(){
		return dirname(__FILE__) . "/soyshop.config.html";
	}

	function setConfigObj($obj) {
		$this->config = $obj;
	}
	
	function getConfig(){
		return SOYShop_DataSets::get("common_new_item", array(
			"count" => 4
		));
	}
}
?>
