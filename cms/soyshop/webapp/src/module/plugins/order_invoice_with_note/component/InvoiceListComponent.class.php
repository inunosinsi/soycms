<?php

class InvoiceListComponent extends HTMLList{

	private $orderId;

	private $itemOrderDao;
	private $userDao;
	private $itemDao;

	protected function populateItem($entity){
		$order = self::orderLogic()->getById($this->orderId);

		/*** 注文概要 ***/
		self::buildOrderArea($order);

		/*** 支払い配送情報 ***/
		$this->createAdd("module_list", "InvoiceModuleListComponent", array(
		 	"list" => self::insertDaibikiModule($order->getModuleList())
		));

		$this->addLabel("document_label", array(
			"text" => (defined("ORDER_DOCUMENT_LABEL")) ? ORDER_DOCUMENT_LABEL : "納品書"
		));

		/*** お届け先 ***/
		self::buildSendArea($order);

		/*** 顧客情報 ***/
		self::buildClaimedArea($order);

		// /*** 注文商品 ***/
		$items = self::getItemOrders($order->getItems(), $order->getId());
		//商品が10個以上の場合はその他にしてまとめる
		$otherNum = 9;
		if(count($items) > $otherNum){
			$otherItem = new SOYShop_ItemOrder();
			$otherItem->setItemId(-1);
			$otherItem->setItemName("その他");
			$remains = array_slice($items, $otherNum + 1); //合算する商品を取り出す
			foreach($remains as $itm){
				$otherItem->setTotalPrice((int)$otherItem->getTotalPrice() + (int)$itm->getTotalPrice());
			}
			$items = array_slice($items, 0, $otherNum + 1);
			$items[] = $otherItem;
		}
		if(ORDER_TEMPLATE !== "jungle" && count($items) < 10){
			for($i = count($items) + 1; $i <= $otherNum + 2; $i++){
				$items[] = new SOYShop_ItemOrder();
			}
		}

		$this->createAdd("item_detail", "InvoiceItemListComponent", array(
			"list" => $items,
		));

		/*** ショップ情報 ***/
		self::buildCompanyArea();
	}

	/*** 注文概要 ***/
	protected function buildOrderArea($order){
		$this->addLabel("order_id", array(
			"text" => $order->getTrackingNumber()
		));

		$this->addLabel("order_date", array(
			"text" => date('Y-m-d', $order->getOrderDate())
		));

		$this->addLabel("order_time", array(
			"text" => date("H:i", $order->getOrderDate())
		));

		$this->addLabel("create_date", array(
			"text" => date('Y-m-d', time())
		));

		$this->addLabel("create_date", array(
			"text" => date('Y-m-d', time())
		));

		$this->addLabel("subtotal_price", array(
			"text" => number_format($this->getTotalPrice($order->getId()))
		));

		$this->addLabel("order_total_price", array(
			"text" => number_format($order->getPrice())
		));
	}

	/*** お届け先 ***/
	protected function buildSendArea($order){
		$customer = self::getCustomer($order->getUserId());
		$address = $order->getAddressArray();

		//お届け先の郵便番号
		$this->addLabel("customer_zip_code", array(
			"text" => (isset($address["zipCode"])) ? $address["zipCode"] : ""
		));

		//お届け先の都道府県
		$this->addLabel("customer_area", array(
			"text" => (isset($address["area"])) ? SOYShop_Area::getAreaText($address["area"]) : ""
		));

		//お届け先の住所1
		for($i = 1; $i <= 3; $i++){
			$this->addLabel("customer_address" . $i, array(
				"text" => (isset($address["address" . $i])) ? $address["address" . $i] : ""
			));
		}

		//お届け先の法人名
		$this->addLabel("customer_office", array(
			"text" => (isset($address["office"])) ? $address["office"] : ""
		));

		//お届け先の人名
		$this->addLabel("customer_name", array(
			"text" => (isset($address["name"])) ? $address["name"] : ""
		));
	}

	/*** 顧客情報 ***/
	protected function buildClaimedArea($order){
		$claimedAddress = $order->getClaimedAddressArray();

		//注文者住所の郵便番号
		$this->addLabel("zip_code", array(
			"text" => (isset($claimedAddress["zipCode"])) ? $claimedAddress["zipCode"] : ""
		));

		//注文者住所の都道府県
		$this->addLabel("area", array(
			"text" => (isset($claimedAddress["area"])) ? SOYShop_Area::getAreaText($claimedAddress["area"]) : ""
		));

		//注文者の住所
		for($i = 1; $i <= 3; $i++){
			$this->addLabel("address" . $i, array(
				"text" => (isset($claimedAddress["address" . $i])) ? $claimedAddress["address" . $i] : ""
			));
		}

		//注文者の法人名
		$this->addLabel("office", array(
			"text" => (isset($claimedAddress["office"])) ? $claimedAddress["office"] : ""
		));

		//注文者の名前
		$this->addLabel("name", array(
			"text" => (isset($claimedAddress["name"])) ? $claimedAddress["name"] : ""
		));
	}

	/*** ショップ情報 ***/
	protected function buildCompanyArea(){
		$config = SOYShop_ShopConfig::load();
		$company = $config->getCompanyInformation();

		$this->addLabel("shop_name", array(
			"text" => $config->getShopName()
		));

		$this->addLabel("shop_url", array(
			"text" => soyshop_get_site_url(true)
		));

		$this->addLabel("company_name", array(
			"text" => (isset($company["name"])) ? $company["name"] : ""
		));

		$this->addLabel("company_area", array(
			"text" => ""
		));

		//address1にzipCodeが入ってしまっている
		$this->addLabel("company_zip_code", array(
			"text" => (isset($company["address1"])) ? $company["address1"] : ""
		));

		$this->addLabel("company_address", array(
			"text" => (isset($company["address2"])) ? $company["address2"] : ""
		));

		$this->addModel("is_campany_telephone", array(
			"visible" => (isset($company["telephone"]) && strlen($company["telephone"]))
		));

		$this->addLabel("company_telephone", array(
			"text" => (isset($company["telephone"])) ? $company["telephone"] : ""
		));

		$this->addModel("is_campany_fax", array(
			"visible" => (isset($company["fax"]) && strlen($company["fax"]))
		));

		$this->addLabel("company_fax", array(
			"text" => (isset($company["fax"])) ? $company["fax"] : ""
		));

		$this->addModel("is_campany_mailaddress", array(
			"visible" => (isset($company["mailaddress"]) && strlen($company["mailaddress"]))
		));

		$this->addLabel("company_mailaddress", array(
			"text" => (isset($company["mailaddress"])) ? $company["mailaddress"] : ""
		));
	}

	protected function getItemOrders($itemOrders, $orderId){
		//ordersが空の時は再度取得する
		if(count($itemOrders) === 0){
			try{
				//一件しか取得できないのがちらほらあるので、再度コンストラクトすることにした
				$itemOrders = SOY2DAOFactory::create("order.SOYShop_ItemOrderDAO")->getByOrderId($orderId);
			}catch(Exception $e){
				$itemOrders = array();
			}
		}

		return $itemOrders;
	}

	protected function getCustomer($id){
		SOY2DAOFactory::importEntity("user.SOYShop_User");
		SOY2DAOFactory::importEntity("config.SOYShop_Area");

		try{
			$customer = self::userDao()->getById($id);
		}catch(Exception $e){
			$customer = new SOYShop_User();
			$customer->setName("[deleted]");
		}

		return $customer;
	}

	private function insertDaibikiModule($modules){
		//代引き支払がない場合は最初の値に追加
		if(!array_key_exists("payment_daibiki", $modules)){
			$module = new SOYShop_ItemModule();
			$module->setId("payment_daibiki");
			$module->setName("代金引換手数料");
			$module->setPrice(0);
			$module->setIsVisible(true);
			array_unshift($modules, $module);
		}

		return $modules;
	}

	/**
	 * @return object#SOYShop_ItemOrder
	 * @param orderId
	 */
	function getTotalPrice($orderId){

		try{
			return self::itemOrderDao()->getTotalPriceByOrderId($orderId);
		}catch(Exception $e){
			return 0;
		}
	}

	private function orderLogic(){
		static $logic;
		if(is_null($logic)) $logic = SOY2Logic::createInstance("logic.order.OrderLogic");
		return $logic;
	}
	private function itemOrderDao(){
		static $dao;
		if(is_null($dao)) $dao = SOY2DAOFactory::create("order.SOYShop_ItemOrderDAO");
		return $dao;
	}
	private function userDao(){
		static $dao;
		if(is_null($dao)) $dao = SOY2DAOFactory::create("user.SOYShop_UserDAO");
		return $dao;
	}

	function setOrderId($orderId){
		$this->orderId = $orderId;
	}
}
