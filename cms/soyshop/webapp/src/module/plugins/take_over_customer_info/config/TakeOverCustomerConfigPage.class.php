<?php

class TakeOverCustomerConfigPage extends WebPage {

	private $configObj;

	function __construct(){
		SOY2::import("module.plugins.take_over_customer_info.util.TakeOverCustomerUtil");
	}

	function doPost(){
		if(soy2_check_token()){
			TakeOverCustomerUtil::saveConfig($_POST["Config"]);
			$this->configObj->redirect("updated");
		}
	}

	function execute(){
		parent::__construct();
		$cnf = TakeOverCustomerUtil::getConfig();

		$this->addForm("form");

		$this->addSelect("shop_id", array(
			"name" => "Config[shopId]",
			"options" => self::_getShopSiteList(),
			"selected"  => $cnf["shopId"]
		));

		//例
		$orderId = rand(1, 100);
		$userId = rand(1, 100);
		$trackingNumber = rand(1, 100) . "-" . rand(1000, 9999) . "-" . rand(1000, 9999);
		$orderDate = time();

		$this->addLabel("order_id_example", array(
			"text" => $orderId
		));

		$this->addLabel("user_id_example", array(
			"text" => $userId
		));

		$this->addLabel("tracking_number_example", array(
			"text" => $trackingNumber
		));

		$this->addLabel("order_date_example", array(
			"text" => $orderDate . " (" . date("Y-m-d H:i:s", $orderDate) . ")"
		));

		$this->addLabel("site_url_with_ctk_parameter", array(
			"text" => soyshop_get_site_url(true) . "?ctk=" . $trackingNumber . "-" . md5($orderId + $userId + $orderDate)
		));
	}

	private function _getShopSiteList(){
		$old = SOYAppUtil::switchAdminDsn();
		try{
			$shops = SOY2DAOFactory::create("admin.SiteDAO")->getBySiteType(Site::TYPE_SOY_SHOP);
		}catch(Exception $e){
			$shops = array();
		}

		SOYAppUtil::resetAdminDsn($old);
		if(!count($shops)) return array();

		$list = array();
		foreach($shops as $shop){
			$list[$shop->getId()] = $shop->getSiteName();
		}

		return $list;
	}

	function setConfigObj($configObj){
		$this->configObj = $configObj;
	}
}
