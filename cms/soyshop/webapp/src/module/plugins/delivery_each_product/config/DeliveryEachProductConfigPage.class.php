<?php

class DeliveryEachProductConfigPage extends WebPage{

  private $configObj;

  function __construct(){
    SOY2::import("module.plugins.delivery_normal.util.DeliveryNormalUtil");
    SOY2::import("module.plugins.delivery_normal.component.DeliveryPriceListComponent");
    SOY2DAOFactory::importEntity("config.SOYShop_Area");
  }

  function doPost(){
    if(soy2_check_token()){
			if(isset($_POST["price"])){
				DeliveryNormalUtil::savePrice($_POST["price"]);
			}

      $this->configObj->redirect("updated");
    }

    $this->configObj->redirect("error");
  }

  function execute(){
    WebPage::__construct();

    DisplayPlugin::toggle("error", isset($_GET["error"]));
    DisplayPlugin::toggle("installed_other_module", self::checkInstalledOtherDeliveryModule());

    $this->buildForm();

    $this->addLabel("toggle_js", array(
      "html" => "\n" . file_get_contents(dirname(dirname(__FILE__)) . "/js/toggle.js")
    ));
  }

  private function buildForm(){
    $this->addForm("form");

    $this->createAdd("prices", "DeliveryPriceListComponent", array(
			"list"   => SOYShop_Area::getAreas(),
			"prices" => DeliveryNormalUtil::getPrice()
		));
  }

  // 他の配送モジュールがインストールされていればtrueを返す
  private function checkInstalledOtherDeliveryModule(){
    try{
      $plugins = SOY2DAOFactory::create("plugin.SOYShop_PluginConfigDAO")->getByTypeAndIsActive(SOYShop_PluginConfig::PLUGIN_TYPE_DELIVERY, SOYShop_PluginConfig::PLUGIN_ACTIVE);
    }catch(Exception $e){
      return false;
    }

    // 自身の配送モジュール以外を調べるため、1より多いかで判定
    return (count($plugins) > 1);
  }

  function setConfigObj($configObj){
    $this->configObj = $configObj;
  }
}
