<?php

class PurchaseListComponent extends HTMLList{

	private $itemOrderDao;
	private $config;

	protected function populateItem($order){

		/*** 注文概要 ***/
		$this->buildOrderArea($order);

		/*** 支払い配送情報 ***/
		$this->createAdd("module_list", "PurchaseModuleListComponent", array(
			"list" => $order->getModuleList()
		));

		$this->addLabel("document_label", array(
			"text" => (defined("ORDER_DOCUMENT_LABEL")) ? ORDER_DOCUMENT_LABEL : "納品書"
		));

		/*** お届け先 ***/
		$this->buildSendArea($order);

		/*** 顧客情報 ***/
		$this->buildClaimedArea($order);

		/*** 注文商品 ***/
		$items = $this->getItemOrders($order->getItems(), $order->getId());
		if(ORDER_TEMPLATE !== "jungle" && count($items) < 10){
			for($i = count($items) + 1; $i <= 10; $i++){
				$items[] = new SOYShop_ItemOrder();
			}
		}
	   	$this->createAdd("item_detail", "PurchaseItemListComponent", array(
			"list" => $items,
		));

		/*** ショップ情報 ***/
		$this->buildCompanyArea();

		/*** 振り込み情報 ***/
//		$this->buildPaymentArea($order);

		/*** その他メッセージ ***/
//		$this->buildMessageArea();
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

		$this->addModel("order_first_order", array(
			"visible" => (isset($this->config["firstOrder"]) && $this->config["firstOrder"] == 1 && array_key_exists("order_first_order", $order->getAttributeList()))
		));
	}

	/*** お届け先 ***/
	protected function buildSendArea($order){
		$customer = soyshop_get_user_object($order->getUserId());
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

		//注文者の住所1
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

		/** 画像 **/
		//$fDir = OrderInvoiceCommon::getFileDirectory();
		//$fUrl = OrderInvoiceCommon::getFileUrl();
//		foreach(array("logo", "stamp") as $t){
//			$this->addModel("is_" . $t, array(
//				"visible" => (isset($this->config[$t]) && file_exists($fDir . $this->config[$t]))
//			));
//
//			$this->addModel("no_" . $t, array(
//				"visible" => (!isset($this->config[$t]) || !file_exists($fDir . $this->config[$t]))
//			));
//
//			$this->addImage($t, array(
//				"src" => (isset($this->config[$t])) ? $fUrl . $this->config[$t] : null
//			));
//		}

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

//	/*** 振込先情報 ***/
//	protected function buildPaymentArea($order){
//		list($paymentId, $deliveryId) = $this->getPluginIds($order);
//
//		if(isset($this->config["payment"]) && $this->config["payment"] == 1){
//			//振込先情報の取得
//			$paymentCofig = SOYShop_DataSets::get($paymentId . ".text", array());
//			$account = (isset($paymentCofig["account"])) ? $paymentCofig["account"] : "";
//		}else{
//			$account = "";
//		}
//
//		$this->addModel("display_payment", array(
//			"visible" => (strlen($account) > 0)
//		));
//
//		$this->addLabel("payment", array(
//			"html" => nl2br($account)
//		));
//	}
//
//	/*** その他メッセージ ***/
//	protected function buildMessageArea(){
//		$title = (isset($this->config["title"])) ? $this->config["title"] : "";
//		$content = (isset($this->config["content"])) ? $this->config["content"] : "";
//
//		$this->addModel("display_content", array(
//			"visible" => (strlen($title) > 0 && strlen($content) > 0)
//		));
//
//		$this->addLabel("title", array(
//			"text" => $title
//		));
//
//		$this->addLabel("content", array(
//			"html" => nl2br($content)
//		));
//	}

	protected function getPluginIds($order){
		$attributes = $order->getAttributeList();

		$paymentId = null;
		$deliveryId = null;

		foreach($attributes as $key => $attribute){
			if(strpos($key, "payment_") !== false){
				$paymentId = $key;
				continue;
			}

			if(strpos($key, "delivery_") !== false && strpos($key, ".") === false){
				$deliveryId = $key;
				continue;
			}

			if(isset($paymentId) && (isset($deliveryId))) break;
		}

		return array($paymentId, $deliveryId);
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

	/**
	 * @return object#SOYShop_ItemOrder
	 * @param orderId
	 */
	function getTotalPrice($orderId){

		try{
			return $this->itemOrderDao->getTotalPriceByOrderId($orderId);
		}catch(Exception $e){
			return 0;
		}
	}

	function setItemOrderDao($itemOrderDao){
		$this->itemOrderDao = $itemOrderDao;
	}
	function setConfig($config){
		$this->config = $config;
	}
}
