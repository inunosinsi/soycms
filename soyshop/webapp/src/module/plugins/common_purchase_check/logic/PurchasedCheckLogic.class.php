<?php

class PurchasedCheckLogic extends SOY2LogicBase{

	function __construct(){}

	/**
	 * @param int itemId, int userId
	 * @return boolen
	 * userIdが1以上の場合はマイページにログインしていることになる
	 */
	function checkPurchased(int $itemId, int $userId=0){
		if($userId === 0) return false;	//ログインしていない顧客の購入状況は分からない

		$dao = self::_dao();
		$sql = "SELECT o.id FROM soyshop_order o ".
				"INNER JOIN soyshop_orders os ".
				"ON o.id = os.order_id ".
				"WHERE o.user_id = :userId ".
				"AND o.order_status >= " . SOYShop_Order::ORDER_STATUS_REGISTERED . " ".
				"AND o.order_status < " . SOYShop_Order::ORDER_STATUS_CANCELED . " ";
		if(self::_isPaid()) $sql .= "AND o.payment_status IN (" . SOYShop_Order::PAYMENT_STATUS_CONFIRMED . ", " . SOYShop_Order::PAYMENT_STATUS_DIRECT . ") ";
		$sql .=	"AND os.item_id = :itemId";
		$binds = array(":userId" => $userId, ":itemId" => $itemId);
		try{
			$results = $dao->executeQuery($sql, $binds);
		}catch(Exception $e){
			return false;
		}

		return (count($results) > 0);
	}

	private function _isPaid(){
		static $isPaid;
		if(is_null($isPaid)){
			SOY2::import("module.plugins.common_purchase_check.util.PurchaseCheckUtil");
			$cnf = PurchaseCheckUtil::getConfig();
			$isPaid = (isset($cnf["paid"]) && $cnf["paid"] == 1);
		}
		return $isPaid;
	}

	private function _dao(){
		static $dao;
		if(is_null($dao)) $dao = SOY2DAOFactory::create("order.SOYShop_OrderDAO");
		return $dao;
	}
}
