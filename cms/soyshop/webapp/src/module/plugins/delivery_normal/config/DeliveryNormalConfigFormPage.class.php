<?php
class DeliveryNormalConfigFormPage extends WebPage{

	private $configObj;

	function __construct(){
		SOY2::import("module.plugins.delivery_normal.util.DeliveryNormalUtil");
		SOY2::import("module.plugins.delivery_normal.component.DeliveryPriceListComponent");
		SOY2::import("module.plugins.delivery_normal.component.DeliveryTimeConfigListComponent");
		SOY2DAOFactory::importEntity("config.SOYShop_Area");
		SOY2DAOFactory::importEntity("SOYShop_DataSets");
		SOY2::import("util.SOYShopPluginUtil");
	}

	function doPost(){

		if(soy2_check_token()){

			if(isset($_POST["title"])){
				DeliveryNormalUtil::saveTitle($_POST["title"]);
			}
			if(isset($_POST["description"])){
				DeliveryNormalUtil::saveDescription($_POST["description"]);
			}

			if(isset($_POST["config"])){
				DeliveryNormalUtil::saveFreePrice($_POST["config"]);
			}

			if(isset($_POST["price"])){
				DeliveryNormalUtil::savePrice($_POST["price"]);
			}

			if(isset($_POST["delivery_time_config"])){
				DeliveryNormalUtil::saveDeliveryTimeConfig($_POST["delivery_time_config"]);

				//配達時間帯を使用するかどうかの設定
				$useDeliveryTime["use"] = (isset($_POST["use_delivery_time"]) && $_POST["use_delivery_time"] == 1) ? 1 : 0;
				DeliveryNormalUtil::saveUseDeliveryTimeConfig($useDeliveryTime);
			}
			
			if(isset($_POST["Date"])){
				DeliveryNormalUtil::saveDeliveryDateConfig($_POST["Date"]);
			}
			
			$this->configObj->redirect("updated");
		}
	}

	function execute(){
		WebPage::__construct();
		
		$this->addForm("form");

		self::buildTextForm();
		self::buildPriceForm();
		self::buildTimeForm();
		self::buildDateForm();
	}

	private function buildTextForm(){

		$this->addInput("title", array(
			"value" => DeliveryNormalUtil::getTitle(),
			"name"  => "title"
		));
		$this->addTextArea("description", array(
			"value" => DeliveryNormalUtil::getDescription(),
			"name"  => "description"
		));
	}

	private function buildPriceForm(){
		$free = DeliveryNormalUtil::getFreePrice();

		$this->addInput("price_free", array(
			"name" => "config[free]",
			"value" => (isset($free["free"])) ? $free["free"] : ""
		));

		$this->createAdd("prices", "DeliveryPriceListComponent", array(
			"list"   => SOYShop_Area::getAreas(),
			"prices" => DeliveryNormalUtil::getPrice()
		));
	}

	private function buildTimeForm(){
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

		$this->createAdd("delivery_time_config", "DeliveryTimeConfigListComponent", array(
			"list" => $time_config,
		));
	}
	
	private function buildDateForm(){
		$config = DeliveryNormalUtil::getDeliveryDateConfig();
		
		$this->addCheckBox("use_delivery_date", array(
			"name" => "Date[use_delivery_date]",
			"value" => 1,
			"selected" => (isset($config["use_delivery_date"]) && $config["use_delivery_date"] == 1),
			"label" => "お届け日の指定を表示する"
		));
		
		$this->addCheckBox("use_delivery_date_unspecified", array(
			"name" => "Date[use_delivery_date_unspecified]",
			"value" => 1,
			"selected" => (isset($config["use_delivery_date_unspecified"]) && $config["use_delivery_date_unspecified"] == 1),
			"label" => "お届け日のセレクトボックスに指定なしを追加する"
		));
		
		$this->addInput("delivery_shortest_date", array(
			"name" => "Date[delivery_shortest_date]",
			"value" => (isset($config["delivery_shortest_date"])) ? (int)$config["delivery_shortest_date"] : "",
			"style" => "width:60px;text-align:right;"
		));
		
		$this->addCheckBox("use_re_calc_shortest_date", array(
			"name" => "Date[use_re_calc_shortest_date]",
			"value" => 1,
			"selected" => (isset($config["use_re_calc_shortest_date"]) && $config["use_re_calc_shortest_date"] == 1),
			"label" => "注文日が定休日の場合、最短のお届け日を翌営業日から表示する"
		));
		
		$installedCalender = SOYShopPluginUtil::checkIsActive("parts_calendar");
		DisplayPlugin::toggle("notice_re_calc_shortest_date", !$installedCalender);
		
		DisplayPlugin::toggle("installed_calendar_plugin", $installedCalender);
		
		$this->addLink("calendar_config_link", array(
			"link" => SOY2PageController::createLink("Config.Detail?plugin=parts_calendar")
		));
		
		
		$this->addInput("delivery_date_period", array(
			"name" => "Date[delivery_date_period]",
			"value" => (isset($config["delivery_date_period"])) ? (int)$config["delivery_date_period"] : "",
			"style" => "width:60px;text-align:right;"
		));
		
		$this->addInput("delivery_date_format", array(
			"name" => "Date[delivery_date_format]",
			"value" => (isset($config["delivery_date_format"])) ? $config["delivery_date_format"] : "",
		));
	}

	function setConfigObj($configObj) {
		$this->configObj = $configObj;
	}
}
?>