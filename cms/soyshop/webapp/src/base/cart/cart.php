<?php

/**
 * action の "a"
 */
if(isset($_REQUEST["a"])) {

	SOYShopPlugin::load("soyshop.item.option");
	$cart = CartLogic::getCart();

	$_item = $_count = $_index = array();

	//複数一括指定のために配列でも受け付ける
	if(isset($_REQUEST["item"])){
		if(is_array($_REQUEST["item"])){
			$_item = $_REQUEST["item"];
		}else{
			$_item[] = $_REQUEST["item"];
		}
	}
	if(isset($_REQUEST["count"])){
		if(is_array($_REQUEST["count"])){
			$_count = $_REQUEST["count"];
		}else{
			$_count[] = $_REQUEST["count"];
			//mb_convert_kana(htmlspecialchars($_REQUEST["count"], ENT_QUOTES, "UTF-8"), "a");
		}
	}
	if(isset($_REQUEST["index"])){
		if(is_array($_REQUEST["index"])){
			$_index = $_REQUEST["index"];
		}else{
			$_index[] = $_REQUEST["index"];
		}
	}

	//隠しモード 差し替え用index カートのフォームのactionで?a=add&replace_index=idx&item=intのように指定すると使える
	$replaceIdx = (isset($_REQUEST["replace_index"]) && is_numeric($_REQUEST["replace_index"])) ? $_REQUEST["replace_index"] : null;

	//カートに入っている商品に変更がある場合は、選択されているモジュールをクリアする
	$cart->clearModules();

	switch($_REQUEST["a"]) {
		case "add":
			$lastInsertedItemId = null;
			foreach($_item as $key => $item){
				$count = isset($_count[$key]) ? $_count[$key] : 1 ;
				//個数は-1以上の整数
				$count = max(-1, (int)$count);

				$res = $cart->addItem($item, $count, $replaceIdx);
				if($res){
					SOYShopPlugin::invoke("soyshop.item.option", array(
						"mode" => "post",
						"index" => (is_numeric($replaceIdx)) ? $replaceIdx : max(array_keys($cart->getItems())),
						"cart" => $cart
					));
				}

				$lastInsertedItemId = $item;
			}

			//最後にカートに入れた商品の情報を保持する
			if(is_numeric($lastInsertedItemId)) $cart->setAttribute("last_insert_item", $lastInsertedItemId);
			break;

		case "remove":
			foreach($_index as $key => $index){
				$cart->removeItem($index);
				SOYShopPlugin::invoke("soyshop.item.option", array(
					"mode" => "clear",
					"index" => $index,
					"cart" => $cart
				));
			}
			$cart->setAttribute("last_insert_item", null);
			break;

		case "update":
			foreach($_index as $key => $index){
				$count = isset($_count[$key]) ? $_count[$key] : 1 ;
				//個数は0以上の整数
				$count = max(0, (int)$count);
				$cart->updateItem($index, $count);
			}
			break;

		case "shoooot";//カートを一つの商品で満たす
			//追加
			foreach($_item as $key => $item){
				$count = 1;
				$res = $cart->addItem($item, $count);
				if($res){
					SOYShopPlugin::invoke("soyshop.item.option", array(
						"mode" => "post",
						"index" => max(array_keys($cart->getItems())),
						"cart" => $cart
					));
				}
			}

			//全て個数は1
			$items = $cart->getItems();
			foreach($items as $index => $item){
				$cart->updateItem($index, 1);
			}

			break;
	}

	//消費税の計算とモジュールの登録
	$cart->calculateConsumptionTax();

	//カートのセッションに値を保持する前に動作する
	SOYShopPlugin::load("soyshop.cart");
	SOYShopPlugin::invoke("soyshop.cart", array(
		"mode" => "afterOperation",
		"cart" => $cart
	));

	$cart->save();
}

//use cart id
$cartId = soyshop_get_cart_id();
include(SOY2::RootDir() . "cart/" . $cartId . "/cart.php");
