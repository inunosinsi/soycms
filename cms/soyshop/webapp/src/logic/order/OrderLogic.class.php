<?php

class OrderLogic extends SOY2LogicBase{

	const CHANGE_STOCK_MODE_CANCEL = "cancel";	//キャンセルにした場合
	const CHANGE_STOCK_MODE_RETURN = "return";	//キャンセルから他のステータスに戻した場合

	/**
	 * 商品情報を詳細つきで取得
	 * @return SOYShop_Order
	 */
    function getById($id){
		try{
			//Orderを取得
			$order = SOY2DAOFactory::create("order.SOYShop_OrderDAO")->getById($id);
		}catch(Exception $e){
			return new SOYShop_Order();
		}

    	//ItemOrderを取得（してないし…）
    	$items = array();
    	$order->setItems($items);

    	return $order;
    }

	/**
	 * 商品情報を詳細つきで取得
	 * @param integer $id
	 * @return SOYShop_Order
	 */
	function getFullOrderById($id){
		$order = $this->getById($id);
    	$items = $this->getItemsByOrderId($id);
    	$order->setItems($items);

    	return $order;
	}

    function getTotalPrice($orderId) {
    	try{
    		return self::itemOrderDao()->getTotalPriceByOrderId($orderId);
    	}catch(Exception $e){
    		return null;
    	}
    }

	function getItemsByOrderId($orderId){
		return self::_getItemsByOrderId($orderId);
	}

	/**
	 * @param integer $itemId
	 * @return integer 商品の個数
	 */
    function getOrderCountByItemId($itemId){
    	try{
			return self::itemOrderDao()->countByItemId($itemId);
    	}catch(Exception $e){
    		return 0;
    	}
    }


	/**
	 * @param integer $orderId
	 * @return integer 商品の個数の合計
	 */
    function getTotalOrderItemCountByItemId($orderId){
    	try{
			return self::itemOrderDao()->getTotalItemCountByOrderId($orderId);
    	}catch(Exception $e){
    		return 0;
    	}
    }

	/**
	 * @param integer $orderId
	 * @return integer 商品の個数
	 */
    function getItemCountById($orderId){
    	return count(self::_getItemsByOrderId($orderId));
    }

    /**
     * 変更履歴を取得する
     */
    function getOrderHistories($id){
    	try{
    		$dao = SOY2DAOFactory::create("order.SOYShop_OrderStateHistoryDAO");
    		$dao->setOrder("id asc");
    		return $dao->getByOrderId($id);
    	}catch(Exception $e){
    		return array();
    	}
    }

    /**
     * メールのステータスを設定する
     */
    function setMailStatus($id, $type, $value){
    	$orderDAO = SOY2DAOFactory::create("order.SOYShop_OrderDAO");

    	$order = $orderDAO->getById($id);
    	$order->setMailStatusByType($type, $value);

    	$orderDAO->updateMailStatus($order);
    }

	//マイページで使用する為のメソッド
	function addHistory($id, $content, $more = null, $author = null){
		self::_addHistory($id, $content, $more, $author);
	}

    /**
     * ヒストリーに追加
     */
    private function _addHistory($id, $content, $more = null, $author = null){
		static $dao;
		if(is_null($dao)) $dao = SOY2DAOFactory::create("order.SOYShop_OrderStateHistoryDAO");
    	$history = new SOYShop_OrderStateHistory();

    	$history->setOrderId($id);
    	$history->setContent($content);
    	$history->setMore($more);

		if(isset($author) && strlen($author)) $history->setAuthor($author);
    	$dao->insert($history);
    }

    /**
     * 問い合わせ番号を生成
     *
     */
    function getTrackingNumber(SOYShop_Order $order){

    	SOYShopPlugin::load("soyshop.order.complete");
		$delegate = SOYShopPlugin::invoke("soyshop.order.complete", array(
			"mode" => "tracking_number",
			"order" => $order
		));

		if(!is_null($delegate->getTrackingNumberList()) && count($delegate->getTrackingNumberList())){
			//最初に見つけた注文番号を返す
			foreach($delegate->getTrackingNumberList() as $customTrackNum){
				if(isset($customTrackNum)) return $customTrackNum;
			}
		}

    	$orderDAO = SOY2DAOFactory::create("order.SOYShop_OrderDAO");

    	for($i = 0;;++$i){
	    	$seed = $order->getId() . $order->getOrderDate() . $i;
   	 		$hash = base_convert(md5($seed), 16, 10);
    		if($order->getId() < 100000){
    			$trackingnum = substr($hash, 2, 4) . "-" . substr($hash, 6, 4);
    		}else{
    			$trackingnum = substr($hash, 2, 4) . "-" . substr($hash, 6, 4) . "-" . substr($hash, 10, 4);
    		}

   			$trackingnum = $order->getUserId() . "-" . $trackingnum;

    		try{
	    		$tmp = $orderDAO->getByTrackingNumber($trackingnum);
    		}catch(Exception $e){
				break;
    		}
    	}

	    return $trackingnum;
    }

    /**
	 * 注文状態を変更する マイページで実行した場合はauthorに何らかの値がある
	 */
    function changeOrderStatus($orderIds, $status, $author=null){
    	if(!is_array($orderIds)) $orderIds = array($orderIds);
    	$status = (int)$status;

		SOY2::import("domain.config.SOYShop_ShopConfig");
		$isDestroyTrackingNumber = SOYShop_ShopConfig::load()->getDestroyTrackingNumberOnCancelOrder();

		SOYShopPlugin::load("soyshop.order.status.update");

    	$dao = SOY2DAOFactory::create("order.SOYShop_OrderDAO");
    	$dao->begin();

    	foreach($orderIds as $id){
			$order = soyshop_get_order_object($id);
			if(is_null($order->getId())) continue;

			//ステータスが異なる場合
			$oldStatus = $order->getStatus();
			if($oldStatus != $status){
				$order->setStatus($status);
				if(isset($author) && strlen($author)){	//マイページでの実行
					$historyContent = "注文番号『" . $order->getTrackingNumber() . "』の注文をキャンセルしました。";
					$author = "顧客：" . $author;
				}else{	//管理画面
					$historyContent = "注文状態を<strong>「" . $order->getOrderStatusText() ."」</strong>に変更しました。";
				}

				/** メール送信 **/
				if(self::_sendMailOnChangeDeliveryStatus($order, $status, $oldStatus)){
					$order->setMailStatusByType(self::_getMailStatus($status), time());
				}
				//注文番号を壊して登録
				if($isDestroyTrackingNumber) $order->setTrackingNumber(self::_destroyTrackingNumber($order));

	    		try{
	    			$dao->update($order);
	    		}catch(Exception $e){
	    			continue;
	    		}

				/** 在庫数の変更 **/

				//キャンセルの場合は紐付いた商品分だけ在庫数を戻したい
				if(self::_compareStatus($status, $oldStatus, self::CHANGE_STOCK_MODE_CANCEL)){
					self::_changeItemStock($order->getId(), self::CHANGE_STOCK_MODE_CANCEL);
				}

				//キャンセルから他のステータスに戻した場合は在庫数を減らしたい
				if(self::_compareStatus($status, $oldStatus, self::CHANGE_STOCK_MODE_RETURN)){
					self::_changeItemStock($order->getId(), self::CHANGE_STOCK_MODE_RETURN);
				}

	    		self::_addHistory($order->getId(), $historyContent, "", $author);

				//拡張ポイント
				SOYShopPlugin::invoke("soyshop.order.status.update", array(
	    			"order" => $order,
	    			"mode" => "status"
	    		));
			}
    	}

    	$dao->commit();
    }

	//注文番号を壊す
	private function _destroyTrackingNumber(SOYShop_Order $order){
		return "d" . $order->getId() . "-" . substr(md5($order->getTrackingNumber(). mt_rand(100, 999)), 0, 10);
	}

	private function _sendMailOnChangeDeliveryStatus(SOYShop_Order $order, $newStatus, $oldStatus){
		//送信前に念の為に確認
		if((int)$newStatus === (int)$oldStatus) return false;

		$sendMailType = self::_getMailStatus($newStatus);
		if(is_null($sendMailType)) return false;

		//既に送信している場合は送信しない
		$mailStatus = $order->getMailStatusByType($sendMailType);
		if(isset($mailStatus) && is_numeric($mailStatus)) return false;

		$mailLogic = SOY2Logic::createInstance("logic.mail.MailLogic");
		$mailConfig = $mailLogic->getUserMailConfig($sendMailType);
		if(!isset($mailConfig["active"]) || (int)$mailConfig["active"] !== 1) return false;

		list($mailBody, $title) = $mailLogic->buildMailBodyAndTitle($order, $sendMailType);

		//宛名
		$user = soyshop_get_user_object($order->getUserId());
		$userName = $user->getName();
		if(strlen($userName) > 0) $userName .= " 様";

		//送信
		$mailLogic->sendMail($user->getMailAddress(), $title, $mailBody, $userName, $order);

		return true;
	}

	private function _getMailStatus($status){
		static $sendMailType;
		if(is_null($sendMailType)){
			switch($status){
				case SOYShop_Order::ORDER_STATUS_SENDED:
					$sendMailType = SOYShop_Order::SENDMAIL_TYPE_DELIVERY;
					break;
				default:
					//拡張ポイントを調べる
					SOYShopPlugin::load("soyshop.order.detail.mail");
					$statusList = SOYShopPlugin::invoke("soyshop.order.detail.mail", array("mode" => "autosend"))->getList();
					if(count($statusList)){
						foreach($statusList as $mailConf){
							foreach($mailConf as $statusCode => $mailType){
								if((int)$statusCode === (int)$status){
									$sendMailType = $mailType;
								}
							}
						}
					}
			}
		}
		return $sendMailType;
	}

    /**
	 * 支払状態を変更する
	 */
    function changePaymentStatus($orderIds,$status){
    	if(!is_array($orderIds)) $orderIds = array($orderIds);
    	$status = (int)$status;

    	$dao = SOY2DAOFactory::create("order.SOYShop_OrderDAO");
    	$dao->begin();

    	foreach($orderIds as $id){
    		try{
    			$order = $dao->getById($id);
    		}catch(Exception $e){
    			continue;
    		}

    		$order->setPaymentStatus($status);
    		$historyContent = "支払い状態を<strong>「" . $order->getPaymentStatusText() ."」</strong>に変更しました。";
    		try{
    			$dao->update($order);
    		}catch(Exception $e){
    			continue;
    		}
    		self::_addHistory($id,$historyContent);
    	}

    	$dao->commit();
    }

	private function _compareStatus($newStatus, $oldStatus, $mode=self::CHANGE_STOCK_MODE_CANCEL){
		switch($mode){
			case self::CHANGE_STOCK_MODE_CANCEL:
				//キャンセルにする場合 無効注文も含む
				if($newStatus == SOYShop_Order::ORDER_STATUS_CANCELED || $newStatus == SOYShop_Order::ORDER_STATUS_INVALID){
					//前のステータスがキャンセルか返却(21)でないことを確認
					return ($oldStatus != SOYShop_Order::ORDER_STATUS_CANCELED || $oldStatus != 21);
				}

				//返却にする場合
				if($newStatus == 21){
					//前のステータスがキャンセルか返却(21)でないことを確認
					return ($oldStatus != SOYShop_Order::ORDER_STATUS_CANCELED || $oldStatus != 21);
				}
				break;
			case self::CHANGE_STOCK_MODE_RETURN:
				//キャンセルから戻す場合
				if($newStatus != SOYShop_Order::ORDER_STATUS_CANCELED){
					//前のステータスがキャンセルか返却(21)であるか確認する
					return ($oldStatus == SOYShop_Order::ORDER_STATUS_CANCELED || $oldStatus == 21);
				}

				//返却(21)から戻す場合
				if($newStatus != 21){
					//前のステータスがキャンセルか返却(21)であるか確認する
					return ($oldStatus == SOYShop_Order::ORDER_STATUS_CANCELED || $oldStatus == 21);
				}
		}

		return false;
	}

	private function _changeItemStock($orderId, $mode){
		$itemOrders = self::_getItemsByOrderId($orderId);
		if(!count($itemOrders)) return false;

		$itemDao = SOY2DAOFactory::create("shop.SOYShop_ItemDAO");
		foreach($itemOrders as $itemOrder){
			try{
				$item = $itemDao->getById($itemOrder->getItemId());
			}catch(Exception $e){
				continue;
			}

			//在庫数を戻す
			if($mode == self::CHANGE_STOCK_MODE_CANCEL){
				$item->setStock((int)$item->getStock() + (int)$itemOrder->getItemCount());
			//在庫数を減らす
			}else if($mode == self::CHANGE_STOCK_MODE_RETURN){
				$item->setStock((int)$item->getStock() - (int)$itemOrder->getItemCount());
			}else{
				//何もしない
			}

			try{
				$itemDao->update($item);
			}catch(Exception $e){
				var_dump($e);
			}
		}
	}

	function changeItemOrdersIsConfirm($orderId, $isConfirmItemOrderIds){
		$itemOrders = self::_getItemsByOrderId($orderId);
		if(!count($itemOrders)) return array();

		$changes = array();
		foreach($itemOrders as $itemOrder){
			if((int)$itemOrder->getIsConfirm() == 1){
				//既に確認済みの場合、新たにステータスを変更するID一覧になければステータス変更
				if(!in_array($itemOrder->getId(), $isConfirmItemOrderIds)){
					$itemOrder->setIsConfirm(SOYShop_ItemOrder::NO_CONFIRM);
					try{
						self::itemOrderDao()->update($itemOrder);
						$changes[] = "「" . $itemOrder->getItemName() . "」の確認済みを取り消しました。";
					}catch(Exception $e){
						//
					}
				}
			}else{
				//未確認の場合、新たにステータスを変更するID一覧になければステータス変更
				if(in_array($itemOrder->getId(), $isConfirmItemOrderIds)){
					$itemOrder->setIsConfirm(SOYShop_ItemOrder::IS_CONFIRM);
					try{
						self::itemOrderDao()->update($itemOrder);
						$changes[] = "「" . $itemOrder->getItemName() . "」を確認済みにしました。";
					}catch(Exception $e){
						//
					}
				}
			}
		}

		return $changes;
	}

	function changeItemOrdersStatus($orderId, $statuses){
		if(!is_array($statuses) || !count($statuses)) return array();
		$itemOrders = self::_getItemsByOrderId($orderId);
		if(!count($itemOrders)) return array();

		$changes = array();
		foreach($statuses as $itemOrderId => $newStatus){
			if(!isset($itemOrders[$itemOrderId])) continue;
			$itemOrder = $itemOrders[$itemOrderId];
			$oldStatus = $itemOrder->getStatus();
			if($oldStatus != $newStatus){
				$itemOrder->setStatus($newStatus);
				self::itemOrderDao()->update($itemOrder);
				$changes[] = "「" . $itemOrder->getItemName() . "」の状態を「" . SOYShop_ItemOrder::getStatusText($oldStatus) . "」から「" . SOYShop_ItemOrder::getStatusText($newStatus) . "」に変更しました。";
			}
		}

		return $changes;
	}

	function changeItemOrdersFlag($orderId, $flags){
		if(!is_array($flags) || !count($flags)) return array();
		$itemOrders = self::_getItemsByOrderId($orderId);
		if(!count($itemOrders)) return array();

		$changes = array();
		foreach($flags as $itemOrderId => $newFlag){
			if(!isset($itemOrders[$itemOrderId])) continue;
			$itemOrder = $itemOrders[$itemOrderId];
			$oldFlag = $itemOrder->getFlag();
			if($oldFlag != $newFlag){
				$itemOrder->setFlag($newFlag);
				self::itemOrderDao()->update($itemOrder);
				$changes[] = "「" . $itemOrder->getItemName() . "」のフラグを「" . SOYShop_ItemOrder::getFlagText($oldFlag) . "」から「" . SOYShop_ItemOrder::getFlagText($newFlag) . "」に変更しました。";
			}
		}

		return $changes;
	}

	function getOrderCountListByItemIds($itemIds){
		if(!count($itemIds)) return array();

		//予約カレンダーの場合は保留
		if(SOYShopPluginUtil::checkIsActive("reserve_calendar")) return array();

		$orders = array();

		$res = self::itemOrderDao()->countOrderCountListByItemIds($itemIds);
		if(count($res)){
			foreach($res as $itemId => $count){
				$orders[$itemId] = $count;
			}
		}
		if(count($orders) === count($itemIds)) return $orders;

		foreach($orders as $itemId => $stock){
			$idx = array_search($itemId, $itemIds);
			unset($itemIds[$idx]);
			$itemIds = array_values($itemIds);
		}

		$res = self::itemOrderDao()->countChildOrderCountListByItemIds($itemIds);
		if(count($res)){
			foreach($res as $itemId => $count){
				$orders[$itemId] = $count;
			}
		}

		//高速化の為に最後に0で埋めておく
		foreach($itemIds as $itemId){
			if(!isset($orders[$itemId])) $orders[$itemId] = 0;
		}

		return $orders;
	}

	function getTrackingNumberListByIds($ids){
		if(!is_array($ids) || !count($ids)) return array();
		return SOY2DAOFactory::create("order.SOYShop_OrderDAO")->getTrackingNumberListByIds($ids);
	}

	//orderIdとuserIdの対応一覧
	function getOrderIdAndUserIdPairList($orderIds){
		if(!is_array($orderIds) || !count($orderIds)) return array();
		return SOY2DAOFactory::create("order.SOYShop_OrderDAO")->getOrderIdAndUserIdPairList($orderIds);
	}

	function getOrderDateListByIds($ids){
		if(!is_array($ids) || !count($ids)) return array();
		return SOY2DAOFactory::create("order.SOYShop_OrderDAO")->getOrderDateListByIds($ids);
	}

	private function _getItemsByOrderId($orderId) {
    	try{
			return self::itemOrderDao()->getByOrderId($orderId);
    	}catch(Exception $e){
    		return array();
    	}
    }

	private function itemOrderDao(){
		static $dao;
		if(is_null($dao)) $dao = SOY2DAOFactory::create("order.SOYShop_ItemOrderDAO");
		return $dao;
	}
}
