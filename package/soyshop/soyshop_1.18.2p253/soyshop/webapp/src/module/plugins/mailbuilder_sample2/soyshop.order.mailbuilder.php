<?php
/*
 * soyshop.order.mailbuilder.php
 * Created: 2010/02/04
 */

class SampleMailBuilder2 extends SOYShopOrderMailBuilder{


	/**
	 * 注文者向け注文情報を作る
	 */
	public function buildOrderMailBodyForUser(SOYShop_Order $order, SOYShop_User $user){
		return $this->buildOrderMailBody($order, $user);
    }

	/**
	 * 管理者向け注文情報を作る
	 */
	public function buildOrderMailBodyForAdmin(SOYShop_Order $order, SOYShop_User $user){
		return $this->buildOrderMailBody($order, $user);
    }

	/**
	 * 注文者と管理者に同じ文面を送る
	 */
    protected function buildOrderMailBody(SOYShop_Order $order, SOYShop_User $user){

    	$logic = SOY2Logic::createInstance("logic.order.OrderLogic");
    	$orderItems = $logic->getItemsByOrderId($order->getId());

    	//支払い方法を取ってくる
    	$payment = null;
    	/*
    	//通常は支払いモジュールが文面を追加するので不要
    	$attributes = $order->getAttributeList();
    	$attr_keys = array_keys($attributes);
    	foreach($attributes as $keys => $value){
    		if(preg_match("/^payment_[a-zA-Z0-9]+\$/", $keys)){
    			$payment = $value["value"];
    			break;
    		}
    	}
    	*/

		//注文の基本情報
    	$mail = array();
    	$mail[] = "■ ご注文内容  ━━━━━━━━━━━━━━━━━━━━━━━━━━━";
    	$mail[] = "";
    	$mail[] = "   ◆ ご注文番号    " . $order->getTrackingNumber();
    	$mail[] = "   ◆ ご注文日時    ".date("Y/m/d H:i",$order->getOrderDate());
    	if(strlen($payment))
    	$mail[] = "   ◆ お支払方法    " . $payment;
    	$mail[] = "   ◆ お支払金額    ".number_format($order->getPrice())." 円";
    	$mail[] = "";

		//注文された商品
		$mail = array_merge($mail, $this->buildOrderInfo($order, $orderItems));
		$mail[] = "";
		//配送先
		$mail = array_merge($mail, $this->buildDeliveryInfo($order));
		//備考
		$mail = array_merge($mail, $this->buildMemo($order));
		$mail[] = "";
		//注文者
		$mail = array_merge($mail, $this->buildUserInfoMailBody($user));

    	return implode("\n", $mail);

    }

    /**
     * 注文内容
     * @return Array
     */
    protected function buildOrderInfo($order, $orderItems){

    	$itemDAO = SOY2DAOFactory::create("shop.SOYShop_ItemDAO");

    	$mail = array();

    	//商品のみ小計
    	$itemPrice = 0;

    	$mail[] = "   ================================================================";

		$count = 0;
    	foreach($orderItems as $orderItem){
			$mail[] = "   [品名] " . $orderItem->getItemName();
			$str   = "            税込単価:" . $this->printColumn(number_format($orderItem->getItemPrice()),"right",10)."円"
			        ."    数量:" . $this->printColumn(number_format($orderItem->getItemCount()),"right",4)
			        ."    小計:" . $this->printColumn(number_format($orderItem->getTotalPrice()),"right",10)."円";
			$mail[] = $str;

    		$itemPrice += $orderItem->getTotalPrice();

			$count++;
			if($count < count($orderItems)){
				$mail[] = "   ----------------------------------------------------------------";
			}
    	}

    	$mail[] = "   ================================================================";
    	$mail[] = "   商品合計" . $this->printColumn(number_format($itemPrice),"right",54)."円";
    	$mail[] = "   ================================================================";
    	$mail[] = "   (内　税)                                                    税込";

    	$modules = $order->getModuleList();
    	foreach($modules as $module){
    		if(!$module->isVisible()) continue;
    		$str = "   " . $this->printColumn($module->getName(),"left",30);
    		$str .= $this->printColumn(number_format($module->getPrice()),"right",32)."円";

    		$mail[] = $str;
    	}

    	$mail[] = "   ================================================================";
    	$mail[] = "   合計額". $this->printColumn(number_format($order->getPrice()),"right",56)."円";
    	$mail[] = "   ================================================================";
    	$mail[] = "";

    	return $mail;
    }

    /**
     * お届け先情報
     * @return Array
     */
    protected function buildDeliveryInfo($order){
    	$mail = array();

    	$address = $order->getAddressArray();

    	$mail[] = "■ お届け先  ━━━━━━━━━━━━━━━━━━━━━━━━━━━━";
    	$mail[] = "";
    	if(strlen($address["office"]) > 0) $mail[] = "   [" . $this->printColumn("法人名","left",14)."] " . $address["office"];
    	$mail[] = "   [" . $this->printColumn("お名前","left",14)."] " . $address["name"]." 様";
    	$mail[] = "   [" . $this->printColumn("かな","left",14)."] " . $address["reading"];
    	$mail[] = "   [" . $this->printColumn("郵便番号","left",14)."] " . $address["zipCode"];
    	$mail[] = "   [" . $this->printColumn("住所","left",14)."] " . SOYShop_Area::getAreaText($address["area"]);
    	$mail[] = $this->printColumn("","left",20) . $address["address1"];
    	$mail[] = $this->printColumn("","left",20) . $address["address2"];
    	$mail[] = "   [" . $this->printColumn("お電話番号","left",14)."] " . $address["telephoneNumber"];
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
	protected function buildUserInfoMailBody($user){
    	$logic = SOY2Logic::createInstance("logic.order.OrderLogic");

    	$mail = array();

    	$mail[] = "■ ご購入者  ━━━━━━━━━━━━━━━━━━━━━━━━━━━━";
    	$mail[] = "";
    	$mail[] = "   [" . $this->printColumn("お名前","left",14)."] " . $user->getName() ." 様";
    	$mail[] = "   [" . $this->printColumn("かな","left",14)."] " . $user->getReading();
    	$mail[] = "   [" . $this->printColumn("郵便番号","left",14)."] " . $user->getZipCode();
    	$mail[] = "   [" . $this->printColumn("住所","left",14)."] " . $user->getAreaText();
    	$mail[] = $this->printColumn("","left",20) . $user->getAddress1();
    	$mail[] = $this->printColumn("","left",20) . $user->getAddress2();
    	$mail[] = "   [" . $this->printColumn("お電話番号","left",14)."] " . $user->getTelephoneNumber();
    	if(strlen($user->getFaxNumber()))$mail[] = "   [" . $this->printColumn("FAX番号","left",14)."] " . $user->getFaxNumber();
    	$mail[] = "   [" . $this->printColumn("メールアドレス","left",14)."] " . $user->getMailAddress();
    	$mail[] = "";

    	return $mail;

	}

	/**
	 * 備考
	 * @return Array
	 */
	protected function buildMemo($order){
		$mail = array();

		$attr = $order->getAttributeList();
		if(!isset($attr["memo"])) return $mail;

		$memo = $attr["memo"];
		if(empty($memo["value"]))return $mail;

    	$mail[] = "備考：" . $memo["value"];
    	$mail[] = "";
    	$mail[] = "-----------------------------------------";
    	$mail[] = "";

		return $mail;
	}
}

SOYShopPlugin::extension("soyshop.order.mailbuilder","mailbuilder_sample2","SampleMailBuilder2");