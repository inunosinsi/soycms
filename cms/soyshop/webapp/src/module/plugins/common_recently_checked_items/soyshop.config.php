<?php
class RecentlyCheckedItemsConfig extends SOYShopConfigPageBase{

	/**
	 * @return string
	 */
	function getConfigPage(){
		$form = SOY2HTMLFactory::createInstance("RecentlyCheckedItemsConfigFormPage");
		$form->setConfigObj($this);
		$form->execute();
		return $form->getObject();
	}

	/**
	 * @return string
	 */
	function getConfigPageTitle(){
		return "最近表示した商品プラグインの設定";
	}
}

class RecentlyCheckedItemsConfigFormPage extends WebPage{

	private $configObj;
	const KEY = "recently_checked_items";

	function __construct(){
		SOY2DAOFactory::importEntity("SOYShop_DataSets");
	}

	function doPost(){
		if(isset($_POST[self::KEY])){
			SOYShop_DataSets::put(self::KEY, $_POST[self::KEY]);
			$this->configObj->redirect("updated");
		}
	}

	function execute(){

		parent::__construct();

		$config = SOYShop_DataSets::get(self::KEY, array(
			"max_display_number" => 10,
		));

		$this->addInput("max_display_number", array(
			"value" => $config["max_display_number"],
			"name"  => self::KEY."[max_display_number]"
		));
	}

	function getTemplateFilePath(){
		return dirname(__FILE__) . "/soyshop.config.html";
	}

	function setConfigObj($obj) {
		$this->configObj = $obj;
	}

}

SOYShopPlugin::extension("soyshop.config","common_recently_checked_items","RecentlyCheckedItemsConfig");
?>
