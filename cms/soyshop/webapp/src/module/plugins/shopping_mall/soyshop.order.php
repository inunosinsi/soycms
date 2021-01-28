<?php
class ShoppingMallOrder extends SOYShopOrderBase{

	// 商品登録をしたアカウントと異なる場合は詳細ページを表示しない
	function executeOnDetailPage($orderId){
		if(SOYMALL_SELLER_ACCOUNT){
			$adminId = (int)SOY2ActionSession::getUserSession()->getAttribute("userid");

			$dao = new SOY2DAO();
			$sql = 	"SELECT id FROM soyshop_orders ".
					"WHERE order_id = :orderId ".
					"AND item_id IN (".
						"SELECT item_id FROM soymall_item_relation WHERE admin_id = :adminId".
					")";
			try{
				$res = $dao->executeQuery($sql, array(":orderId" => $orderId, ":adminId" => $adminId));
			}catch(Exception $e){
				$res = array();
			}

			if(!isset($res[0]["id"])) SOY2PageController::jump("Order");
		}
	}
}
SOYShopPlugin::extension("soyshop.order", "shopping_mall", "ShoppingMallOrder");
