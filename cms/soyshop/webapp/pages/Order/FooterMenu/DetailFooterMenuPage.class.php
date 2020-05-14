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
		$user = soyshop_get_user_object($order->getUserId());

		/*** メール送信フォームの生成 ***/
		DisplayPlugin::toggle("mail", $user->isUsabledEmail());

    	$mailTypes = ($user->isUsabledEmail()) ? SOYShop_Order::getMailTypes() : array();
		if(is_array($mailTypes) && count($mailTypes)){
			$mailStatus = $order->getMailStatusList();
			foreach($mailTypes as $type){
		    	$this->addLabel($type . "_mail_status", array(
		    		"text" => (isset($mailStatus[$type])) ? date("Y-m-d H:i:s", $mailStatus[$type]) : "未送信"
		    	));

		    	$this->addLink($type . "_mail_link", array(
		    		"link" => SOY2PageController::createLink("Order.Mail." . $order->getId() . "?type=" . $type)
		    	));
	    	}

			$this->createAdd("mail_plugin_list", "_common.Plugin.MailPluginListComponent", array(
	    		"list" => self::_getMailPluginList(),
	    		"status" => $mailStatus,
	    		"orderId" => $order->getId()
	    	));
		}
	}

	private function _getMailPluginList(){
    	SOYShopPlugin::load("soyshop.order.detail.mail");
    	$mailList = SOYShopPlugin::invoke("soyshop.order.detail.mail", array())->getList();
		if(!count($mailList)) return array();

    	$list = array();
    	foreach($mailList as $values){
    		if(!is_array($values)) continue;
   			foreach($values as $value){
   				$list[] = $value;
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
		$user = soyshop_get_user_object($order->getUserId());

		$mailLogs = ($user->isUsabledEmail()) ? self::_getMailHistories($order->getId()) : array();
		DisplayPlugin::toggle("mail_history", count($mailLogs));
		$this->createAdd("mail_history_list", "_common.Order.MailHistoryListComponent", array(
    		"list" => $mailLogs
    	));
	}

	private function _getMailHistories($orderId){
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
