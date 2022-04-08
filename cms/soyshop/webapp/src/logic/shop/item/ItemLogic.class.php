<?php

class ItemLogic extends SOY2LogicBase{

	private $errors = array();

    function validate(SOYShop_Item $item){

		$dao = self::_dao();
		$errors = array();

		if(strlen($item->getName()) < 1){
			$errors["name"] = MessageManager::get("ERROR_REQUIRE");
		}

		if(strlen($item->getCode()) < 1){
			$errors["code"] = MessageManager::get("ERROR_REQUIRE");
		}else{
			//重複チェック
			$tmp = soyshop_get_item_object_by_code($item->getCode());
			if(is_numeric($tmp->getId()) && $tmp->getId() !== $item->getId()){
				$errors["code"] = MessageManager::get("ERROR_DUPLICATED");
			}
		}

		if(strlen($item->getAlias()) > 0){
			$tmp = $dao->checkAlias($item->getAlias());	//重複チェック

			if(count($tmp) > 0){
				if(count($tmp) > 1){
					$errors["alias"] = MessageManager::get("ERROR_DUPLICATED");
				}else if($tmp[0]->getId() !== $item->getId()){
					$errors["alias"] = MessageManager::get("ERROR_DUPLICATED");
				}
			}
		}

		$this->setErrors($errors);

		return (empty($errors));
    }

    function update(SOYShop_Item $item, string $alias=""){
		//設定しない限りaliasはそのまま
		if(!strlen($alias)) $alias = null;
		$item->setAlias($alias);

		try{
			self::_dao()->update($item);
		}catch(Exception $e){
			var_dump($e);
		}

    }

    function setAttribute(int $itemId, string $fieldId, string $value=""){
		$attr = soyshop_get_item_attribute_object($itemId, $fieldId);
    	$attr->setValue($value);
		soyshop_save_item_attribute_object($attr);
    }

    function delete($ids){
    	if(!is_array($ids)) $ids = array($ids);

    	$dao = self::_dao();

    	$dao->begin();

    	foreach($ids as $id){
    		//商品がおすすめ商品登録してある場合は、おすすめ商品設定を解除
    		$recommend = SOYShop_DataSets::get("item.recommend_items", array());
			if(array_search($id, $recommend) !==false){
				$index = array_search($id, $recommend);
				unset($recommend[$index]);
				SOYShop_DataSets::put("item.recommend_items", $recommend);
			}

			$item = soyshop_get_item_object($id);
			if(!is_numeric($item->getId())) continue;

			//削除用のデータを作成
			$deleteItemCode = null;
			$i = 0;
			for(;;){
				$deleteItemCode = $item->getCode() . "_delete_" . $i;
				$tmp = soyshop_get_item_object_by_code($deleteItemCode);
				if(!is_numeric($tmp->getId())) break;
    			$i++;
    		}

			$deleteItemAlias = null;
    		$i = 0;
			for(;;){
				$deleteItemAlias = $item->getAlias() . "_delete_" . $i;
    			try{
    				$_dust = $dao->getByAlias($deleteItemAlias);
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
				continue;
			}
    	}
    	$dao->commit();
    }

    function create(SOYShop_Item $item){
		$dao = self::_dao();

		//一番IDの小さい詳細ページを紐付ける
		$detailPageId = SOY2Logic::createInstance("logic.site.page.PageLogic")->getOldestDetailPageId();
		if(is_numeric($detailPageId)) $item->setDetailPageId($detailPageId);

		$item->setAttribute("image_small", soyshop_get_item_sample_image());
		$item->setAttribute("image_large", soyshop_get_item_sample_image());

		return $dao->insert($item);
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

    	$dao = self::_dao();
    	$dao->begin();

    	foreach($itemIds as $id){
			$dao->updateIsOpen($id, (int)$status);
    	}

    	$dao->commit();
    }

    //マルチカテゴリモード
    function updateCategories(array $categories, int $itemId){
    	$dao = SOY2DAOFactory::create("shop.SOYShop_CategoriesDAO");
    	try{
    		$dao->deleteByItemId($itemId);
    	}catch(Exception $e){
    		//
    	}

    	foreach($categories as $categoryId){
    		$item = new SOYShop_Categories();
    		$item->setItemId($itemId);
    		$item->setCategoryId($categoryId);
    		try{
    			$dao->insert($item);
    		}catch(Exception $e){
    			//
    		}
    	}
    }

    /** ダミー商品をたくさん追加 **/
    function createDummyItems(int $count=100){
    	$dao = self::_dao();

    	for($i = 0; $i < $count; $i++){
    		$code = self::createDummyCode();
    		$item = new SOYShop_Item();
    		$item->setName("ダミー" . $code);
    		$item->setCode("dummy-" . $code);
			$item->setStock(100);
			$item->setPrice(100);
			try{
				$dao->insert($item);
			}catch(Exception $e){
				//
			}
    	}
    }

    const RAND_MIN = 10000;
    const RAND_MAX = 99999;

    private function createDummyCode(){
    	//被らない値を取得するまで何度も取得する
    	for(;;){
    		$code = mt_rand(self::RAND_MIN, self::RAND_MAX);
    		try{
	    		if(is_null(soyshop_get_item_object_by_code("dummy-" . $code)->getId())) return $code;
	    	}catch(Exception $e){
	    		return $code;
	    	}
    	}
    }

	function getItemPriceListByItemId(int $itemId){
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

	function getStockListByItemIds(array $itemIds){
		if(!count($itemIds)) return array();

		$stocks = array();

		$res = self::_dao()->getStockTotalListByItemIds($itemIds);
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
		$res = self::_dao()->getChildStockListByItemIds($itemIds);
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
		return self::_dao()->getItemNameListByIds($ids);
	}

	function getLatestRegisteredItem(){
		try{
			return self::_dao()->getLatestRegisteredItem(time());
		}catch(Exception $e){
			return new SOYShop_Item();
		}
	}

	private function _dao(){
		return soyshop_get_hash_table_dao("item");
    }
}
