<?php

class OrderLogic extends SOY2LogicBase {

	private $cart;

	function __construct(){
		include_once(SOY2HTMLConfig::PageDir() . "Order/Register/common.php");
		$this->cart = AdminCartLogic::getCart();
	}

	function backup($itemOrders){
		self::removeBackup();
		if(count($itemOrders)){
			$array = array();
			foreach($itemOrders as $itemOrder){
				$values = array();
				$values["id"] = $itemOrder->getItemId();
				$values["name"] = $itemOrder->getItemName();
				$values["count"] = $itemOrder->getItemCount();
				$values["price"] = $itemOrder->getItemPrice();
				$array[] = $values;
			}

			//JSONファイルを生成する
			file_put_contents(self::getJsonFilePath(), json_encode($array));
		}
	}

	function restore(){
		if(!self::isBackupJsonFile()) return false;
		$json = file_get_contents(self::getJsonFilePath());
		$array = json_decode($json, true);
		if(!count($array)){
			self::removeBackup();
			return false;
		}

		$dao = SOY2DAOFactory::create("order.SOYShop_ItemOrderDAO");
		$itemOrders = array();
		foreach($array as $v){
			$itemOrder = new SOYShop_ItemOrder();
			$itemOrder->setItemId((int)$v["id"]);
			$itemOrder->setItemCount((int)$v["count"]);
			$itemOrder->setItemPrice((int)$v["price"]);
			$itemOrder->setTotalPrice($itemOrder->getItemPrice() * $itemOrder->getItemCount());
			$itemOrder->setItemName($v["name"]);
			$itemOrders[] = $itemOrder;
		}

		$this->cart->setItems($itemOrders);
		$this->cart->save();

		self::removeBackup();
		return true;
	}

	private function removeBackup(){
		unlink(self::getJsonFilePath());
	}

	function isBackupJsonFile(){
		return (file_exists(self::getJsonFilePath()));
	}

	private function getJsonFilePath(){
		static $path;
		if(is_null($path)){
			$dir = SOYSHOP_SITE_DIRECTORY . ".cache/json/";
			if(!file_exists($dir)) mkdir($dir);
			$path = $dir . "backup.json";
		}
		return $path;
	}
}
