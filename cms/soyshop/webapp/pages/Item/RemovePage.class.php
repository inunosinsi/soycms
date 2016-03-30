<?php

class RemovePage extends WebPage{

	function RemovePage($args) {

		if(soy2_check_token()){
			$id = $args[0];
			$dao = SOY2DAOFactory::create("shop.SOYShop_ItemDAO");
			
			//商品がおすすめ商品登録してある場合は、おすすめ商品設定を解除
			$recommend = SOYShop_DataSets::get("item.recommend_items", array());
			if(array_search($id,$recommend) !== false){
				$index = array_search($id, $recommend);
				unset($recommend[$index]);
				SOYShop_DataSets::put("item.recommend_items", $recommend);
			}
			
			try{
				$item = $dao->getById($id);
			}catch(Exception $e){
				SOY2PageController::jump("Item?error");
			}
			
			//削除用のデータを作成
			$itemCode = $item->getCode();
			for($i = 0; $i <= 100; $i++){
    			try{
    				$checkItemCode = $dao->getByCode($itemCode . "_delete_" . $i);
    			}catch(Exception $e){
    				$deleteItemCode = $itemCode . "_delete_" . $i;
    				break;
    			}
    		}
    		
    		$itemAlias = $item->getAlias();
			for($j = 0; $j <= 100; $j++){
    			try{
    				$checkItemAlias = $dao->getByAlias($itemAlias . "_delete_" . $i);
    			}catch(Exception $e){
    				$deleteItemAlias = $itemAlias . "_delete_" . $i;
    				break;
    			}
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
?>