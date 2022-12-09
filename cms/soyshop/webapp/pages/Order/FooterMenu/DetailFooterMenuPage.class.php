<?php

class DetailFooterMenuPage extends HTMLPage{

	private $id;

	function __construct($args){
		$this->id = (isset($args[0])) ? (int)$args[0] : null;
		parent::__construct();

		$order = soyshop_get_order_object($this->id);

		self::_buildMailArea($order);
		self::_buildHistoryArea($order);
		self::_buildMailHistoryArea($order);
		self::_buildCardOperateArea($order);
	}

	private function _buildMailArea(SOYShop_Order $order){
		$isUsableEmail = soyshop_get_user_object($order->getUserId())->isUsabledEmail();

		/*** メール送信フォームの生成 ***/
		DisplayPlugin::toggle("mail", $isUsableEmail);
		
		$this->createAdd("mail_type_list", "_common.Plugin.MailPluginListComponent", array(
			"list" => ($isUsableEmail) ? self::_getMailTypeList() : array(),
			"status" => $order->getMailStatusList(),
			"orderId" => $order->getId()
		));
	}

	private function _getMailTypeList(){
		$list = array();
		foreach(SOYShop_Order::getMailTypeList() as $id => $lab){
			$list[$id] = $lab;
		}
		
		SOYShopPlugin::load("soyshop.order.detail.mail");
		$mailList = SOYShopPlugin::invoke("soyshop.order.detail.mail", array())->getList();
		if(!count($mailList)) return $list;

		foreach($mailList as $arr){
			if(!is_array($arr)) continue;
   			foreach($arr as $v){
				$list[$v["id"]] = $v["title"];
   			}
		}
		return $list;
	}

	/*** 注文状態変更の履歴 ***/
	private function _buildHistoryArea(SOYShop_Order $order){
		try{
			$histories = SOY2Logic::createInstance("logic.order.OrderLogic")->getOrderHistories($order->getId());
		}catch(Exception $e){
			$histories = array();
		}

		$this->createAdd("history_list", "_common.Order.HistoryListComponent", array(
			"list" => $histories
		));
	}

	/*** メールの送信履歴 ***/
	private function _buildMailHistoryArea(SOYShop_Order $order){
		$mailLogs = (soyshop_get_user_object($order->getUserId())->isUsabledEmail()) ? self::_getMailHistories($order->getId()) : array();
		DisplayPlugin::toggle("mail_history", count($mailLogs));
		$this->createAdd("mail_history_list", "_common.Order.MailHistoryListComponent", array(
			"list" => $mailLogs
		));
	}

	private function _getMailHistories(int $orderId){
		try{
			return SOY2DAOFactory::create("logging.SOYShop_MailLogDAO")->getByOrderId($orderId);
		}catch(Exception $e){
			return array();
		}
	}

	private function _buildCardOperateArea(SOYShop_Order $order){
		/*** カード決済操作 ***/
		SOYShopPlugin::load("soyshop.operate.credit");
		$list = SOYShopPlugin::invoke("soyshop.operate.credit", array(
			"order" => $order,
			"mode" => "order_detail",
		))->getList();
		DisplayPlugin::toggle("operate_credit_menu", (is_array($list) && count($list) > 0));

		$this->createAdd("operate_list", "_common.Order.OperateListComponent", array(
			"list" => $list
		));
	}
}
