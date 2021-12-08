<?php
SOY2::import("logic.mail.SOYShop_MailBuilder");

class MailBuilder implements SOY2LogicInterface,SOYShop_MailBuilder{

	public static function getInstance($className, $args){

		SOYShopPlugin::load("soyshop.order.mailbuilder");
		$delegate = SOYShopPlugin::invoke("soyshop.order.mailbuilder");

		$builder = $delegate->getBuilder();
		if(!is_null($builder) && $builder instanceof SOYShop_MailBuilder){
			return $builder;
		}

		$className = "MailBuilder";
		return SOY2LogicBase::getInstance($className, $args);
	}

	/**
	 * 注文者向け
	 */
	public function buildOrderMailBodyForUser(SOYShop_Order $order, SOYShop_User $user){
		return $this->buildOrderMailBody($order, $user);
	}

	/**
	 * 管理者向け
	 */
	public function buildOrderMailBodyForAdmin(SOYShop_Order $order, SOYShop_User $user){
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
		$mail[] = "注文番号：" . $order->getTrackingNumber();
		$mail[] = "注文日時：".date("Y-m-d (D) H:i:s",$order->getOrderDate());
		$mail[] = "-----------------------------------------";

		//注文商品
		$mail[] = "";
		$mail = array_merge($mail, $this->buildOrderInfo($order, $orderItems));

		//配送先、備考
		$mail[] = "";
		$mail = array_merge($mail, $this->buildDeliveryInfo($order));
		$mail = array_merge($mail, $this->buildMemo($order));

		//注文者情報
		$mail[] = "";
		$mail = array_merge($mail, $this->buildUserInfoMailBody($order,$user));

		return implode("\n", $mail);
	}

	/**
	 * 注文内容
	 * @return Array
	 */
	private function buildOrderInfo(SOYShop_Order $order, array $orderItems){

		$mail = array();

		//商品のみ小計
		$itemPrice = 0;

		$itemColumnSize = 0;
		foreach($orderItems as $orderItem){
			$itemColumnSize = max($itemColumnSize,mb_strwidth($orderItem->getItemName()));
		}
		$itemColumnSize += "5";

		$str  = self::printColumn("商品名","left",$itemColumnSize);
		$str .= self::printColumn("商品コード","left");
		$str .= self::printColumn("数量","right");
		$str .= self::printColumn("価格","right");
		$mail[] = $str;

		$mail[] = str_repeat("-",$itemColumnSize + 30);

		foreach($orderItems as $orderItem){
			$item = soyshop_get_item_object($orderItem->getItemId());
			if(!strlen($item->getName())){
				$item->setName($orderItem->getItemName());
				$item->setCode("-");
			}

			$str  = self::printColumn($item->getOpenItemName(),"left",$itemColumnSize);
			$str .= self::printColumn($item->getCode(),"left");
			$str .= self::printColumn(number_format($orderItem->getItemCount())." 点");
			$str .= self::printColumn(number_format($orderItem->getItemPrice())." 円");

			$itemPrice += $orderItem->getTotalPrice();

			$mail[] = $str;
		}

		$mail[] = "";

		$leftColumnSize = $itemColumnSize + 10;

		$mail[] = self::printColumn("小計","right",$leftColumnSize) . self::printColumn(number_format($itemPrice)." 円","right",20);

		$modules = $order->getModuleList();

		foreach($modules as $module){
			if(!$module->isVisible()) continue;
			$str = self::printColumn($module->getName(),"right",$leftColumnSize);
			$str .= self::printColumn(number_format($module->getPrice())." 円","right",20);

			$mail[] = $str;
		}

		$mail[] = self::printColumn("合計","right",$leftColumnSize) . self::printColumn(number_format($order->getPrice())." 円","right",20);

		return $mail;
	}

	/**
	 * お届け先情報
	 * @return Array
	 */
	private function buildDeliveryInfo(SOYShop_Order $order){
		$mail = array();

		$address = $order->getAddressArray();

		$mail[] = "お届け先";
		$mail[] = "-----------------------------------------";
		if(isset($address["office"])&&strlen($address["office"]) > 0) $mail[] = self::printColumn("法人名","left",10) . $address["office"];
		$mail[] = self::printColumn("お名前","left",10) . $address["name"]." 様";
		if(isset($address["reading"])&&strlen($address["reading"]))$mail[] = self::printColumn("フリガナ","left",10) . $address["reading"];
		$mail[] = self::printColumn("郵便番号","left",10) . $address["zipCode"];
		$mail[] = self::printColumn("住所","left",10) . SOYShop_Area::getAreaText($address["area"]).$address["address1"];
		$mail[] = self::printColumn("","left",10) . $address["address2"];
		$mail[] = self::printColumn("電話番号","left",10) . $address["telephoneNumber"];
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
	private function buildUserInfoMailBody(SOYShop_Order $order, SOYShop_User $user){

		$mail = array();

		$address = $order->getClaimedAddressArray();

		$mail[] = "ご注文者";
		$mail[] = "-----------------------------------------";
		$mail[] = self::printColumn("お名前","left",20) . $address["name"] ." 様";
		if(isset($address["reading"])&&strlen($address["reading"]))$mail[] = self::printColumn("フリガナ","left",20) . $address["reading"];
		$mail[] = self::printColumn("郵便番号","left",10) . $address["zipCode"];
		$mail[] = self::printColumn("住所","left",10) . SOYShop_Area::getAreaText($address["area"]).$address["address1"];
		$mail[] = self::printColumn("","left",10) . $address["address2"];
		$mail[] = self::printColumn("メールアドレス","left",20) . $user->getMailAddress();
		$mail[] = self::printColumn("電話番号","left",20) . $address["telephoneNumber"];
		$mail[] = "";

		return $mail;

	}

	/**
	 * 備考
	 * @return Array
	 */
	private function buildMemo(SOYShop_Order $order){
		$mail = array();

		$attr = $order->getAttributeList();
		if(!isset($attr["memo"]) OR !isset($attr["memo"]["value"]))return array();

		$memo = $attr["memo"];
		if(empty($memo["value"]))return $mail;

		$mail[] = "備考";
		$mail[] = "-----------------------------------------";
		$mail[] = $memo["value"];
		$mail[] = "";

		return $mail;
	}

	private function printColumn(string $str, string $pos="right", int $width=10){

		$strWidth = mb_strwidth($str);

		if($pos == "right"){
			$size = max(0,$width - $strWidth);
			return str_repeat(" ", $size) . $str;

		} else if ($pos == "center"){
			$size = (int)(max(0,$width - $strWidth) / 2);
			$return = str_repeat(" ",$size);
			return $return . $str . $return;

		} else if ($pos == "left"){
			$size = max(0,$width - $strWidth);
			return $str . str_repeat(" ", $size);

		}

		return $str;
	}
}
