<?php
class ShoppingMallItem extends SOYShopItemBase{

	// 商品登録をしたアカウントと異なる場合は詳細ページを表示しない
	function executeOnDetailPage($itemId){
		if(SOYMALL_SELLER_ACCOUNT){
			$adminId = (int)SOY2ActionSession::getUserSession()->getAttribute("userid");
			SOY2::import("module.plugins.shopping_mall.domain.SOYMall_ItemRelationDAO");
			try{
				$obj = SOY2DAOFactory::create("SOYMall_ItemRelationDAO")->get($itemId, $adminId);
			}catch(Exception $e){
				$obj = new SOYMall_ItemRelation();
			}
			if(is_null($obj->getItemId())) SOY2PageController::jump("Item");
		}
	}
}
SOYShopPlugin::extension("soyshop.item", "shopping_mall", "ShoppingMallItem");
