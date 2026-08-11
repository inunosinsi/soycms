<?php
class DeliveryChargeFreeConfig extends SOYShopConfigPageBase{

	/**
	 * @return string
	 */
	function getConfigPage(){
		$form = SOY2HTMLFactory::createInstance("DeliveryChargeFreeConfigFormPage");
		$form->setConfigObj($this);
		$form->execute();
		return $form->getObject();
	}

	/**
	 * @return string
	 */
	function getConfigPageTitle(){
		return "配送料、配達時間、文言の設定（無料配送モジュール）";
	}




}
SOYShopPlugin::extension("soyshop.config","delivery_charge_free","DeliveryChargeFreeConfig");



class DeliveryChargeFreeConfigFormPage extends WebPage{

	private $config;

	function __construct(){
		SOY2DAOFactory::importEntity("SOYShop_DataSets");
	}

	function doPost(){

		if(soy2_check_token()){
			if(isset($_POST["price"])){
				SOYShop_DataSets::put("delivery.charge_free.price", $_POST["price"]);
			}
			if(isset($_POST["notification"])){
				SOYShop_DataSets::put("delivery.charge_free.notification", $_POST["notification"]);
			}
			if(isset($_POST["title"])){
				SOYShop_DataSets::put("delivery.charge_free.title", $_POST["title"]);
			}
			if(isset($_POST["description"])){
				SOYShop_DataSets::put("delivery.charge_free.description", $_POST["description"]);
			}
			if(isset($_POST["delivery_time_config"])){
				$time_config = array_diff($_POST["delivery_time_config"], array(""));
				SOYShop_DataSets::put("delivery.charge_free.delivery_time_config",$time_config);
			}
			if(isset($_POST["special_price"])){
				$shopping = $_POST["special_price"]["shopping"];
				$fee = $_POST["special_price"]["fee"];

				$special_price = array();
				foreach($shopping as $key => $value){
					if(strlen($shopping[$key]) > 0 && is_numeric($shopping[$key]) && strlen($fee[$key]) > 0 && is_numeric($fee[$key])){
						$special_price[$shopping[$key]] = $fee[$key];
					}
				}

				ksort($special_price);
				SOYShop_DataSets::put("delivery.charge_free.special_price", $special_price);
			}
			if(isset($_POST["discount"])){
				$shopping = $_POST["discount"]["shopping"];
				$percentage = $_POST["discount"]["percentage"];

				$discount = array();
				foreach($shopping as $key => $value){
					if(strlen($shopping[$key]) > 0 && is_numeric($shopping[$key]) && strlen($percentage[$key]) > 0 && is_numeric($percentage[$key])){
						$discount[$shopping[$key]] = $percentage[$key];
					}
				}

				ksort($discount);
				SOYShop_DataSets::put("delivery.charge_free.discount", $discount);
			}

			$this->config->redirect("updated");
		}
	}

	function execute(){
		parent::__construct();

		$this->addForm("form");

		include_once(dirname(__FILE__) . "/util.php");

		$price = DeliveryChargeFreeConfigUtil::getPrice();

		$this->addInput("item_price", array(
			"value" => $price["item_price"],
			"name"  => "price[item_price]"
		));
		$this->addInput("shipping_fee", array(
			"value" => $price["shipping_fee"],
			"name"  => "price[shipping_fee]"
		));
		$this->addInput("default_shipping_fee", array(
			"value" => $price["default_shipping_fee"],
			"name"  => "price[default_shipping_fee]"
		));

		$notification = DeliveryChargeFreeConfigUtil::getNotification();

		$this->addCheckBox("notification_check", array(
			"name" => "notification[check]",
			"value" => 1,
			"selected" => (isset($notification["check"]) && $notification["check"] == 1),
			"label" => "カートの商品確認画面で通知を表示"
		));

		$this->addTextArea("notification_text", array(
			"name" => "notification[text]",
			"value" => (isset($notification["text"])) ? $notification["text"] : ""
		));

		$this->addInput("title", array(
			"value" => DeliveryChargeFreeConfigUtil::getTitle(),
			"name"  => "title"
		));
		$this->addTextArea("description", array(
			"value" => DeliveryChargeFreeConfigUtil::getDescription(),
			"name"  => "description"
		));

		$this->createAdd("delivery_time_config", "DeliveryChargeFreeTimeConfigList", array(
			"list" => DeliveryChargeFreeConfigUtil::getDliveryTimeConfig(),
		));

		$this->createAdd("special_shipping_fee_list", "DeliveryChargeFreeSpecialPriceList", array(
			"list" => DeliveryChargeFreeConfigUtil::getSpecialPrice(),
		));

		$this->createAdd("discount_list", "DeliveryChargeFreeDiscountList", array(
			"list" => DeliveryChargeFreeConfigUtil::getDiscountSetting(),
		));
	}

	function getTemplateFilePath(){
		return dirname(__FILE__) . "/soyshop.config.html";
	}

	function setConfigObj($obj) {
		$this->config = $obj;
	}
}

class DeliveryChargeFreeTimeConfigList extends HTMLList{

	function populateItem($entity){
		$this->addInput("delivery_time", array(
			"value" => $entity,
			"name"  => "delivery_time_config[]"
		));
	}
}

class DeliveryChargeFreeSpecialPriceList extends HTMLList{

	function populateItem($entity,$key){
		$this->addInput("shopping", array(
			"value" => $key,
			"name"  => "special_price[shopping][]"
		));

		$this->addInput("fee", array(
			"value" => $entity,
			"name"  => "special_price[fee][]"
		));
	}
}

class DeliveryChargeFreeDiscountList extends HTMLList{

	function populateItem($entity,$key){
		$this->addInput("shopping", array(
			"value" => $key,
			"name"  => "discount[shopping][]"
		));

		$this->addInput("percentage", array(
			"value" => $entity,
			"name"  => "discount[percentage][]"
		));
	}
}
