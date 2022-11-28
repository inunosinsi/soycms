<?php
/*
 */
class ShoppingMallItemName extends SOYShopItemNameBase{

	function doPost(SOYShop_Item $item){
		if(SOYMALL_SELLER_ACCOUNT){
			$adminId = (int)SOY2ActionSession::getUserSession()->getAttribute("userid");

			SOY2::import("module.plugins.shopping_mall.domain.SOYMall_ItemRelationDAO");
			$dao = SOY2DAOFactory::create("SOYMall_ItemRelationDAO");

			$rel = new SOYMall_ItemRelation();
			$rel->setItemId($item->getId());
			$rel->setAdminId($adminId);

			try{
				$dao->insert($rel);
			}catch(Exception $e){
				//
			}
		}
	}
}
SOYShopPlugin::extension("soyshop.item.name", "shopping_mall", "ShoppingMallItemName");
