<?php
class DiscountFreeCouponComment extends SOYShopCommentFormBase{

	function doPost(SOYShop_Order $order){

		if(isset($_POST["discountFreeCoupon"]) && strlen($_POST["discountFreeCoupon"])){
			$code = trim($_POST["discountFreeCoupon"]);
			$itemTotal = self::getItemTotalPriceByOrderId($order->getId());
			$discount = SOY2Logic::createInstance("module.plugins.discount_free_coupon.logic.DiscountFreeCouponLogic")->getDiscountPriceByCodeWithTotalPrice($code, $itemTotal);

			if($discount > 0){
				$module = new SOYShop_ItemModule();
				$module->setId("discount_free_coupon");
				$module->setName(MessageManager::get("MODULE_NAME_COUPON"));
				$module->setType("discount_module");	//typeを指定すると同じtypeのモジュールは同時使用できなくなる
				$module->setPrice(0 - $discount);//負の値
				
				$order->setPrice($order->getPrice() + $module->getPrice());

				$modules = $order->getModuleList();
				$modules["discount_free_coupon"] = $module;
				$order->setModules($modules);

				$attribute = array(
					"name" => "クーポン",
					"value" => $code,
					"hidden" => false,
					"readonly" => false
				);
				$attributes = $order->getAttributeList();
				$attributes["discount_free_coupon.code"] = $attribute;
				$order->setAttributes($attributes);

				try{
					SOY2DAOFactory::create("order.SOYShop_OrderDAO")->update($order);
					//成功したら履歴を残す
					return "クーポンコード「" . trim($_POST["discountFreeCoupon"]) . "」を登録し、注文合計を「" . $order->getPrice() . "」に変更しました。";
				}catch(Exception $e){
					//
				}
			}
		}

		return "";
	}

	private function getItemTotalPriceByOrderId($orderId){
		try{
			$itemOrders = SOY2DAOFactory::create("order.SOYShop_ItemOrderDAO")->getByOrderId($orderId);
		}catch(Exception $e){
			$itemOrders = array();
		}

		if(!count($itemOrders)) return 0;

		$total = 0;
		foreach($itemOrders as $itemOrder){
			$total += $itemOrder->getTotalPrice();
		}

		return $total;
	}

	function getForm(SOYShop_Order $order){
		SOY2::import("module.plugins.discount_free_coupon.form.DiscountFreeCouponFormPage");
		$form = SOY2HTMLFactory::createInstance("DiscountFreeCouponFormPage");
		$form->setPluginObj($this);
		$form->setOrderId($order->getId());
		$form->execute();
		return $form->getObject();
	}
}
SOYShopPlugin::extension("soyshop.comment.form", "discount_free_coupon", "DiscountFreeCouponComment");
