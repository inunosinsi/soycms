<?php
class AdditionOption extends SOYShopItemOptionBase{

	function getCartAttributeId(int $itemIndex, int $itemId){
		return "addition_option_{$itemIndex}_{$itemId}";
	}

	function clear(int $index, CartLogic $cart){

		$items = $cart->getItems();
		if(isset($items[$index])){
			$itemId = $items[$index]->getItemId();

			$obj = $this->getCartAttributeId($index, $itemId);
			$cart->clearAttribute($obj);
		}
	}

	/**
	 * 配列が一致したindexを返す
	 */
	function compare(array $postedOption, CartLogic $cart){
		$checkOptionId = null;

		$isAddition = (isset($_POST["item_option"]["addition_option"]) && $_POST["item_option"]["addition_option"]> 0) ? $_POST["item_option"]["addition_option"] : null;

		$items = $cart->getItems();

		foreach($items as $index => $item){
			$obj = $this->getCartAttributeId($index, $item->getItemId());

			//前の商品で加算したかどうかのフラグが入っている
			$checkAddition = $cart->getAttribute($obj);

			//今回の商品を加算したい場合
			if(isset($isAddition) && $isAddition > 0){

				//比較対象の商品が加算されていた場合
				if($checkAddition){
					//念の為、addition_optionに商品IDが入っているので、セッションを取得する際に使用した商品IDと比較する
					if($isAddition == $item->getItemId()){
						//同じだった場合はindexを返す
						$checkOptionId = $index;
						break;
					}

				//falseの場合は必ず新規登録になる
				}

			//今回の商品では加算したくない場合
			}else{

				//加算フラグがfalseの場合、その商品が入っていることになる
				if(!is_null($checkAddition) && $checkAddition == false){
					$checkOptionId = $index;
					break;

				//trueの場合は必ず新規登録になる
				}
			}
		}

		return $checkOptionId;
	}

	function doPost(int $index, CartLogic $cart){

		//加算したかどうかのフラグ
		$checkAddition = false;

		$isAddition = (isset($_POST["item_option"]["addition_option"]) && $_POST["item_option"]["addition_option"]> 0) ? $_POST["item_option"]["addition_option"] : null;

		$items = $cart->getItems();
		if(isset($items[$index])){
			$itemId = $items[$index]->getItemId();
			$obj = $this->getCartAttributeId($index, $itemId);

			if(isset($isAddition) && $isAddition > 0){

				//加算を許可しているか調べる
				if(soyshop_get_item_attribute_value($itemId, "addition_option_flag", "bool")){
					$add = soyshop_get_item_attribute_value($itemId, "addition_option_price", "int");
					$addPrice = (int)$items[$index]->getItemPrice() + $add;

					//加算した値をセットする
					$items[$index]->setItemPrice($addPrice);

					//合計金額の変更を行う
					$count = $items[$index]->getItemCount();
					$items[$index]->setTotalPrice($addPrice * $count);

					$checkAddition = true;
				}
			}
		}else{
			$obj = $this->getCartAttributeId($index, 0);
		}

		//属性には次の商品の比較のために加算したか？のboolean値を入れておく
		$cart->setAttribute($obj, $checkAddition);
	}

	function onOutput($htmlObj, int $index){

		$cart = CartLogic::getCart();

		$items = $cart->getItems();
		if(!isset($items[$index])){
			return "";
		}

		$itemId = $items[$index]->getItemId();

		$html = array();
		$obj = $this->getCartAttributeId($index, $itemId);
		$attributeFlag = $cart->getAttribute($obj);

		//属性フラグがtrueだった場合、設定からテキストを取得する？
		if($attributeFlag){
			$name = soyshop_get_item_attribute_value($itemId, "addition_option_name", "string");
			$html[] = (strlen($name)) ? $name : "加算";
		}

		return implode("<br />", $html);
	}

	function addition(int $index){
		$cart = CartLogic::getCart();

		$items = $cart->getItems();
		if(!isset($items[$index])){
			return 0;
		}

		$itemId = $items[$index]->getItemId();

		$obj = $this->getCartAttributeId($index, $itemId);
		$flag = $cart->getAttribute($obj);

		return ($flag) ? 1 :0;
	}

	function display(SOYShop_ItemOrder $item){
		//加算されている場合は、加算内容を表示
		if($item->getIsAddition() != 1) return "";

		$name = soyshop_get_item_attribute_value((int)$item->getItemId(), "addition_option_name", "string");
		return (strlen($name)) ? $name : "加算";
	}
}

SOYShopPlugin::extension("soyshop.item.option", "common_addition_option", "AdditionOption");
