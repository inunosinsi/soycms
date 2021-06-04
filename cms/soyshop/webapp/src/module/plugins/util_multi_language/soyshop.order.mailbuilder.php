<?php
/*
 * soyshop.order.mailbuilder.php
 * Created: 2010/02/04
 */

class UtilMultiLanguageMailBuilder extends SOYShopOrderMailBuilder{

	function buildOrderMailBodyForUser(SOYShop_Order $order, SOYShop_User $user){
		if(!defined("SOYSHOP_MAIL_LANGUAGE")) define("SOYSHOP_MAIL_LANGUAGE", SOYSHOP_PUBLISH_LANGUAGE);
		SOY2::import("module.plugins.util_multi_language.util.UtilMultiLanguageUtil");
		return $this->buildOrderMailBody($order, $user);
	}
	function buildOrderMailBodyForAdmin(SOYShop_Order $order, SOYShop_User $user){
		if(!defined("SOYSHOP_MAIL_LANGUAGE")) define("SOYSHOP_MAIL_LANGUAGE", SOYSHOP_PUBLISH_LANGUAGE);
		SOY2::import("module.plugins.util_multi_language.util.UtilMultiLanguageUtil");
		return $this->buildOrderMailBody($order, $user);
	}

	/**
	 * 注文情報を作る
	 */
	private function buildOrderMailBody(SOYShop_Order $order, SOYShop_User $user){
		$logic = SOY2Logic::createInstance("logic.order.OrderLogic");
		$orderItems = $logic->getItemsByOrderId($order->getId());

		$mail = array();

		$mail[] = "-----------------------------------------";
		$mail[] = UtilMultiLanguageUtil::translate("order_number") . "：" . $order->getTrackingNumber();
		$mail[] = UtilMultiLanguageUtil::translate("order_date") . "：" . date("Y-m-d (D) H:i:s", $order->getOrderDate());
		$mail[] = "-----------------------------------------";

		//注文商品
		$mail[] = "";
		$mail = array_merge($mail, self::buildOrderInfo($order, $orderItems));

		//配送先、備考
		$mail[] = "";
		$mail = array_merge($mail, self::buildDeliveryInfo($order));
		$mail = array_merge($mail, self::buildMemo($order));

		//注文者情報
		$mail[] = "";
		$mail = array_merge($mail, self::buildUserInfoMailBody($order, $user));

		return implode("\n", $mail);
	}

	/**
	 * 注文内容
	 * @return Array
	 */
	private function buildOrderInfo($order, $orderItems){

		$itemDAO = SOY2DAOFactory::create("shop.SOYShop_ItemDAO");

		$mail = array();

		//商品のみ小計
		$itemPrice = 0;

		$itemColumnSize = 0;
		foreach($orderItems as $orderItem){
			$itemColumnSize = max($itemColumnSize,mb_strwidth($orderItem->getItemName()));
		}
		$itemColumnSize += "5";

		$str  = $this->printColumn(UtilMultiLanguageUtil::translate("item_name"), "left", $itemColumnSize);
		$str .= $this->printColumn(UtilMultiLanguageUtil::translate("item_code"), "left");
		$str .= $this->printColumn(UtilMultiLanguageUtil::translate("item_count"), "right");
		$str .= $this->printColumn(UtilMultiLanguageUtil::translate("item_price"), "right");
		$mail[] = $str;

		$mail[] = str_repeat("-", $itemColumnSize + 30);

		foreach($orderItems as $orderItem){
			try{
				$item = $itemDAO->getById($orderItem->getItemId());
			}catch(Exception $e){
				$item = new SOYShop_Item();
				$item->setName($orderItem->getItemName());
				$item->setCode("-");
			}

			$str  = $this->printColumn($item->getOpenItemName(), "left", $itemColumnSize);
			$str .= $this->printColumn($item->getCode(), "left");
			$str .= $this->printColumn(number_format($orderItem->getItemCount()) . " " . UtilMultiLanguageUtil::translate("pcs"));
			$str .= $this->printColumn(number_format($orderItem->getItemPrice() * $orderItem->getItemCount()) . " " . UtilMultiLanguageUtil::translate("yen"));

			$itemPrice += $orderItem->getTotalPrice();

			$mail[] = $str;
		}

		$mail[] = "";

		$leftColumnSize = $itemColumnSize + 10;

		$mail[] = $this->printColumn(UtilMultiLanguageUtil::translate("subtotal"), "right", $leftColumnSize) . $this->printColumn(number_format($itemPrice) . " " . UtilMultiLanguageUtil::translate("yen"), "right", 20);

		SOY2::import("domain.order.SOYShop_ItemModule");
		$modules = $order->getModuleList();

		foreach($modules as $module){
			if(!$module->isVisible()) continue;
			$str = $this->printColumn($module->getName(), "right", $leftColumnSize);
			$str .= $this->printColumn(number_format($module->getPrice()) . " " . UtilMultiLanguageUtil::translate("yen"), "right", 20);

			$mail[] = $str;
		}

		$mail[] = $this->printColumn(UtilMultiLanguageUtil::translate("total"), "right", $leftColumnSize) . $this->printColumn(number_format($order->getPrice()) . " " . UtilMultiLanguageUtil::translate("yen"), "right", 20);

		return $mail;
	}

	/**
	 * お届け先情報
	 * @return Array
	 */
	private function buildDeliveryInfo($order){
		$mail = array();

		$address = $order->getAddressArray();

		$mail[] = UtilMultiLanguageUtil::translate("shipping");
		$mail[] = "-----------------------------------------";
		if(isset($address["office"])&&strlen($address["office"]) > 0) $mail[] = $this->printColumn(UtilMultiLanguageUtil::translate("office"), "left",10) . $address["office"];
		$mail[] = $this->printColumn(UtilMultiLanguageUtil::translate("name"), "left", 10) . $address["name"]. " " . UtilMultiLanguageUtil::translate("honorific");
		if(SOYSHOP_MAIL_LANGUAGE == "jp" && isset($address["reading"]) && strlen($address["reading"])){
			$mail[] = $this->printColumn(UtilMultiLanguageUtil::translate("reading"), "left", 10) . $address["reading"];
		}
		$mail[] = $this->printColumn(UtilMultiLanguageUtil::translate("zip"), "left", 10) . $address["zipCode"];
		$mail[] = $this->printColumn(UtilMultiLanguageUtil::translate("address"), "left", 10) . SOYShop_Area::getAreaText($address["area"]) . "," . $address["address1"];
		$mail[] = $this->printColumn("", "left", 10) . $address["address2"].$address["address3"];
		$mail[] = $this->printColumn(UtilMultiLanguageUtil::translate("phone"), "left", 10) . " " . $address["telephoneNumber"];
		$mail[] = "";

		return $mail;
	}

	/**
	 * 注文者情報を作る
	 *
	 * @param order_id
	 * @param user
	 * @return string
	 */
	private function buildUserInfoMailBody($order,$user){

		$mail = array();

		$address = $order->getClaimedAddressArray();

		$mail[] = UtilMultiLanguageUtil::translate("customer");
		$mail[] = "-----------------------------------------";
		$mail[] = $this->printColumn(UtilMultiLanguageUtil::translate("name"), "left", 20) . $address["name"] . " " . UtilMultiLanguageUtil::translate("honorific");
		if(SOYSHOP_MAIL_LANGUAGE == "jp" && isset($address["reading"]) && strlen($address["reading"])){
			$mail[] = $this->printColumn(UtilMultiLanguageUtil::translate("reading"), "left", 20) . $address["reading"];
		}
		$mail[] = $this->printColumn(UtilMultiLanguageUtil::translate("mailaddress"), "left", 20) . $user->getMailAddress();
		$mail[] = $this->printColumn(UtilMultiLanguageUtil::translate("phone"), "left", 20) . " " . $address["telephoneNumber"];
		$mail[] = "";

		return $mail;

	}

	/**
	 * 備考
	 * @return Array
	 */
	private function buildMemo($order){
		$mail = array();

		$attr = $order->getAttributeList();
		if(!isset($attr["memo"]) || !isset($attr["memo"]["value"])) return array();

		$memo = $attr["memo"];
		if(empty($memo["value"])) return $mail;

		$mail[] = UtilMultiLanguageUtil::translate("memo");
		$mail[] = "-----------------------------------------";
		$mail[] = $memo["value"];
		$mail[] = "";

		return $mail;
	}

	function printColumn($str, $pos = "right", $width = 10){

		$strWidth = mb_strwidth($str);

		if($pos == "right"){
			$size = max(0, $width - $strWidth);
			$return = str_repeat(" ",$size);

			return $return . $str;
		}

		else if($pos == "center"){
			$size = (int)(max(0, $width - $strWidth) / 2);
			$return = str_repeat(" ", $size);

			return $return . $str . $return;
		}

		else if($pos == "left"){
			$size = max(0, $width - $strWidth);
			$return = str_repeat(" ", $size);

			return $str . $return;
		}

		return $str;
	}
}

SOYShopPlugin::extension("soyshop.order.mailbuilder", "util_multi_language", "UtilMultiLanguageMailBuilder");
