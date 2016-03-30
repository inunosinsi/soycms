<?php

class DiscountLogic extends SOY2LogicBase{
	
	private $itemAttributeDao;
	const PLUGIN_ID = "discount_item_stock";
	
	function DiscountLogic(){
		if(!class_exists("DiscountItemStockUtil")) SOY2::import("module.plugins.discount_item_stock.util.DiscountItemStockUtil");
		if(!$this->itemAttributeDao) $this->itemAttributeDao = SOY2DAOFactory::create("shop.SOYShop_ItemAttributeDAO");
	}
	
	/**
	 * 割引後の金額を取得する
	 */
	function getDiscountPrice(SOYShop_Item $item){
		$price = $item->getSellingPrice();
		
		try{
			$obj = $this->itemAttributeDao->get($item->getId(), self::PLUGIN_ID);
		}catch(Exception $e){
			return $price;
		}
		
		if((int)$obj->getValue()){
			$configs = $this->sortConfigArray(DiscountItemStockUtil::getConfig(), SORT_DESC);
			$stock = $item->getStock();
			
			foreach($configs as $config){
				if($stock <= $config["stock"]){
					$price = ceil($price * (100 - $config["discount"]) / 100);
					break;
				}
			}
		}
			
		return $price;
	}
	
	/**
	 * 割引後の金額を取得する
	 */
	function getDiscountRate(SOYShop_Item $item){
		$rate = 0;
		
		try{
			$obj = $this->itemAttributeDao->get($item->getId(), self::PLUGIN_ID);
		}catch(Exception $e){
			return $rate;
		}
		
		if((int)$obj->getValue()){
			$configs = $this->sortConfigArray(DiscountItemStockUtil::getConfig(), SORT_DESC);
			$stock = $item->getStock();
			
			foreach($configs as $config){
				if($stock <= $config["stock"]){
					$rate = (int)$config["discount"];
					break;
				}
			}
		}
			
		return $rate;
	}
	
	/**
	 * データベースに放り込むための配列の整形
	 * @param Array values
	 * @return Array configs
	 */
	function convertConfigArray($values){
		
		$array = array();
		$configs = array();	//設定内容を格納する配列
		
		foreach($values as $key => $value){
			//nameが入っている配列の場合は配列を初期化
			if($key % 2 === 0){
				$array = array();
				$array["stock"] = (isset($value["stock"])) ? $value["stock"] : "";
				
			//valueの場合は保存用の配列に格納
			}else{
				$array["discount"] = (int)soyshop_convert_number($value["discount"], 0);
				$configs[] = $array;
			}
		}
		
		$configs = $this->formatConfigArray($configs);
		return $this->sortConfigArray($configs);
	}
	
	/**
	 * 配列を保存できる様に整形する
	 */
	function formatConfigArray($values){
		
		$array = array();
		
		$zeroFlag = false;
		foreach($values as $value){

			//配列に値が入っていなかった場合
			if(strlen($value["stock"]) === 0) continue;
			
			//値が0の場合は一つだけ残す
			if((int)$value["stock"] === 0){
				if($zeroFlag === true) continue;
				$zeroFlag = true;
			}
			
			$array[] = $value;
		}
		
		return $array;
	}
	
	/**
	 * 配列を価格でソートする
	 */
	function sortConfigArray($values, $condition = SORT_ASC){
		
		$array = array();
		
		$sort = array();
		foreach($values as $key => $value){
			$sort[$key] = $value["discount"];
		}
		
		array_multisort($sort, $condition, $values);
		
		return $values;
	}
}
?>