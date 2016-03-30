<?php

class PurchasedCheckLogic extends SOY2LogicBase{
	
	private $userId;
	private $orderDao;
	private $isPaid;
	
	function PurchasedCheckLogic(){
		$this->orderDao = SOY2DAOFactory::create("order.SOYShop_OrderDAO");
		SOY2::import("module.plugins.common_purchase_check.util.PurchaseCheckUtil");
		$config = PurchaseCheckUtil::getConfig();
		$this->isPaid = (isset($config["paid"]) && $config["paid"] == 1);
	}
	
	function checkPurchased($itemId){
		if(isset($this->userId)){
			$sql = "SELECT o.id FROM soyshop_order o ".
					"INNER JOIN soyshop_orders os ".
					"ON o.id = os.order_id ".
					"WHERE o.user_id = :userId ".
					"AND o.order_status >= " . SOYShop_Order::ORDER_STATUS_REGISTERED . " ".
					"AND o.order_status < " . SOYShop_Order::ORDER_STATUS_CANCELED . " ";
			if($this->isPaid){
				$sql .= "AND o.payment_status IN (" . SOYShop_Order::PAYMENT_STATUS_CONFIRMED . ", " . SOYShop_Order::PAYMENT_STATUS_DIRECT . ") ";
			}	
			$sql .=	"AND os.item_id = :itemId";
			$binds = array(":userId" => $this->userId, ":itemId" => $itemId);
			try{
				$results = $this->orderDao->executeQuery($sql, $binds);
			}catch(Exception $e){
				return false;
			}
			
			if(count($results) > 0) return true;
		}
		
		return false;
	}
	
	function setUserId($userId){
		$this->userId = $userId;
	}
}
?>