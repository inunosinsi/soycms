<?php

class TicketBaseLogic extends SOY2LogicBase{

	private $count;
	private $config;
	private $ticketDao;
	private $ticketHistoryDao;
	private $userDao;
	private $itemAttributeDao;

	function __construct(){
		SOY2::imports("module.plugins.common_ticket_base.domain.*");
		SOY2::import("module.plugins.common_ticket_base.util.TicketBaseUtil");
		$this->config = TicketBaseUtil::getConfig();
		$this->ticketDao = SOY2DAOFactory::create("SOYShop_TicketDAO");
		$this->ticketHistoryDao = SOY2DAOFactory::create("SOYShop_TicketHistoryDAO");
		$this->itemAttributeDao = SOY2DAOFactory::create("shop.SOYShop_ItemAttributeDAO");
	}

	/**
	 * 自由にチケットを投稿する場合
	 * @param int count 追加したいチケット, string history, int userId
	 */
	function insert($count, $history, $userId){

		$obj = self::getTicketObjByUserId($userId);

		$res = true;

		//すでに指定したユーザにポイントがあった場合
		if(!is_null(($obj->getUserId()))) {
			$oldCount = $obj->getCount();
			$obj->setCount($oldCount + $count);

			try{
				$this->ticketDao->update($obj);
			}catch(Exception $e){
				error_log($e);
				$res = false;
			}

		//初回のポイント加算の場合
		} else {
			$obj->setUserId($userId);
			$obj->setCount($count);

			try{
				$this->ticketDao->insert($obj);
			}catch(Exception $e){
				error_log($e);
				$res = false;
			}
		}

		//次に履歴に挿入する
		$content = ($res) ? $history : "チケット追加を失敗しました";
		self::__insertHistory($userId, null, $count, $content);

		//一応boolean値を返しておく
		return true;
	}

	/**
	 * @param object SOYShop_Order, int count
	 */
	function insertTicket(SOYShop_Order $order, $count){

		$this->count = $count;
		$userId = $order->getUserId();

		//チケット追加処理のフラグ
		$obj = self::getTicketObjByUserId($userId);

		//初回の購入
		if(is_null($obj->getUserId())){
			$obj->setUserId($userId);
			$obj->setCount($count);
			try{
				$this->ticketDao->insert($obj);
			}catch(Exception $e){
				error_log($e);
				$res = false;
			}

		//二度目以降の購入
		}else{
			$obj->setCount((int)$obj->getCount() + $count);
			try{
				$this->ticketDao->update($obj);
			}catch(Exception $e){
				error_log($e);
				$res = false;
			}
		}
		self::insertHistory($order->getId(), $userId, true);
	}

	/**
	 * @param int count, int userId
	 */
	function updateTicket($count, $userId){

		$this->count = $count;

		$obj = self::getTicketObjByUserId($userId);

		$res = false;

		//念の為、オブジェクトの中に値があるかチェックする
		if(is_numeric($obj->getUserId())){
			$obj->setCount($count);

			try{
				$this->ticketDao->update($obj);
				$res = true;
			}catch(Exception $e){
				error_log($e);
			}

		//userIdがない場合は新規にポイント加算
		}else{
			$obj->setUserId($userId);
			$obj->setCount($count);

			try{
				$this->ticketDao->insert($obj);
				$res = true;
			}catch(Exception $e){
				error_log($e);
			}
		}

		if($res) self::insertHistory(null, $userId, true, true);
	}

	function insertHistory($orderId, $userId, $result, $update=false){
		$cnt = $this->count . $this->config["unit"];	//単位付き表記

		//要リファクタリング
		if($update){
			$content = $cnt . SOYShop_ticketHistory::TICKET_UPDATE;
		}else{
			$content = ($result) ? $cnt . SOYShop_ticketHistory::TICKET_INCREASE : SOYShop_ticketHistory::TICKET_FAILED;
		}

		$this->__insertHistory($userId, $orderId, $this->count, $content);
	}

	/**
	 * ポイント履歴の作成
	 * @param int
	 * @param int
	 * @param unsigned int
	 * @param string
	 */
	private function __insertHistory($userId, $orderId, $count, $content){
		$obj = new SOYShop_TicketHistory();

		$obj->setUserId($userId);
		$obj->setOrderId($orderId);
		$obj->setCount($count);
		$obj->setContent($content);

		try{
			$this->ticketHistoryDao->insert($obj);
			sleep(1);//同時刻の挿入で順序がおかしくならないようにしておく
		}catch(Exception $e){
			error_log($e);
		}
	}

	/**
	 * @param int paymentCount, int userId
	 */
	function paymentTicket($paymentCount, $userId){

		$obj = self::getTicketObjByUserId($userId);

		$res = false;

		//念の為、オブジェクトの中に値があるかチェックする
		if(is_numeric($obj->getUserId())){
			//手持ちのポイント
			$hasCount = $obj->getCount();
			$newCount = $hasCount - $paymentCount;

			$obj->setCount($newCount);

			try{
				$this->ticketDao->update($obj);
				self::__insertHistory($userId, null, $paymentCount, $paymentCount . SOYShop_TicketHistory::TICKET_PAYMENT);
				$res = true;
			}catch(Exception $e){
				var_dump($e);
			}
		}

		return $res;
	}

	/**
	 * @param object SOYShop_Order, int paymentCount
	 */
	function insertPaymentTicketHistory(SOYShop_Order $order, $paymentCount){

		$res = self::paymentCount($paymentCount, $order->getUserId());

		if($res){
			$this->__insertHistory(
				$order->getUserId(),
				$order->getId(),
				-1 * $paymentCount,//使用した際はマイナスの値でログを取る
				$paymentCount . $this->config["unit"] . SOYShop_CountHistory::POINT_PAYMENT
			);
		}
	}

	/**
	 * ポイント支払後に付与するポイントを取得
	 * @param CartLogic $cart, SOYShop_Order $order
	 * @return Integer $totalCount
	 */
	function getTicketCount(CartLogic $cart, SOYShop_Order $order){
		$count = 0;
		$itemOrders = $cart->getItems();

		//クレジット支払からの結果通知の場合はCartLogicのitemsは消えているので、再度取得する
		if(is_null($itemOrders) || is_array($itemOrders)){
			try{
				$itemOrders = soyshop_get_item_orders($order->getId());
			}catch(Exception $e){
				$itemOrders = array();
			}
		}

		foreach($itemOrders as $itemOrder){
			$count += self::getTicketCountConfig($itemOrder->getItemId(), $itemOrder->getItemCount());
		}

		return $count;
	}

	//商品ごとに設定したチケット枚数付与の割合
	function getTicketCountConfig(int $itemId, int $itemCount){

		//親商品がないか調べる
		$item = soyshop_get_item_object($itemId);
		if(is_numeric($item->getType())) $itemId = (int)$item->getType();

		$count = self::getTicketCountByItemId($itemId);
		return $count * $itemCount;
	}

	function getUser(int $userId){
		return soyshop_get_user_object($userId);
	}

	private function getUserIdByMailAddress(string $mailaddress){
		return soyshop_get_user_object_by_mailaddress($mailaddress)->getId();
	}

	function getTicketObjByUserId(int $userId){
		try{
			return $this->ticketDao->getByUserId($userId);
		}catch(Exception $e){
			return new SOYShop_Ticket();
		}
	}

	function getTicketCountByItemId(int $itemId){
		$cnt = soyshop_get_item_attribute_value($itemId, TicketBaseUtil::PLUGIN_ID, "int");
		if(!$cnt) $cnt = 1;
		return $cnt;
	}

	function getTotalCountByOrderId(int $orderId){
		try{
			$histories = SOY2DAOFactory::create("SOYShop_TicketHistoryDAO")->getByOrderId($orderId);
		}catch(Exception $e){
			$histories = array();
		}

		if(!count($histories)) return 0;

		//ポイントが+の値が合った時に返す
		foreach($histories as $history){
			if((int)$history->getCount() > 0){
				return $history->getCount();
			}
		}

		return 0;
	}
}
