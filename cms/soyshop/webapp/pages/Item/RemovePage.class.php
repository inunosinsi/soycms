<?php

class RemovePage extends WebPage{

	function __construct($args) {

		if(soy2_check_token()){
			$id = (int)$args[0];
			$dao = SOY2DAOFactory::create("shop.SOYShop_ItemDAO");

			//商品がおすすめ商品登録してある場合は、おすすめ商品設定を解除
			$recommend = SOYShop_DataSets::get("item.recommend_items", array());
			if(array_search($id,$recommend) !== false){
				$index = array_search($id, $recommend);
				unset($recommend[$index]);
				SOYShop_DataSets::put("item.recommend_items", $recommend);
			}

			$item = soyshop_get_item_object($id);
			if(!is_numeric($item->getId())) SOY2PageController::jump("Item?error");

			//削除用のデータを作成
			$deleteItemCode = null;
			$i = 0;
			for(;;){
				$deleteItemCode = $item->getCode() . "_delete_" . $i;
				$tmp = soyshop_get_item_object_by_code($deleteItemCode);
    			if(!is_numeric($tmp->getId())){
					break;
				}
				$i++;
    		}

			$deleteItemAlias = null;
			$i = 0;
			for(;;){
				$deleteItemAlias = $item->getAlias() . "_delete_" . $i;
    			try{
    				$_dust = $dao->getByAlias($deleteItemAlias . "_delete_" . $i);
    			}catch(Exception $e){
    				break;
    			}
				$i++;
    		}

			$itemName = $item->getName();
			$item->setName($itemName . "(削除)");
			$item->setCode($deleteItemCode);
			$item->setAlias($deleteItemAlias);
			$item->setIsDisabled(SOYShop_Item::IS_DISABLED);

			try{
				$dao->update($item);
			}catch(Exception $e){
				SOY2PageController::jump("Item?error");
			}
		}

		SOY2PageController::jump("Item?deleted");
	}
}
