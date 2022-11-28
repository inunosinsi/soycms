<?php
class ItemOptionOrderEdit extends SOYShopOrderEditBase{

	function addFunc($orderId){}

	function addFuncOnAdminOrder($orderId){
		include_once(SOYSHOP_WEBAPP . "pages/Order/Register/common.php");
		$cart = AdminCartLogic::getCart();
		if($cart->getAttribute("add_mode_on_admin_order") == 1){
			$items = $cart->getItems();
			if(!count($items)) return null;
			$item = end($items);

			$sql = "SELECT item_value FROM soyshop_item_attribute WHERE item_id = :itemId AND item_field_id LIKE 'item_option_%'";
			$dao = new SOY2DAO();
			try{
				$res = $dao->executeQuery($sql, array(":itemId" => $item->getItemId()));
			}catch(Exception $e){
				$res = array();
			}

			//親商品の方を調べる
			if(count($res) && isset($res[0]["item_value"]) && !strlen($res[0]["item_value"])){
				$parentId = soyshop_get_parent_id_by_child_id($item->getItemId());
				if($parentId > 0) {
					try{
						$res = $dao->executeQuery($sql, array(":itemId" => $parentId));
					}catch(Exception $e){
						//
					}
				}
			}

			if(count($res)){
				$isOption = false;
				foreach($res as $v){
					if(strlen($v["item_value"])){
						$isOption = true;
						break;
					}
				}


				if($isOption){
					return self::buildScript();
				}
			}
		}

		return null;
	}

	private function buildScript(){
		$html = array();
		$html[] = "<script>";
		$html[] = file_get_contents(dirname(__FILE__) . "/js/admin.js");
		$html[] = "</script>";
		return implode("\n", $html);
	}
}
SOYShopPlugin::extension("soyshop.order.edit", "common_item_option", "ItemOptionOrderEdit");
