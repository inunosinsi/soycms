<?php
class CommonItemOption extends SOYShopItemOptionBase{

	function getCartAttributeId($optionId, $itemIndex, $itemId){
		return "item_option_{$optionId}_{$itemIndex}_{$itemId}";
	}

	/**
	 * カートから商品を削除した時にセッションに放り込んだ値を削除する
	 * @param integer index, object CartLogic
	 */
	function clear($index, CartLogic $cart){
		$logic = SOY2Logic::createInstance("module.plugins.common_item_option.logic.ItemOptionLogic");
		$list = $logic->getOptions();
		
		$items = $cart->getItems();
		if(isset($items[$index])){
			$itemId = $items[$index]->getItemId();
			
			foreach($list as $key => $value){
				$obj = $this->getCartAttributeId($key, $index, $itemId);
				$cart->clearAttribute($obj);
			}		
		}
	}
	
	/**
	 * 配列が一致したindexを返す
	 * カートに入れた商品がすでにカートに入っている商品と一致しているか？を調べるメソッド
	 * @param array postedOption, object CartLogic
	 * @return integer index
	 */
	function compare($postedOption, CartLogic $cart){
		$logic = SOY2Logic::createInstance("module.plugins.common_item_option.logic.ItemOptionLogic");
		$list = $logic->getOptions();
		
		$checkOptionId = null;
		
		$items = $cart->getItems();

		//比較用の配列を作成する
		$attributes = array();
		foreach($items as $index => $item){
			foreach($list as $key => $value){
				$obj = $this->getCartAttributeId($key, $index, $item->getItemId());
				$attributes[$index][$key] = $cart->getAttribute($obj);
			}
			
			$currentOptions = array_diff($attributes[$index], array(null));

			if($postedOption == $currentOptions){
				$checkOptionId = $index;
				break;
			}
		}

		return $checkOptionId;
	}
	
	/**
	 * src/base/cart/cart.phpでカートに商品を入れたときの対応
	 * オプション内容をセッションに放り込む
	 * @param integer index, object CartLogic
	 */
	function doPost($index, CartLogic $cart){
		
		if(isset($_POST["item_option"]) && is_array($_POST["item_option"]) && count($_POST["item_option"])){
			$options = $_POST["item_option"];

			$items = $cart->getItems();
			if(isset($items[$index])){
				$itemId = $items[$index]->getItemId();
				
				foreach($options as $key => $value){
					$obj = $this->getCartAttributeId($key, $index, $itemId);
					$cart->setAttribute($obj, $value);
				}			
			}
		}
	}
	
	/**
	 * 商品情報の下に表示される情報
	 * @param htmlObj, integer index
	 * @return string html
	 */
	function onOutput($htmlObj, $index){
		
		$cart = CartLogic::getCart();

		$items = $cart->getItems();
		if(!isset($items[$index])){
			return "";
		}

		$itemId = $items[$index]->getItemId();

		$logic = SOY2Logic::createInstance("module.plugins.common_item_option.logic.ItemOptionLogic");
		$list = $logic->getOptions();
		
		$html = array();
		foreach($list as $key => $values){
			$obj = $this->getCartAttributeId($key, $index, $itemId);
			$option = $cart->getAttribute($obj);

			if(strlen($option) > 0){
				$html[] = self::getOptionName($values) . ":" . $option;
			}
		}
		
		return implode("<br />", $html);
	}
	
	/**
	 * 注文確定時に商品とオプション内容を紐づける
	 * @param integer index
	 */
	function order($index){
		$cart = CartLogic::getCart();

		$items = $cart->getItems();
		if(!isset($items[$index])){
			return null;
		}
		
		$itemId = $items[$index]->getItemId();
		
		$logic = SOY2Logic::createInstance("module.plugins.common_item_option.logic.ItemOptionLogic");
		$list = $logic->getOptions();
		
		$array = array();
		foreach($list as $key => $value){
			$obj = $this->getCartAttributeId($key, $index, $itemId);
			$array[$key] = $cart->getAttribute($obj);
		}

		return (count($array) > 0) ? soy2_serialize($array) : null;
	}
	
	/**
	 * 注文確定後の注文詳細の商品情報の下に表示される
	 * @param object SOYShop_ItemOrder
	 * @return string html
	 */
	function display($item){
		
		$logic = SOY2Logic::createInstance("module.plugins.common_item_option.logic.ItemOptionLogic");
		$list = $logic->getOptions();
		
		$attributes = $item->getAttributeList();
		
		$html = array();
		foreach($attributes as $key => $value){
			if(isset($list[$key]["name"]) && strlen($value) > 0){
				$html[] = $list[$key]["name"] . " : " . $value;
			}
		}
		
		return implode("<br />", $html);
	}
	
	/**
	 * 注文詳細で登録されている商品オプションを変更できるようにする
	 */
	function edit($key){
		
		$logic = SOY2Logic::createInstance("module.plugins.common_item_option.logic.ItemOptionLogic");
		$list = $logic->getOptions();
		
		return $list[$key]["name"];
	}
	
	private function getOptionName($values){
		if(defined("SOYSHOP_PUBLISH_LANGUAGE") && SOYSHOP_PUBLISH_LANGUAGE != "jp"){
			return (isset($values["name_" . SOYSHOP_PUBLISH_LANGUAGE]) && strlen($values["name_" . SOYSHOP_PUBLISH_LANGUAGE])) ? $values["name_" . SOYSHOP_PUBLISH_LANGUAGE] : $values["name"];
		}else{
			return $values["name"];
		}
	}
}

SOYShopPlugin::extension("soyshop.item.option", "common_item_option", "CommonItemOption");
?>