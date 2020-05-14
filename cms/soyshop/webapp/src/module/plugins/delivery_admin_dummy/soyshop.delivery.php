<?php

class DeliveryAdminDummyModule extends SOYShopDelivery{

	function onSelect(CartLogic $cart){
		SOY2::import("module.plugins.delivery_admin_dummy.util.DeliveryAdminDummyUtil");
		$config = DeliveryAdminDummyUtil::getConfig();
		if(isset($config["show_description"]) && (int)$config["show_description"] === 1){
			//POSTで料金指定
			$price = 0;
			if(isset($_POST["delivery_admin_dummy_price"])){
				$price = (int)$_POST["delivery_admin_dummy_price"];
			}
			$cart->setAttribute("delivery_admin_dummy.price", $price);

			if(isset($_POST["delivery_admin_dummy_memo"]) && strlen($_POST["delivery_admin_dummy_memo"])){
				$cart->setOrderAttribute("delivery_admin_dummy.memo", "配送メモ", $_POST["delivery_admin_dummy_memo"], true);
			}


			$module = new SOYShop_ItemModule();
			$module->setId("delivery_admin_dummy");
			$module->setName("送料");
			$module->setType("delivery_module");
			$module->setPrice($price);
			$module->setIsVisible(true);
			$module->setIsInclude(false);
		}else{
			$module = new SOYShop_ItemModule();
			$module->setId("delivery_admin_dummy");
			$module->setName("送料");
			$module->setType("delivery_module");
			$module->setPrice(0);
			$module->setIsVisible(false);
			$module->setIsInclude(false);
		}

		$cart->addModule($module);
	}

	function getName(){
		SOY2::import("module.plugins.delivery_admin_dummy.util.DeliveryAdminDummyUtil");
		$config = DeliveryAdminDummyUtil::getConfig();
		return (isset($config["label"])) ? $config["label"] : "金額指定";
	}

	function getDescription(){
		SOY2::import("module.plugins.delivery_admin_dummy.util.DeliveryAdminDummyUtil");
		$config = DeliveryAdminDummyUtil::getConfig();
		if(!isset($config["show_description"]) || (int)$config["show_description"] !== 1) return "ダミーの配送モジュールです";

		$cart = $this->getCart();
		$price = (int)$cart->getAttribute("delivery_admin_dummy.price");
		$memoArray = $cart->getOrderAttribute("delivery_admin_dummy.memo");
		$memo = is_array($memoArray) && isset($memoArray["value"]) && strlen(trim($memoArray["value"])) ? trim($memoArray["value"]) : "" ;

		$hPrice = htmlspecialchars($price, ENT_QUOTES, "UTF-8");
		$hMemo = htmlspecialchars($memo, ENT_QUOTES, "UTF-8");

		$html =<<<"HTML"
<table>
<tr><th style="width:5ex">送料</th><td><input class="text-right" type="text" name="delivery_admin_dummy_price" value="{$hPrice}" style="" onfocus="this.select()"> 円</td></tr>
<tr><th style="width:5ex">メモ</th><td><textarea name="delivery_admin_dummy_memo">
{$hMemo}</textarea></td></tr>
</table>
HTML;

		return $html;
	}

	function getPrice(){
		$cart = $this->getCart();
		$price = (int)$cart->getAttribute("delivery_admin_dummy.price");
		return $price;
	}
}

//管理画面でのみ使用する
if(defined("SOYSHOP_ADMIN_PAGE") && SOYSHOP_ADMIN_PAGE){
	SOYShopPlugin::extension("soyshop.delivery", "delivery_admin_dummy", "DeliveryAdminDummyModule");
}
