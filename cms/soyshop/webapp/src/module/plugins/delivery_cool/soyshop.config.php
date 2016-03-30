<?php
class DeliveryCoolConfig extends SOYShopConfigPageBase{

	/**
	 * @return string
	 */
	function getConfigPage(){
		SOY2DAOFactory::importEntity("config.SOYShop_Area");

		$form = SOY2HTMLFactory::createInstance("DeliveryCoolConfigFormPage");
		$form->setConfigObj($this);
		$form->execute();
		return $form->getObject();
	}

	/**
	 * @return string
	 */
	function getConfigPageTitle(){
		return "配送料、配達時間帯の設定（クール配送モジュール）";
	}

	function getPrices(){

		try{
			$price = SOYShop_DataSets::get("delivery.default.prices");
		}catch(Exception $e){
			$price = array();	//default
		}

		return $price;
	}

	function getCoolPrices(){

		try{
			$coolPrice = SOYShop_DataSets::get("delivery.cool.prices");
		}catch(Exception $e){
			$coolPrice = 0;
		}

		return $coolPrice;
	}
}
SOYShopPlugin::extension("soyshop.config", "delivery_cool", "DeliveryCoolConfig");

class DeliveryCoolConfigFormPage extends WebPage{

	private $config;

	function DeliveryCoolConfigFormPage(){
		SOY2DAOFactory::importEntity("SOYShop_DataSets");
	}

	function doPost(){

		if(soy2_check_token()){
			if(isset($_POST["price"])){
				SOYShop_DataSets::put("delivery.default.prices", $_POST["price"]);

				$coolPrice = (int)$_POST["cool_price"];

				if(!isset($coolPrice)){
					$coolPrice = 0;
				}
				SOYShop_DataSets::put("delivery.cool.prices", $coolPrice);
				$this->config->redirect("updated");
			}

			if(isset($_POST["delivery_time_config"])){
				$time_config = array_diff($_POST["delivery_time_config"], array(""));
				SOYShop_DataSets::put("delivery.default.delivery_time_config", $time_config);
				$this->config->redirect("updated");
			}
		}
	}

	function execute(){
		WebPage::WebPage();

		include_once(dirname(__FILE__) . "/common.php");

		$this->createAdd("price_form", "HTMLForm");

		$this->createAdd("cool_price", "HTMLInput", array(
			"style" => 'text-align:right;',
			"value" => DeliveryCoolCommon::getCoolPrice(),
			"name" => "cool_price"
		));

		$this->createAdd("prices", "DeliveryCoolPriceList", array(
			"list"   => SOYShop_Area::getAreas(),
			"prices" => DeliveryCoolCommon::getPrice(),
		));


		$this->createAdd("time_form", "HTMLForm");

		$time_config = DeliveryCoolCommon::getDliveryTimeConfig();
		while(count($time_config) <6){
			$time_config[] = "";
		}
		$this->createAdd("delivery_time_config", "DeliveryCoolTimeConfigList", array(
			"list" => $time_config,
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
}

class DeliveryCoolTimeConfigList extends HTMLList{

	function populateItem($entity){
		$this->createAdd("delivery_time", "HTMLInput", array(
			"value" => $entity,
			"name"  => "delivery_time_config[]"
		));
	}
}

class DeliveryCoolPriceList extends HTMLList{

	var $prices;
	var $coolPrices;

	function populateItem($entity,$key,$counter,$length){

		$this->createAdd("second_table", "HTMLModel", array(
			"visible" => ($counter == 24),
		));
		$this->createAdd("area_check", "HTMLCheckBox", array(
			"label"    => $entity,
			"elementId"  => "price_check_" . $key,
			"targetId" => "price_input_" . $key,
		));
		$this->createAdd("price", "HTMLInput", array(
			"attr:id"  => "price_input_" . $key,
			"value" => @$this->prices[$key],
			"name"  => "price[$key]"
		));

	}

	function setPrices($prices) {
		$this->prices = $prices;
	}

	function setCoolPrices($coolPrices){
		$this->coolPrices = $coolPrices;
	}
}