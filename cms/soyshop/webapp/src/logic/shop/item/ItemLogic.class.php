<?php

class ItemLogic extends SOY2LogicBase{

	private $errors = array();

    function validate(SOYShop_Item $obj){

		$dao = self::getItemDAO();
		$errors = array();

		if(strlen($obj->getName()) < 1){
			$errors["name"] = MessageManager::get("ERROR_REQUIRE");
		}

		if(strlen($obj->getCode()) < 1){
			$errors["code"] = MessageManager::get("ERROR_REQUIRE");
		}else{
			//重複チェック
			try{
				$tmp = $dao->getByCode($obj->getCode());
				if($tmp->getId() !== $obj->getId()){
					$errors["code"] = MessageManager::get("ERROR_DUPLICATED");
				}
			}catch(Exception $e){
				//ok
			}
		}

		if(strlen($obj->getAlias()) > 0){

			//重複チェック
			$tmp = $dao->checkAlias($obj->getAlias());

			if(count($tmp) > 0){
				if(count($tmp) > 1){
					$errors["alias"] = MessageManager::get("ERROR_DUPLICATED");
				}else if($tmp[0]->getId() !== $obj->getId()){
					$errors["alias"] = MessageManager::get("ERROR_DUPLICATED");
				}
			}
		}

		$this->setErrors($errors);

		return (empty($errors));
    }

    function update(SOYShop_Item $obj, $alias = null){
		$dao = self::getItemDAO();

		//設定しない限りaliasはそのまま
		$obj->setAlias($alias);

		try{
			$dao->update($obj);
		}catch(Exception $e){
			var_dump($e);
		}

    }

    function setAttribute($id, $key, $value){
    	$dao = $this->getItemAttributeDAO();
    	$dao->delete($id,$key);

    	$obj = new SOYShop_ItemAttribute();
		$obj->setItemId($id);
		$obj->setFieldId($key);
		$obj->setValue($value);

		$dao->insert($obj);
    }

    function delete($ids){
    	if(!is_array($ids)) $ids = array($ids);

    	$dao = self::getItemDAO();

    	$dao->begin();

    	foreach($ids as $id){
    		//商品がおすすめ商品登録してある場合は、おすすめ商品設定を解除
    		$recommend = SOYShop_DataSets::get("item.recommend_items", array());
			if(array_search($id, $recommend) !==false){
				$index = array_search($id, $recommend);
				unset($recommend[$index]);
				SOYShop_DataSets::put("item.recommend_items", $recommend);
			}

			try{
				$item = $dao->getById($id);
			}catch(Exception $e){
				continue;
			}

			//削除用のデータを作成
			$itemCode = $item->getCode();
			for($i=0; $i<=100; $i++){
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
				continue;
			}
    	}
    	$dao->commit();
    }

    function create(SOYShop_Item $obj){
		$dao = self::getItemDAO();

		$obj->setAttribute("image_small", soyshop_get_item_sample_image());
		$obj->setAttribute("image_large", soyshop_get_item_sample_image());

		return $dao->insert($obj);
    }

    function getErrors() {
    	return $this->errors;
    }
    function setErrors($errors) {
    	$this->errors = $errors;
    }

	/**
	 * 公開状態を変更する
	 */
    function changeOpen($itemIds, $status){
    	if(!is_array($itemIds)) $itemIds = array($itemIds);
    	$status = (int)(boolean)$status;	//0 or 1

    	$dao = SOY2DAOFactory::create("shop.SOYShop_ItemDAO");
    	$dao->begin();

    	foreach($itemIds as $id){
			$dao->updateIsOpen($id, (int)$status);
    	}

    	$dao->commit();
    }

    function getItemAttributeDAO(){
    	static $dao;
    	if(!$dao) $dao = SOY2DAOFactory::create("shop.SOYShop_ItemAttributeDAO");
    	return $dao;
    }

    private function getItemDAO(){
    	static $dao;
    	if(is_null($dao)) $dao = SOY2DAOFactory::create("shop.SOYShop_ItemDAO");
    	return $dao;
    }

    //マルチカテゴリモード
    function updateCategories($categories, $itemId){
    	$dao = SOY2DAOFactory::create("shop.SOYShop_CategoriesDAO");
    	try{
    		$dao->deleteByItemId($itemId);
    	}catch(Exception $e){
    		//
    	}

    	foreach($categories as $categoryId){
    		$obj = new SOYShop_Categories();
    		$obj->setItemId($itemId);
    		$obj->setCategoryId($categoryId);
    		try{
    			$dao->insert($obj);
    		}catch(Exception $e){
    			//
    		}
    	}
    }

    /** ダミー商品をたくさん追加 **/
    function createDummyItems($count = 100){
    	$dao = self::getItemDAO();

    	for($i = 0; $i < $count; $i++){
    		$code = self::createDummyCode();
    		$obj = new SOYShop_Item();
    		$obj->setName("ダミー" . $code);
    		$obj->setCode("dummy-" . $code);
			$obj->setStock(100);
			$obj->setPrice(100);
			try{
				$dao->insert($obj);
			}catch(Exception $e){
				//
			}
    	}
    }

    const RAND_MIN = 10000;
    const RAND_MAX = 99999;

    private function createDummyCode(){
    	$dao = self::getItemDAO();

    	//被らない値を取得するまで何度も取得する
    	for(;;){
    		$code = mt_rand(self::RAND_MIN, self::RAND_MAX);
    		try{
	    		$item = $dao->getByCode("dummy-" . $code);
	    		if(is_null($item->getId())) return $code;
	    	}catch(Exception $e){
	    		return $code;
	    	}
    	}
    }

	function getItemPriceListByItemId($itemId){
		static $list;
		if(isset($list[$itemId])) return $list[$itemId];
		$list = array();

		$item = soyshop_get_item_object($itemId);

		//価格一覧を格納する配列
		$prices = array();

		//商品に紐付いた価格
		$prices[] = array("label" => "定価", "price" => soyshop_check_price_string($item->getAttribute("list_price")));
		$price = soyshop_check_price_string($item->getPrice());
		$prices[] = array("label" => "通常価格", "price" => $price);
		$salePrice = soyshop_check_price_string($item->getSalePrice());
		if($salePrice > 0 && $price != $salePrice) $prices[] = array("label" => "セール価格", "price" => $salePrice);

		//会員特別価格の拡張ポイント
		SOYShopPlugin::load("soyshop.add.price");
		$priceList = SOYShopPlugin::invoke("soyshop.add.price", array(
			"mode" => "confirm",
			"item" => $item
		))->getPriceList();

		if(is_array($priceList) && count($priceList)){
			foreach($priceList as $moduleId => $extPrices){
				$prices = array_merge($prices, $extPrices);
			}
		}

		$list[$itemId] = $prices;
		return $list[$itemId];
	}

	function getStockListByItemIds($itemIds){
		if(!count($itemIds)) return array();

		$stocks = array();

		$res = self::getItemDAO()->getStockTotalListByItemIds($itemIds);
		if(count($res)){
			foreach($res as $itemId => $stock){
				$stocks[$itemId] = $stock;
			}
		}

		if(count($stocks) === count($itemIds)) return $stocks;

		foreach($stocks as $itemId => $stock){
			$idx = array_search($itemId, $itemIds);
			unset($itemIds[$idx]);
			$itemIds = array_values($itemIds);
		}

		//商品グループの場合
		$res = self::getItemDAO()->getChildStockListByItemIds($itemIds);
		if(count($res)){
			foreach($res as $itemId => $stock){
				$stocks[$itemId] = $stock;
			}
		}

		//高速化の為に最後に0で埋めておく
		foreach($itemIds as $itemId){
			if(!isset($stocks[$itemId])) $stocks[$itemId] = 0;
		}

		return $stocks;
	}

	function getItemNameListByIds($ids){
		if(!is_array($ids) || !count($ids)) return array();

		return SOY2DAOFactory::create("shop.SOYShop_ItemDAO")->getItemNameListByIds($ids);
	}

	function getLatestRegisteredItem(){
		try{
			return self::getItemDAO()->getLatestRegisteredItem(time());
		}catch(Exception $e){
			return new SOYShop_Item();
		}
	}
}
