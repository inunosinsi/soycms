<?php

class StockLogic extends SOY2LogicBase{
	
	private $orderDao;
	
	function __construct(){
		$this->orderDao = SOY2DAOFactory::create("order.SOYShop_OrderDAO");
	}
	
	function getCountShipsWaiting($itemId){
		if(!isset($itemId)) return 0;
		
		$sql = "SELECT COUNT(o.id) AS count FROM soyshop_orders os ".
				"INNER JOIN soyshop_order o ".
				"ON os.order_id = o.id ".
				"WHERE os.item_id = :itemId ".
				//ステータスが発送済み(4)よりも下、もしくは在庫確認中(6)
				"AND (" .
					"o.order_status < " . SOYShop_Order::ORDER_STATUS_SENDED . " " .
					"OR o.order_status = " . SOYShop_Order::ORDER_STATUS_STOCK_CONFIRM . " ".
				")";
		
		/**
		 * @memo もともとは入金確認待ちの要件で開発が始まりましたが、
		 * 		 発送していない商品がいくつあるか？が重要で、支払確認済みにしていなくても発送してしまえば個数を下げて良いとのことで、
		 * 		 現時点では支払状態の確認は不要と判断し発送状況のみで実装している
		 */
				
		try{
			$res = $this->orderDao->executeQuery($sql, array(":itemId" => $itemId));
		}catch(Exception $e){
			return 0;
		}
		
		return (isset($res[0]["count"])) ? (int)$res[0]["count"] : 0;
	}
}
?>