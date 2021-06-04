<?php

class InvoiceListComponent extends HTMLList{

	private $config;

	protected function populateItem($order){
		if(!$order instanceof SOYShop_Order) $order = new SOYShop_Order();

		//軽減税率に対応しているか？
		$reducedTaxRateMode = OrderInvoiceCommon::checkReducedTaxRateMode($order);

		//軽減税率モードの場合は区分の項目を追加する
		$this->addModel("reduced_tax_rate_division", array("visible" => $reducedTaxRateMode));

		/*** 注文概要 ***/
		self::buildOrderArea($order);

		/*** 支払い配送情報 ***/
		$this->createAdd("module_list", "InvoiceModuleListComponent", array(
			"list" => $order->getModuleList()
		));

		$this->addLabel("document_label", array(
			"text" => (defined("ORDER_DOCUMENT_LABEL")) ? ORDER_DOCUMENT_LABEL : "納品書"
		));

		/*** お届け先 ***/
		self::buildSendArea($order);

		/*** 顧客情報 ***/
		self::buildClaimedArea($order);

		/*** 注文商品 ***/
		$items = self::_getItemOrders($order->getItems(), $order->getId(), $reducedTaxRateMode);
	   	$this->createAdd("item_detail", "InvoiceItemListComponent", array(
			"list" => $items,
			"reducedTaxRateMode" => $reducedTaxRateMode
		));

		/*** 軽減税率に関する記述 ***/
		self::buildReducedTaxRateArea($order, $items, $reducedTaxRateMode);

		/*** ショップ情報 ***/
		self::buildCompanyArea();

		/*** 振り込み情報 ***/
		self::buildPaymentArea($order);

		/*** その他メッセージ ***/
		self::buildMessageArea();
	}

	/*** 注文概要 ***/
	private function buildOrderArea(SOYShop_Order $order){
		//バーコード	使いどころは決めていない
		if(SOYShopPluginUtil::checkIsActive("generate_barcode_tracking_number")){
			if(!class_exists("GenerateBarcodeUtil")) SOY2::import("module.plugins.generate_barcode_tracking_number.util.GenerateBarcodeUtil");
			$barcodeSrc = GenerateBarcodeUtil::getBarcodeImagePath($order->getTrackingNumber() . ".jpg");
		}else{
			$barcodeSrc = null;
		}

		$this->addModel("is_barcode", array(
			"visible" => (strlen($barcodeSrc))
		));

		$this->addImage("barcode", array(
			"src" => $barcodeSrc
		));

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

		//和表記
		$this->addLabel("create_date_jp", array(
			"text" => date('Y年m月d日', time())
		));

		$this->addLabel("subtotal_price", array(
			"text" => number_format(self::getTotalPrice($order->getId()))
		));

		$this->addLabel("order_total_price", array(
			"text" => number_format($order->getPrice())
		));

		$this->addModel("order_first_order", array(
			"visible" => (isset($this->config["firstOrder"]) && $this->config["firstOrder"] == 1 && array_key_exists("order_first_order", $order->getAttributeList()))
		));
	}

	/*** お届け先 ***/
	private function buildSendArea(SOYShop_Order $order){
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

		//お届け先の住所
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
	private function buildClaimedArea(SOYShop_Order $order){
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

	private function buildReducedTaxRateArea(SOYShop_Order $order, $itemOrders, $reducedTaxRateMode){
		//軽減税率に関する記述
		$reducedTaxRateTargetItemTotal = ($reducedTaxRateMode) ? OrderInvoiceCommon::calcReducedTaxRateTargetItemTotal($itemOrders) : 0;
		$this->addModel("is_reduced_tax_rate_detail", array(
			"visible" => ($reducedTaxRateTargetItemTotal > 0)
		));

		$this->addLabel("reduced_tax_rate_subtotal_label", array(
			"text" => ConsumptionTaxUtil::getReducedTaxRate() . "%税率対象小計"
		));

		$this->addLabel("reduced_tax_rate_items_subtotal", array(
			"text" => number_format($reducedTaxRateTargetItemTotal)
		));

		$this->addLabel("reduced_tax_rate_target_tax_label", array(
			"text" => ConsumptionTaxUtil::getReducedTaxRate() . "%税率対象消費税"
		));

		$reducedTax = ConsumptionTaxUtil::calculateTax($reducedTaxRateTargetItemTotal, ConsumptionTaxUtil::getReducedTaxRate());
		$this->addLabel("reduced_tax_rate_items_tax_total", array(
			"text" => number_format($reducedTax)
		));

		$this->addLabel("reduced_tax_rate_total_label", array(
			"text" => ConsumptionTaxUtil::getReducedTaxRate() . "%税率対象合計(税込)"
		));

		$this->addLabel("reduced_tax_rate_total", array(
			"text" => number_format($reducedTaxRateTargetItemTotal + $reducedTax)
		));

		$this->addLabel("normal_tax_rate_subtotal_label", array(
			"text" => ConsumptionTaxUtil::getTaxRate() . "%税率対象小計"
		));

		$normalTaxTargetTotal = self::getNormalTaxTargetTotal($order) - $reducedTaxRateTargetItemTotal;
		$this->addLabel("normal_tax_rate_items_sub_total", array(	//soy:idでsubtotalにすると何故か動かないので、sub_totalにした
			"text" => number_format($normalTaxTargetTotal)
		));

		$this->addLabel("normal_tax_rate_target_tax_label", array(
			"text" => ConsumptionTaxUtil::getTaxRate() . "%税率対象消費税"
		));

		$normalTax = ConsumptionTaxUtil::calculateTax($normalTaxTargetTotal, ConsumptionTaxUtil::getTaxRate());
		$this->addLabel("normal_tax_rate_items_tax_total", array(
			"text" => number_format($normalTax)
		));

		$this->addLabel("normal_tax_rate_total_label", array(
			"text" => ConsumptionTaxUtil::getTaxRate() . "%税率対象合計"
		));

		$this->addLabel("normal_tax_rate_items_total", array(
			"text" => number_format($normalTaxTargetTotal + $normalTax)
		));
	}

	/*** ショップ情報 ***/
	private function buildCompanyArea(){
		static $config;
		if(is_null($config)) $config = SOYShop_ShopConfig::load();
		$company = $config->getCompanyInformation();

		/** 画像 **/
		$fDir = OrderInvoiceCommon::getFileDirectory();
		$fUrl = OrderInvoiceCommon::getFileUrl();
		foreach(array("logo", "stamp") as $t){
			$this->addModel("is_" . $t, array(
				"visible" => (isset($this->config[$t]) && strlen($this->config[$t]) && file_exists($fDir . $this->config[$t]))
			));

			$this->addModel("no_" . $t, array(
				"visible" => (!isset($this->config[$t]) || !strlen($this->config[$t]) || !file_exists($fDir . $this->config[$t]))
			));

			$this->addImage($t, array(
				"src" => (isset($this->config[$t])) ? $fUrl . $this->config[$t] : null
			));
		}

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

	/*** 振込先情報 ***/
	private function buildPaymentArea(SOYShop_Order $order){
		list($paymentId, $deliveryId) = self::getPluginIds($order);

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
	private function buildMessageArea(){
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

	private function getPluginIds(SOYShop_Order $order){
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

	private function _getItemOrders($itemOrders, $orderId, $reducedTaxRateMode){
		if(count($itemOrders) === 0){
			try{
				//一件しか取得できないのがちらほらあるので、再度コンストラクトすることにした
				$itemOrders = SOY2DAOFactory::create("order.SOYShop_ItemOrderDAO")->getByOrderId($orderId);
			}catch(Exception $e){
				$itemOrders = array();
			}
		}

		$maxLimit = ($reducedTaxRateMode) ? 5 : 10;
		if(ORDER_TEMPLATE !== "jungle" && count($itemOrders) < $maxLimit){
			for($i = count($itemOrders) + 1; $i <= $maxLimit; $i++){
				$itemOrders[] = new SOYShop_ItemOrder();
			}
		}

		return $itemOrders;
	}

	/**
	 * @return object#SOYShop_ItemOrder
	 * @param orderId
	 */
	private function getTotalPrice($orderId){
		static $dao;
		if(is_null($dao)) $dao = SOY2DAOFactory::create("order.SOYShop_ItemOrderDAO");

		try{
			return $dao->getTotalPriceByOrderId($orderId);
		}catch(Exception $e){
			return 0;
		}
	}

	private function getNormalTaxTargetTotal(SOYShop_Order $order){
		$total = self::getTotalPrice($order->getId());
		$modules = $order->getModuleList();
		if(!count($modules)) return 0;
		foreach($modules as $moduleId => $module){
			if($module->getIsInclude() || $moduleId == "consumption_tax") continue;
			$total += $module->getPrice();
		}

		return $total;
	}

	function setConfig($config){
		$this->config = $config;
	}
}
