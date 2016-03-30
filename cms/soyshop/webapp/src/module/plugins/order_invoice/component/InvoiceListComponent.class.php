<?php

class InvoiceListComponent extends HTMLList{
	
	private $itemOrderDao;
	private $userDao;
	private $itemDao;
	private $config;
	
	protected function populateItem($order){
		
		/*** 注文概要 ***/
		$this->buildOrderArea($order);	
		
		/*** 支払い配送情報 ***/
		$this->createAdd("module_list", "InvoiceModuleListComponent", array(
			"list" => $order->getModuleList()
		));

		/*** お届け先 ***/
		$this->buildSendArea($order);

		/*** 顧客情報 ***/
		$this->buildClaimedArea($order);
		
		/*** 注文商品 ***/
	   	$this->createAdd("item_detail", "InvoiceItemListComponent", array(
			"list" => $this->getItemOrders($order->getItems(), $order->getId()),
			"itemDao" => $this->itemDao
		));
		
		/*** ショップ情報 ***/
		$this->buildCompanyArea();
		
		/*** 振り込み情報 ***/
		$this->buildPaymentArea($order);
		
		/*** その他メッセージ ***/
		$this->buildMessageArea();
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
		$customer = $this->getCustomer($order->getUserId());
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
		$this->addLabel("customer_address1", array(
			"text" => (isset($address["address1"])) ? $address["address1"] : ""
		));

		//お届け先の住所2
		$this->addLabel("customer_address2", array(
			"text" => (isset($address["address2"])) ? $address["address2"] : ""
		));

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
		$this->addLabel("address1", array(
			"text" => (isset($claimedAddress["address1"])) ? $claimedAddress["address1"] : ""
		));

		//注文者の住所2
		$this->addLabel("address2", array(
			"text" => (isset($claimedAddress["address2"])) ? $claimedAddress["address2"] : ""
		));

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

		$this->addLabel("company_telephone", array(
			"text" => (isset($company["telephone"])) ? $company["telephone"] : ""
		));

		$this->addLabel("company_fax", array(
			"text" => (isset($company["fax"])) ? $company["fax"] : ""
		));

		$this->addLabel("company_mailaddress", array(
			"text" => (isset($company["mailaddress"])) ? $company["mailaddress"] : ""
		));
	}
	
	/*** 振込先情報 ***/
	protected function buildPaymentArea($order){
		list($paymentId, $deliveryId) = $this->getPluginIds($order);
		
		if(isset($this->config["payment"]) && $this->config["payment"] == 1){
			//振込先情報の取得
			$paymentCofig = SOYShop_DataSets::get($paymentId . ".text", array());
			$account = (isset($paymentCofig["account"])) ? $paymentCofig["account"] : "";
		}else{
			$account = "";
		}
		
		$this->addModel("display_payment", array(
			"visible" => (strlen($account) > 0)
		));
		
		$this->addLabel("payment", array(
			"html" => nl2br($account)
		));
	}
	
	/*** その他メッセージ ***/
	protected function buildMessageArea(){
		$title = (isset($this->config["title"])) ? $this->config["title"] : "";
		$content = (isset($this->config["content"])) ? $this->config["content"] : "";
				
		$this->addModel("display_content", array(
			"visible" => (strlen($title) > 0 && strlen($content) > 0)
		));
		
		$this->addLabel("title", array(
			"text" => $title
		));
				
		$this->addLabel("content", array(
			"html" => nl2br($content)
		));
	}
	
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
				$itemOrders = $this->itemOrderDao->getByOrderId($orderId);
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
			$customer = $this->userDao->getById($id);
		}catch(Exception $e){
			$customer = new SOYShop_User();
			$customer->setName("[deleted]");
		}
		
		return $customer;
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
	function setUserDao($userDao){
		$this->userDao = $userDao;
	}
	function setItemDao($itemDao){
		$this->itemDao = $itemDao;
	}
	function setConfig($config){
		$this->config = $config;
	}
}
?>