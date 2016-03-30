<?php
class DeliveryNormalConfigFormPage extends WebPage{

	private $config;

	function DeliveryNormalConfigFormPage(){
		SOY2::import("module.plugins.delivery_normal.util.DeliveryNormalUtil");
		SOY2DAOFactory::importEntity("config.SOYShop_Area");
		SOY2DAOFactory::importEntity("SOYShop_DataSets");
	}

	function doPost(){

		if(soy2_check_token()){

			if(isset($_POST["title"])){
				SOYShop_DataSets::put("delivery.default.title", $_POST["title"]);
			}
			if(isset($_POST["description"])){
				SOYShop_DataSets::put("delivery.default.description", $_POST["description"]);
			}

			if(isset($_POST["config"])){
				$config = $_POST["config"];
				$config["free"] = mb_convert_kana($config["free"], "a");
				$config["free"] = (is_numeric($config["free"])) ? $config["free"] : null;
				SOYShop_DataSets::put("delivery.default.free_price", $config);
				$this->config->redirect("updated");
			}

			if(isset($_POST["price"])){
				SOYShop_DataSets::put("delivery.default.prices", $_POST["price"]);
				$this->config->redirect("updated");
			}

			if(isset($_POST["delivery_time_config"])){
				$time_config = array_diff($_POST["delivery_time_config"], array(""));
				SOYShop_DataSets::put("delivery.default.delivery_time_config", $time_config);

				//配達時間帯を使用するかどうかの設定
				$useDeliveryTime["use"] = (isset($_POST["use_delivery_time"]) && $_POST["use_delivery_time"] == 1) ? 1 : 0;
				SOYShop_DataSets::put("delivery.default.use.time", $useDeliveryTime);
				$this->config->redirect("updated");
			}
		}
	}

	function execute(){
		WebPage::WebPage();

		$this->buildTextForm();
		$this->buildPriceForm();
		$this->buildTimeForm();

		$this->addModel("updated", array(
			"visible" => isset($_GET["updated"])
		));
	}

	function buildTextForm(){
		$this->addForm("text_form");
		
		$this->addInput("title", array(
			"value" => DeliveryNormalUtil::getTitle(),
			"name"  => "title"
		));
		$this->addTextArea("description", array(
			"value" => DeliveryNormalUtil::getDescription(),
			"name"  => "description"
		));
	}

	function buildPriceForm(){
		$free = DeliveryNormalUtil::getFreePrice();

		$this->addForm("free_form");

		$this->addInput("price_free", array(
			"name" => "config[free]",
			"value" => (isset($free["free"])) ? $free["free"] : ""
		));

		$this->addForm("price_form");

		$this->createAdd("prices", "DeliveryNormalPriceList", array(
			"list"   => SOYShop_Area::getAreas(),
			"prices" => DeliveryNormalUtil::getPrice()
		));
	}

	function buildTimeForm(){
		$this->addForm("time_form");

		$time_config = DeliveryNormalUtil::getDeliveryTimeConfig();
		while(count($time_config) < 6){
			$time_config[] = "";
		}

		$useDeliveryTime = DeliveryNormalUtil::getUseDeliveryTimeConfig();
		$this->addCheckBox("use_delivery_time", array(
			"name" => "use_delivery_time",
			"value" => 1,
			"selected" => (isset($useDeliveryTime["use"]) && $useDeliveryTime["use"] == 1),
			"elementId" => "use_delivery_time"
		));

		$this->createAdd("delivery_time_config", "DeliveryNormalTimeConfigList", array(
			"list" => $time_config,
		));
	}

	function setConfigObj($obj) {
		$this->config = $obj;
	}
}

class DeliveryNormalTimeConfigList extends HTMLList{

	function populateItem($entity){
		$this->addInput("delivery_time", array(
			"value" => $entity,
			"name"  => "delivery_time_config[]"
		));
	}
}

class DeliveryNormalPriceList extends HTMLList{

	var $prices;

	function populateItem($entity, $key, $counter, $length){
		$this->addModel("second_table", array(
			"visible" => ($counter == 24),
		));
		$this->addCheckBox("area_check", array(
			"label"    => $entity,
			"elementId"  => "price_check_" . $key,
			"targetId" => "price_input_" . $key,
		));
		$this->addInput("price", array(
			"attr:id"  => "price_input_" . $key,
			"value" => (isset($this->prices[$key])) ? $this->prices[$key] : "",
			"name"  => "price[$key]"
		));
	}

	function setPrices($prices) {
		$this->prices = $prices;
	}
}
?>