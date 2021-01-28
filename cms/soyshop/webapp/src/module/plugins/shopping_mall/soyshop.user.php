<?php
class ShoppingMallUser extends SOYShopUserBase{

	// 商品登録をしたアカウントと異なる場合は詳細ページを表示しない
	function executeOnDetailPage($userId){
		if(SOYMALL_SELLER_ACCOUNT){
			$adminId = (int)SOY2ActionSession::getUserSession()->getAttribute("userid");

			$dao = new SOY2DAO();
			$sql = "SELECT id FROM soyshop_order ".
					"WHERE user_id = :userId ".
					"AND id IN (".
						"SELECT order_id FROM soyshop_orders WHERE item_id IN (".
							"SELECT item_id FROM soymall_item_relation WHERE admin_id = :adminId".
						")".
					")";
			try{
				$res = $dao->executeQuery($sql, array(":userId" => $userId, ":adminId" => $adminId));
			}catch(Exception $e){
				$res = array();
			}

			if(!isset($res[0]["id"])) SOY2PageController::jump("User");
		}
	}
}
SOYShopPlugin::extension("soyshop.user", "shopping_mall", "ShoppingMallUser");
