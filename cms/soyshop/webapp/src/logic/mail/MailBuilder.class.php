<?php
SOY2::import("logic.mail.SOYShop_MailBuilder");

class MailBuilder implements SOY2LogicInterface,SOYShop_MailBuilder{

	public static function getInstance($className,$args){

		SOYShopPlugin::load("soyshop.order.mailbuilder");
		$delegate = SOYShopPlugin::invoke("soyshop.order.mailbuilder");

		$builder = $delegate->getBuilder();
		if(!is_null($builder) && $builder instanceof SOYShop_MailBuilder){
			return $builder;
		}

		$className = "MailBuilder";
		return SOY2LogicBase::getInstance($className,$args);
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

		$str  = $this->printColumn("商品名","left",$itemColumnSize);
		$str .= $this->printColumn("商品コード","left");
		$str .= $this->printColumn("数量","right");
		$str .= $this->printColumn("価格","right");
		$mail[] = $str;

		$mail[] = str_repeat("-",$itemColumnSize + 30);

		foreach($orderItems as $orderItem){
			try{
				$item = $itemDAO->getById($orderItem->getItemId());
			}catch(Exception $e){
				$item = new SOYShop_Item();
				$item->setName($orderItem->getItemName());
				$item->setCode("-");
			}

			$str  = $this->printColumn($item->getOpenItemName(),"left",$itemColumnSize);
			$str .= $this->printColumn($item->getCode(),"left");
			$str .= $this->printColumn(number_format($orderItem->getItemCount())." 点");
			$str .= $this->printColumn(number_format($orderItem->getItemPrice())." 円");

			$itemPrice += $orderItem->getTotalPrice();

			$mail[] = $str;
		}

		$mail[] = "";

		$leftColumnSize = $itemColumnSize + 10;

		$mail[] = $this->printColumn("小計","right",$leftColumnSize) . $this->printColumn(number_format($itemPrice)." 円","right",20);

		$modules = $order->getModuleList();

		foreach($modules as $module){
			if(!$module->isVisible()) continue;
			$str = $this->printColumn($module->getName(),"right",$leftColumnSize);
			$str .= $this->printColumn(number_format($module->getPrice())." 円","right",20);

			$mail[] = $str;
		}

		$mail[] = $this->printColumn("合計","right",$leftColumnSize) . $this->printColumn(number_format($order->getPrice())." 円","right",20);

		return $mail;
	}

	/**
	 * お届け先情報
	 * @return Array
	 */
	private function buildDeliveryInfo($order){
		$mail = array();

		$address = $order->getAddressArray();

		$mail[] = "お届け先";
		$mail[] = "-----------------------------------------";
		if(isset($address["office"])&&strlen($address["office"]) > 0) $mail[] = $this->printColumn("法人名","left",10) . $address["office"];
		$mail[] = $this->printColumn("お名前","left",10) . $address["name"]." 様";
		if(isset($address["reading"])&&strlen($address["reading"]))$mail[] = $this->printColumn("フリガナ","left",10) . $address["reading"];
		$mail[] = $this->printColumn("郵便番号","left",10) . $address["zipCode"];
		$mail[] = $this->printColumn("住所","left",10) . SOYShop_Area::getAreaText($address["area"]).$address["address1"];
		$mail[] = $this->printColumn("","left",10) . $address["address2"];
		$mail[] = $this->printColumn("電話番号","left",10) . $address["telephoneNumber"];
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
		
		$mail[] = "ご注文者";
		$mail[] = "-----------------------------------------";
		$mail[] = $this->printColumn("お名前","left",20) . $address["name"] ." 様";
		if(isset($address["reading"])&&strlen($address["reading"]))$mail[] = $this->printColumn("フリガナ","left",20) . $address["reading"];
		$mail[] = $this->printColumn("メールアドレス","left",20) . $user->getMailAddress();
		$mail[] = $this->printColumn("電話番号","left",20) . $address["telephoneNumber"];
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
		if(!isset($attr["memo"]) OR !isset($attr["memo"]["value"]))return array();

		$memo = $attr["memo"];
		if(empty($memo["value"]))return $mail;

		$mail[] = "備考";
		$mail[] = "-----------------------------------------";
		$mail[] = $memo["value"];
		$mail[] = "";

		return $mail;
	}

	function printColumn($str,$pos = "right",$width = 10){

		$strWidth = mb_strwidth($str);

		if($pos == "right"){
			$size = max(0,$width - $strWidth);
			$return = str_repeat(" ",$size);

			return $return . $str;
		}

		else if($pos == "center"){
			$size = (int)(max(0,$width - $strWidth) / 2);
			$return = str_repeat(" ",$size);

			return $return . $str . $return;
		}

		else if($pos == "left"){
			$size = max(0,$width - $strWidth);
			$return = str_repeat(" ",$size);

			return $str . $return;
		}


		return $str;
	}
}
?>