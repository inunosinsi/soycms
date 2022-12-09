<?php

class DeliverySameDayShippingConfigFormPage extends WebPage{

	private $configObj;
	private $config;

	function __construct(){
		SOY2::import("module.plugins.delivery_same_day_shipping.util.DeliverySameDayShippingUtil");
		SOY2::imports("module.plugins.delivery_same_day_shipping.component.*");
		SOY2DAOFactory::importEntity("config.SOYShop_Area");

		$this->config = DeliverySameDayShippingUtil::getConfig();
	}

	function doPost(){

		if(soy2_check_token()){
			if(isset($_POST["config"])){
				$config = $_POST["config"];
				$config["free"] = mb_convert_kana($config["free"], "a");
				$config["free"] = (is_numeric($config["free"])) ? $config["free"] : null;
				SOYShop_DataSets::put("delivery.default.free_price", $config);
			}

			if(isset($_POST["price"])){
				SOYShop_DataSets::put("delivery.default.prices", $_POST["price"]);
			}

			if(isset($_POST["delivery"])){
				DeliverySameDayShippingUtil::saveConfig($_POST["delivery"]);
			}

			$this->configObj->redirect("updated");
		}
	}

	function execute(){
		parent::__construct();

		//営業日カレンダー周りのタグ
		self::buildNoticeArea();

		$this->addForm("form");

		$this->addInput("title", array(
			"name" => "delivery[title]",
			"value" => (isset($this->config["title"])) ? $this->config["title"] : ""
		));

		//営業時間
		self::buildBusinessHourForm();

		//午前中の注文設定
		self::buildDeliveryForm();

		$free = DeliverySameDayShippingUtil::getFreePrice();

		$this->addInput("price_free", array(
			"name" => "config[free]",
			"value" => (isset($free["free"])) ? $free["free"] : ""
		));

		$this->createAdd("prices", "PriceListComponent", array(
			"list"   => SOYShop_Area::getAreas(),
			"prices" => DeliverySameDayShippingUtil::getPrice()
		));
	}

	private function buildNoticeArea(){

		//営業日カレンダーがインストールされているか調べる
		SOY2::import("util.SOYShopPluginUtil");
		$installedCal = SOYShopPluginUtil::checkIsActive("parts_calendar");

		DisplayPlugin::toggle("installed_calendar_plugin", $installedCal);
		DisplayPlugin::toggle("no_installed_calendar_plugin", !$installedCal);

		$this->addLink("calendar_config_link", array(
			"link" => SOY2PageController::createLink("Config.Detail?plugin=parts_calendar")
		));
	}

	private function buildBusinessHourForm(){

		foreach(array("start", "end") as $label){
			$this->addSelect("business_hour_" . $label . "_hour", array(
				"name" => "delivery[businessHour][" . $label . "][hour]",
				"options" => range(0,24),
				"selected" => (isset($this->config["businessHour"][$label]["hour"])) ? $this->config["businessHour"][$label]["hour"] : "00"
			));

			$this->addSelect("business_hour_" . $label . "_min", array(
				"name" => "delivery[businessHour][" . $label . "][min]",
				"options" => array("00", "10", "20", "30", "40", "50"),
				"selected" => (isset($this->config["businessHour"][$label]["min"])) ? $this->config["businessHour"][$label]["min"] : "00"
			));
		}
	}

	private function buildDeliveryForm(){

		foreach(array("am", "pm", "regular") as $mode){
			$this->addInput("delivery_after_day_" . $mode, array(
				"name" => "delivery[delivery][" . $mode . "][day]",
				"value" => $this->config["delivery"][$mode]["day"],
				"style" => "width:50px;text-align:right;"
			));

			$this->addTextArea("delivery_after_description_" . $mode, array(
				"name" => "delivery[delivery][" . $mode . "][description]",
				"value" => $this->config["delivery"][$mode]["description"],
				"style" => "width:100%;"
			));
		}

	}

	function setConfigObj($configObj) {
		$this->configObj = $configObj;
	}
}
?>
