<?php
class DeliveryCoolCommon{
	
	public static function getPrice(){

		try{
			$price = SOYShop_DataSets::get("delivery.default.prices");
		}catch(Exception $e){
			$price = array();	//default
		}
		return $price;
	}

	public static function getCoolPrice(){

		try{
			$coolPrice = SOYShop_DataSets::get("delivery.cool.prices");
		}catch(Exception $e){
			$coolPrice = 0;	//default
		}

		return $coolPrice;
	}
	
	public static function getDliveryTimeConfig(){

		try{
			$times = SOYShop_DataSets::get("delivery.default.delivery_time_config");
		}catch(Exception $e){
			$times = array("希望なし", "午前中", "12時～14時", "14時～16時", "16時～18時", "18時～21時");//default
		}
		return $times;
	}
}