<?php

class CommonOrderDateCustomfieldMailModule extends SOYShopOrderMail{

	/**
	 * メール本文を取得
	 * @return string
	 */
	function getMailBody(SOYShop_Order $order){
			
		$dao = SOY2DAOFactory::create("order.SOYShop_OrderDateAttributeDAO");
		$list = SOYShop_OrderDateAttributeConfig::load();
		$array = array();
		//リストの再配列
		foreach($list as $obj){
			$array[$obj->getFieldId()]["label"] = $obj->getLabel();
			$array[$obj->getFieldId()]["type"] = $obj->getType();
		}
		if(count($array) === 0) return;
		$list = $array;
		
		//注文の時は新しい注文番号を取得して、管理画面からメールを送信する際は今の注文から注文IDを取得する
		$orderId = (isset($_GET["type"]) || !is_null($order->getId())) ? $order->getId() : $this->getNewOrderId();
		
		try{
			$attributes = $dao->getByOrderId($orderId);
		}catch(Exception $e){
			$attributes = array();
		}
			
		$array = array();
		foreach($attributes as $obj){
			if(strlen($obj->getValue1()) > 0){
				$res = array();
				$res[] = $list[$obj->getFieldId()]["label"];
				
				switch($list[$obj->getFieldId()]["type"]){
					case SOYShop_OrderDateAttribute::CUSTOMFIELD_TYPE_DATE:
						$res[] = $this->getTimeText($obj->getValue1());
						break;
					case SOYShop_OrderDateAttribute::CUSTOMFIELD_TYPE_PERIOD:
						$res[] = $this->getTimeText($obj->getValue1()) . " ～ " . $this->getTimeText($obj->getValue2());
						break;
				}
				$res[] = "";
				
				$array[] = implode("\n", $res);
			}
		}			
		return "\n" . implode("\n", $array);		
	}

	function getDisplayOrder(){
		return 200;//delivery系は200番台
	}
	
	function getTimeText($value){
		return date("Y", $value) . "-" . date("m", $value) . "-" . date("d", $value);
	}
	
	//最新の注文IDを取得する
	function getNewOrderId(){
		$dao = SOY2DAOFactory::create("order.SOYShop_OrderDAO");
		
		$sql = "SELECT id "
			  ."FROM soyshop_order "
			  ."WHERE order_status > " . SOYShop_Order::ORDER_STATUS_INTERIM . " "
			  ."AND order_status < " . SOYShop_Order::ORDER_STATUS_CANCELED . " "
			  ."ORDER BY id desc "
			  ."LIMIT 1";
		try{
			$result = $dao->executeQuery($sql);
			$id = (int)$result[0]["id"];
		}catch(Exception $e){
			$id = null;
		}
		
		return $id;
	}
}

SOYShopPlugin::extension("soyshop.order.mail.user", "common_order_date_customfield", "CommonOrderDateCustomfieldMailModule");
SOYShopPlugin::extension("soyshop.order.mail.confirm", "common_order_date_customfield", "CommonOrderDateCustomfieldMailModule");
SOYShopPlugin::extension("soyshop.order.mail.admin", "common_order_date_customfield", "CommonOrderDateCustomfieldMailModule");
