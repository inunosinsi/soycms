<?php
/*
 * Created on 2009/07/28
 *
 * To change the template for this generated file go to
 * Window - Preferences - PHPeclipse - PHP - Code Templates
 */

class SOYShopDiscountBulkBuyingModule extends SOYShopDiscount{
	private $discount;
	
	function SOYShopDiscountBulkBuyingModule(){
		SOY2::import("module.plugins.discount_bulk_buying.util.DiscountBulkBuyingConfigUtil");
		SOY2::import("module.plugins.discount_bulk_buying.util.DiscountBulkBuyingConditionUtil");
		$this->discount = DiscountBulkBuyingConfigUtil::getDiscount();
	}
	
	function clear(){
		$cart = $this->getCart();
		$cart->removeModule("discount_bulk_buying");
		$cart->clearOrderAttribute("discount_bulk_buying");
	}
	
	/**
	 * 
	 */
	function doPost($param){
		$cart = $this->getCart();
		
		//割引対象
		if(isset($_POST["discount_module"]["discount_bulk_buying"]) && DiscountBulkBuyingConditionUtil::hasDiscountByCart($cart)){
			
			//割引額
			$amount = DiscountBulkBuyingConfigUtil::getDiscountPrice($cart->getItemPrice());
			
			//合計金額から割引
			$module = new SOYShop_ItemModule();
			$module->setId("discount_bulk_buying");
			$module->setName($this->discount["name"]);
			$module->setType("discount_module");	//typeを指定すると同じtypeのモジュールは同時使用できなくなる
			$module->setPrice(0 - $amount);//負の値
			$cart->addModule($module);
			
			//注文属性にも入れておく
			$cart->setOrderAttribute("discount_bulk_buying", $this->discount["name"], (int)$amount. "円割引");
			
		}
	}
	
	function order(){

	}
	
	
	function hasError($param){

	}
	
	function getError(){
		return false;
	}
	
	/**
	 * @return string
	 */
	function getName(){
		if($this->discount["status"] == DiscountBulkBuyingConfigUtil::STATUS_ACTIVE){
			return $this->discount["name"];
		}else{
			//公開状態が非公開ならば表示しない
			return "";
		}		
	}
	
	/**
	 * @return string 
	 */
	function getDescription(){
		if(isset($this->discount["description"]) && strlen($this->discount["description"]) > 0){
			$description = $this->discount["description"];
		}else{
			//割引額
			if($this->discount["type"] == DiscountBulkBuyingConfigUtil::TYPE_AMOUNT){
				$description = (int)$this->discount["amount"] . "円値引き";
			//割引率
			}else{
				$description = (int)$this->discount["percent"] . "%割引き";
			}
		}
		
		//公開状態のチェックを念のためにここでも行う。hiddenの値でモジュールを知らせないと拡張ポイントを通過できない仕組み
		if($this->discount["status"] == DiscountBulkBuyingConfigUtil::STATUS_ACTIVE){
			$description .= "\n<input type=\"hidden\" name=\"discount_module[discount_bulk_buying]\" value=\"1\">"; 
		}
		return $description;
	}
	
	/**
	 * 割引対象とするチェック
	 * @return boolean
	 */
	function checkAddList(){
		$cart = $this->getCart();
		$res = DiscountBulkBuyingConditionUtil::hasDiscountByCart($cart);
		return $res;
	}
	
}
SOYShopPlugin::extension("soyshop.discount", "discount_bulk_buying", "SOYShopDiscountBulkBuyingModule");
?>