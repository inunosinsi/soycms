<?php

class PayJpRecurringCart extends SOYShopCartBase{

	function doOperation(){

		//カートに入れる商品は必ず一つ
		$cart = CartLogic::getCart();
		if(count($cart->getItems())) {
			foreach($cart->getItems() as $index => $item){
				$cart->removeItem($index);
			}
		}
		$cart->save();
	}

	//数量調整
	function afterOperation(CartLogic $cart){

		//カートに入っている商品の数量は必ず一つ
		$items = $cart->getItems();
		if(count($items)){
			foreach($items as $index => $item){
				$items[$index]->setItemCount(1);
				$items[$index]->setTotalPrice($items[$index]->getItemPrice());
			}

			$cart->setItems($items);
		}

		//必ずカート01を開く様に設定
		$cart->setAttribute("page", "Cart01");

		//消費税の計算とモジュールの登録
		$cart->calculateConsumptionTax();
		$cart->save();
	}
}
SOYShopPlugin::extension("soyshop.cart", "payment_pay_jp_recurring", "PayJpRecurringCart");
