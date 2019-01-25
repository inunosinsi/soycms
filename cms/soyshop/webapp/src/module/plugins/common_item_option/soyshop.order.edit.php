<?php
class ItemOptionOrderEdit extends SOYShopOrderEditBase{

	function addFunc($orderId){}

	function addFuncOnAdminOrder($orderId){
		include_once(SOYSHOP_WEBAPP . "pages/Order/Register/common.php");
		$cart = AdminCartLogic::getCart();
		if($cart->getAttribute("add_mode_on_admin_order") == 1){
			$items = $cart->getItems();
			$item = end($items);

			$dao = new SOY2DAO();
			try{
				$res = $dao->executeQuery("SELECT item_value FROM soyshop_item_attribute WHERE item_id = :itemId AND item_field_id LIKE 'item_option_%'", array(":itemId" => $item->getItemId()));
			}catch(Exception $e){
				$res = array();
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
