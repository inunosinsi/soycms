<?php
class DeliveryCountFreeConfig extends SOYShopConfigPageBase{

	/**
	 * @return string
	 */
	function getConfigPage(){
		$form = SOY2HTMLFactory::createInstance("DeliveryCountFreeConfigFormPage");
		$form->setConfigObj($this);
		$form->execute();
		return $form->getObject();
	}

	/**
	 * @return string
	 */
	function getConfigPageTitle(){
		return "配送料、配達時間、文言の設定";
	}
	
	
	

}
SOYShopPlugin::extension("soyshop.config","delivery_count_free","DeliveryCountFreeConfig");



class DeliveryCountFreeConfigFormPage extends WebPage{
	
	private $config;
	
	function DeliveryCountFreeConfigFormPage(){
		SOY2DAOFactory::importEntity("SOYShop_DataSets");
	}
	
	function doPost(){
		
		if(soy2_check_token()){
			if(isset($_POST["price"])){
				SOYShop_DataSets::put("delivery.count_free.price", $_POST["price"]);
			}
			if(isset($_POST["title"])){
				SOYShop_DataSets::put("delivery.count_free.title", $_POST["title"]);
			}
			if(isset($_POST["description"])){
				SOYShop_DataSets::put("delivery.count_free.description", $_POST["description"]);
			}
			if(isset($_POST["delivery_time_config"])){
				$time_config = array_diff($_POST["delivery_time_config"], array(""));
				SOYShop_DataSets::put("delivery.count_free.delivery_time_config",$time_config);
			}
			if(isset($_POST["special_price"])){
				$shopping = $_POST["special_price"]["shopping"];
				$fee = $_POST["special_price"]["fee"];
				
				$special_price = array();
				foreach($shopping as $key => $value){
					if(strlen($shopping[$key]) >0 && is_numeric($shopping[$key]) && strlen($fee[$key]) >0 && is_numeric($fee[$key])){
						$special_price[$shopping[$key]] = $fee[$key];
					}
				}
				
				ksort($special_price);
				SOYShop_DataSets::put("delivery.count_free.special_price",$special_price);
			}
			if(isset($_POST["discount"])){
				$shopping = $_POST["discount"]["shopping"];
				$percentage = $_POST["discount"]["percentage"];
				
				$discount = array();
				foreach($shopping as $key => $value){
					if(strlen($shopping[$key]) >0 && is_numeric($shopping[$key]) && strlen($percentage[$key]) >0 && is_numeric($percentage[$key])){
						$discount[$shopping[$key]] = $percentage[$key];
					}
				}
				
				ksort($discount);
				SOYShop_DataSets::put("delivery.count_free.discount",$discount);
			}

			$this->config->redirect("updated");
		}
		
		
	}
	
	function execute(){
		WebPage::WebPage();
		
		$this->addForm("form");
		
		include_once(dirname(__FILE__) . "/util.php");

		$price = DeliveryCountFreeConfigUtil::getPrice();

		$this->createAdd("item_count","HTMLInput", array(
			"value" => $price["item_count"],
			"name"  => "price[item_count]"
		));
		$this->createAdd("shipping_fee","HTMLInput", array(
			"value" => $price["shipping_fee"],
			"name"  => "price[shipping_fee]"
		));
		$this->createAdd("default_shipping_fee","HTMLInput", array(
			"value" => $price["default_shipping_fee"],
			"name"  => "price[default_shipping_fee]"
		));
		$this->createAdd("title","HTMLInput", array(
			"value" => DeliveryCountFreeConfigUtil::getTitle(),
			"name"  => "title"
		));
		$this->createAdd("description","HTMLTextArea", array(
			"value" => DeliveryCountFreeConfigUtil::getDescription(),
			"name"  => "description"
		));
		
		$this->createAdd("delivery_time_config","DeliveryCountFreeTimeConfigList", array(
			"list" => DeliveryCountFreeConfigUtil::getDliveryTimeConfig(),
		));

		$this->createAdd("special_shipping_fee_list","DeliveryCountFreeSpecialPriceList", array(
			"list" => DeliveryCountFreeConfigUtil::getSpecialPrice(),
		));
		
		$this->createAdd("discount_list","DeliveryCountFreeDiscountList", array(
			"list" => DeliveryCountFreeConfigUtil::getDiscountSetting(),
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

class DeliveryCountFreeTimeConfigList extends HTMLList{
	
	function populateItem($entity){
		$this->createAdd("delivery_time", "HTMLInput", array(
			"value" => $entity,
			"name"  => "delivery_time_config[]"
		));
	}
}

class DeliveryCountFreeSpecialPriceList extends HTMLList{
	
	function populateItem($entity,$key){
		$this->createAdd("shopping", "HTMLInput", array(
			"value" => $key,
			"name"  => "special_price[shopping][]"
		));
		
		$this->createAdd("fee", "HTMLInput", array(
			"value" => $entity,
			"name"  => "special_price[fee][]"
		));
	}
}

class DeliveryCountFreeDiscountList extends HTMLList{
	
	function populateItem($entity,$key){
		$this->createAdd("shopping", "HTMLInput", array(
			"value" => $key,
			"name"  => "discount[shopping][]"
		));
		
		$this->createAdd("percentage", "HTMLInput", array(
			"value" => $entity,
			"name"  => "discount[percentage][]"
		));
	}
}

