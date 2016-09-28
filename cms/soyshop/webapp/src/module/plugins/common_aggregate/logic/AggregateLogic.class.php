<?php

class AggregateLogic extends SOY2LogicBase{
	
	private $dao;
	
	function __construct(){
		SOY2::import("module.plugins.common_aggregate.util.AggregateUtil");
		
		if(!defined("AGGREGATE_WITHOUT_TAX")){
			
			//集計方法
			$methods = (isset($_POST["Aggregate"]["method"])) ? $_POST["Aggregate"]["method"] : array();
			
			//消費税を含めない
			define("AGGREGATE_WITHOUT_TAX", !in_array(AggregateUtil::METHOD_MODE_TAX, $methods));
			
			//手数料を含めない
			define("AGGREGATE_WITHOUT_COMMISSION", !in_array(AggregateUtil::METHOD_MODE_COMMISSION, $methods));
			
			//ポイント値引きを含めない
			define("AGGREGATE_WITHOUT_POINT", !in_array(AggregateUtil::METHOD_MODE_POINT, $methods));
			
			//クーポン等値引きを含めない
			define("AGGREGATE_WITHOUT_DISCOUNT", !in_array(AggregateUtil::METHOD_MODE_DISCOUNT, $methods));
		}
		
		$this->dao = SOY2DAOFactory::create("order.SOYShop_OrderDAO");	
	}
	
	/**
	 * @param $v SOYShop_Orderの元の配列
	 * @return 手数料等を引いた分の合計金額
	 */
	function calc($v){
		$price = (int)$v["price"];
		$modules = $this->dao->getObject($v)->getModuleList();
		
		if(!count($modules)) return $price;
		
		foreach($modules as $key => $module){
			//合算に関するもののみ
			if(!$module->getIsInclude()){
				//消費税や手数料等
				if($module->getPrice() > 0){
					if(AGGREGATE_WITHOUT_TAX && strpos($module->getType(), "tax") !== false){
						$price -= (int)$module->getPrice();
					}else if(AGGREGATE_WITHOUT_COMMISSION && (strpos($module->getType(), "delivery_") !== false || strpos($module->getType(), "payment_") !== false)){
						$price -= (int)$module->getPrice();
					}
				
				//ポイントやクーポン等
				}else if($module->getPrice() < 0){
					//ポイント
					if(AGGREGATE_WITHOUT_POINT && strpos($module->getType(), "point") !== false){
						$price -= (int)$module->getPrice();
					} else {
						//ポイント以外すべて
						if(AGGREGATE_WITHOUT_DISCOUNT && strpos($module->getType(), "point") === false){
							$price -= (int)$module->getPrice();
						}
					}
				}
			}
		}
		
		return $price;
	}
}
?>