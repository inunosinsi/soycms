<?php

class PointBaseLogic extends SOY2LogicBase{
	
	private $point;
	private $config;
	private $pointDao;
	private $pointHistoryDao;
	private $userDao;
	private $itemAttributeDao;
	private $percentage;
	
	const PLUGIN_ID = "common_point_base";
		
	function __construct(){
		SOY2::imports("module.plugins.common_point_base.domain.*");
		SOY2::imports("module.plugins.common_point_base.util.*");
		if(!$this->config) $this->config = PointBaseUtil::getConfig();
		if(!$this->pointDao) $this->pointDao = SOY2DAOFactory::create("SOYShop_PointDAO");
		if(!$this->pointHistoryDao) $this->pointHistoryDao = SOY2DAOFactory::create("SOYShop_PointHistoryDAO");
		if(!$this->itemAttributeDao) $this->itemAttributeDao = SOY2DAOFactory::create("shop.SOYShop_ItemAttributeDAO");
		
		$this->config = PointBaseUtil::getConfig();
		$this->percentage = (int)$this->config["percentage"];
	}
	
	/**
	 * 自由にポイントを投稿する場合
	 * @param int point 追加したいポイント, string history, int userId
	 */
	function insert($point, $history, $userId){
		
		$config = $this->config;
		$dao = $this->pointDao;
		
		try{
			$obj = $dao->getByUserId($userId);
		}catch(Exception $e){
			$obj = new SOYShop_Point();
		}
		
		$res = true;
		
		//すでに指定したユーザにポイントがあった場合
		$id = $obj->getUserId();
		if(isset($id)){
			$oldPoint = $obj->getPoint();
			$obj->setPoint($oldPoint + $point);
			$obj->setTimeLimit($this->getTimeLimit($config["limit"]));
			$obj->setUpdateDate(time());
				
			try{
				$dao->deleteByUserId($userId);
				$dao->insert($obj);
			}catch(Exception $e){
				$res = false;
			}
			
		//初回のポイント加算の場合
		}else{
			$obj->setUserId($userId);
			$obj->setPoint($point);
			$obj->setTimeLimit($this->getTimeLimit($config["limit"]));
			$obj->setCreateDate(time());
			$obj->setUpdateDate(time());
				
			try{
				$dao->insert($obj);
			}catch(Exception $e){
				$res = false;
			}
		}
		
		//次に履歴に挿入する
		$dao = $this->pointHistoryDao;
		
		$content = ($res) ? $history : "ポイント追加を失敗しました";
		
		$obj = new SOYShop_PointHistory();
		
		$obj->setUserId($userId);
		$obj->setPoint($point);
		$obj->setContent($content);
		$obj->setCreateDate(time());
		
		try{
			$dao->insert($obj);
		}catch(Exception $e){
			return false;
		}
		
		//一応boolean値を返しておく
		return true;
	}
	
	/**
	 * @param object SOYShop_Order, int point
	 */
	function insertPoint(SOYShop_Order $order, $point){
		
		$this->point = $point;
				
		$config = $this->config;
		
		$userId = $order->getUserId();
		
		//ポイント加算処理のフラグ
		$flag = true;
		
		if(isset($config["customer"])){
			$user = self::getUser($userId);
			$pass = $user->getPassword();
			if(!isset($pass)) $flag = false;
		}
		
		if($flag){
			
			$dao = $this->pointDao;
			
			$obj = self::getPointByUserId($userId);
			
			$res = true;
		
			//初回の購入
			if(is_null($obj->getUserId())){
				$obj->setUserId($userId);
				$obj->setPoint($point);
				$obj->setTimeLimit($this->getTimeLimit($config["limit"]));
				$obj->setCreateDate(time());
				$obj->setUpdateDate(time());
				
				try{
					$dao->insert($obj);
				}catch(Exception $e){
					$res = false;
				}
					
			//二度目以降の購入
			}else{
				$oldPoint = $obj->getPoint();
				//有効期限切れだった場合は、ポイントを0にリセットしてから加算する
				if(!is_null($obj->getTimeLimit()) && $obj->getTimeLimit() < time()){
					$oldPoint = 0;
					self::insertHistory($order->getId(), $userId, $res, false, true);
				}
				$obj->setPoint($oldPoint + $point);
				$obj->setTimeLimit($this->getTimeLimit($config["limit"]));
				$obj->setUpdateDate(time());
				
				try{
					$dao->deleteByUserId($userId);
					$dao->insert($obj);
				}catch(Exception $e){
					$res = false;
				}
			}
			self::insertHistory($order->getId(), $userId, $res);
		}
	}
	
	/**
	 * @param int point, int userId
	 */
	function updatePoint($point, $userId){
		
		$this->point = $point;
		
		$obj = self::getPointByUserId($userId);		
		$config = $this->config;
		
		$res = false;
		
		//念の為、オブジェクトの中に値があるかチェックする
		if(is_numeric($obj->getUserId())){
			
			$obj->setPoint($point);
			$obj->setUpdateDate(time());
			
			try{
				$this->pointDao->deleteByUserId($userId);
				$this->pointDao->insert($obj);
				$res = true;
			}catch(Exception $e){
			}
			
		//userIdがない場合は新規にポイント加算
		}else{
			$obj->setUserId($userId);
			$obj->setPoint($point);
			$obj->setTimeLimit($this->getTimeLimit($config["limit"]));
			$obj->setCreateDate(time());
			$obj->setUpdateDate(time());
			
			try{
				$this->pointDao->insert($obj);
				$res = true;
			}catch(Exception $e){
			}
		}
		
		if($res){
			self::insertHistory(null, $userId, true, true);
		}
	}
	
	function insertHistory($orderId, $userId, $result, $update=false, $timeLimit=false){		
		
		$config = $this->config;
				
		//要リファクタリング
		if($update){
			$content = $this->point . SOYShop_PointHistory::POINT_UPDATE;
		}else{
			$content = ($result) ? $this->point . SOYShop_PointHistory::POINT_INCREASE : SOYShop_PointHistory::POINT_FAILED;
		}
		
		//timeLimitフラグがある場合は、強制的に$contentを書き換える
		if($timeLimit){
			$content = "有効期限切れのため、ポイントをリセット";
		}
		
		$obj = new SOYShop_PointHistory();
		
		$obj->setUserId($userId);
		$obj->setOrderId($orderId);
		$obj->setPoint($this->point);
		$obj->setContent($content);
		$obj->setCreateDate(time());
		
		try{
			$this->pointHistoryDao->insert($obj);
		}catch(Exception $e){
			//
		}
	}
	
	/**
	 * @param int paymentPoint, int userId
	 */
	function paymentPoint($paymentPoint, $userId){
				
		$obj = self::getPointByUserId($userId);
				
		$res = false;
		
		//念の為、オブジェクトの中に値があるかチェックする
		if(is_numeric($obj->getUserId())){
			
			//有効期限チェック
			if($obj->getTimeLimit() > time() || is_null($obj->getTimeLimit())){
				//手持ちのポイント
				$hasPoint = $obj->getPoint();
				$newPoint = $hasPoint - $paymentPoint;
				
				$obj->setPoint($newPoint);
				$obj->setUpdateDate(time());
				
				try{
					$this->pointDao->deleteByUserId($userId);
					$this->pointDao->insert($obj);
					$res = true;
				}catch(Exception $e){
				}
			}	
		}
		
		return $res;
	}
	
	/**
	 * @param object SOYShop_Order, int paymentPoint
	 */
	function insertPaymentPointHistory(SOYShop_Order $order, $paymentPoint){
				
		$res = self::paymentPoint($paymentPoint, $order->getUserId());
		
		if($res){
						
			$content = $paymentPoint . SOYShop_PointHistory::POINT_PAYMENT;
			
			$obj = new SOYShop_PointHistory();
			$obj->setUserId($order->getUserId());
			$obj->setOrderId($order->getId());
			$obj->setPoint(-1 * $paymentPoint);	//使用した際はマイナスの値でログを取る
			$obj->setContent($content);
			$obj->setCreateDate(time());
			
			try{
				$this->pointHistoryDao->insert($obj);
			}catch(Exception $e){
				//
			}
		}
	}
	
	/**
	 * ポイント支払後に付与するポイントを取得
	 * @param CartLogic $cart, SOYShop_Order $order
	 * @return Integer $totalPoint
	 */
	function getTotalPointAfterPaymentPoint(CartLogic $cart, SOYShop_Order $order){
		$totalPoint = 0;
		$itemOrders = $cart->getItems();
		
		//クレジット支払からの結果通知の場合はCartLogicのitemsは消えているので、再度取得する
		if(is_null($itemOrders) || is_array($itemOrders)){
			try{
				$itemOrders = SOY2DAOFactory::create("order.SOYShop_ItemOrderDAO")->getByOrderId($order->getId());
			}catch(Exception $e){
				$itemOrders = array();
			}
		}
		
		foreach($itemOrders as $itemOrder){
			$totalPoint += self::getPointPercentage($itemOrder->getItemId(), $itemOrder->getTotalPrice());
		}

		$paymentPoint = $cart->getAttribute("point_payment");
		
		//クレジット支払から結果通知の場合はCartLogicのポイント支払のモジュールが消えているので、再度取得する
		if(is_null($paymentPoint)){
			$modules = $order->getModuleList();
			if(isset($modules["point_payment"]) && $modules["point_payment"]->getPrice() !== 0){
				$paymentPoint = abs($modules["point_payment"]->getPrice());
			}
		}
		
		if(isset($paymentPoint)){
			
			//ポイントを使用する
			self::insertPaymentPointHistory($order, $paymentPoint);
			
			//ポイントの再計算
			if($this->config["recalculation"] == 1){
				$itemTotalPrice = (int)$cart->getItemPrice();
				$itemTotalPrice = $itemTotalPrice - (int)$paymentPoint;
				if($itemTotalPrice < 0){
					$itemTotalPrice = 0;
				}
				
				//pointによる支払があった場合は、ここで商品のトータルから引いておく
				$totalPoint = $totalPoint - ceil($paymentPoint * (int)$this->config["percentage"] / 100);
				
				if($totalPoint < 0) $totalPoint = 0;
			}
		}

		return $totalPoint;
	}
	
	//商品ごとに設定したポイント付与の割合
	function getPointPercentage($itemId, $totalPrice){
		
		//親商品がないか調べる
		try{
			$itemDao = SOY2DAOFactory::create("shop.SOYShop_ItemDAO");
			$item = $itemDao->getById($itemId);
			if(is_numeric($item->getType())) $itemId = $item->getType();
		}catch(Exception $e){
			
		}
		
		try{
			$percentage = (int)$this->itemAttributeDao->get($itemId, self::PLUGIN_ID)->getValue();
		}catch(Exception $e){
			$percentage = $this->percentage;
		}

		//ポイント付与プラグインの方の設定を持ってくる
		SOY2::import("util.SOYShopPluginUtil");
		if(SOYShopPluginUtil::checkIsActive("common_point_grant")){
			$percentage = SOY2Logic::createInstance("module.plugins.common_point_grant.logic.PointGrantLogic")->getPercentageAfterCheckSale($itemId, $percentage);
		}
				
		return floor($totalPrice * $percentage / 100);
	}
	
	//指定のメールアドレスのユーザがポイントを持っているかチェックする
	function hasPointByUserMailAddress($mailaddress){
		$userId = self::getUserIdByMailAddress($mailaddress);
		
		if(!$userId) return false;
		
		try{
			$point = $this->pointDao->getByUserId($userId);
		}catch(Exception $e){
			return false;
		}

		$hasPoint = (int)$point->getPoint();

		//有効期限が切れていないかどうか
		if($hasPoint === 0) return false;

		$timeLimit = $point->getTimeLimit();
		if(!is_null($timeLimit) && $timeLimit < time()) return false;
		
		return true;
	}
	
	function getUser($userId){
		if(!$this->userDao) $this->userDao = SOY2DAOFactory::create("user.SOYShop_UserDAO");

		try{
			return $this->userDao->getById($userId);
		}catch(Exception $e){
			return new SOYShop_User();
		}
	}
	
	private function getUserIdByMailAddress($mailaddress){
		try{
			return SOY2DAOFactory::create("user.SOYShop_UserDAO")->getByMailAddress($mailaddress)->getId();
		}catch(Exception $e){
			return 0;
		}
	}
	
	function getPointByUserId($userId){
		
		try{
			return $this->pointDao->getByUserId($userId);
		}catch(Exception $e){
			return new SOYShop_Point();
		}
	}
	
	function getTimeLimit($timeLimit){
		return (isset($timeLimit)) ? time() + $timeLimit * 60 * 60 * 24 : null;
	}
	
	//個々の商品のポイント付与率の一括変更
	function setPointCollective($percentage){
		$dao = SOY2DAOFactory::create("shop.SOYShop_ItemAttributeDAO");
		try{
			$dao->executeUpdateQuery("UPDATE soyshop_item_attribute SET item_value = :percentage WHERE item_field_id = :fieldId", array(":percentage" => $percentage, ":fieldId" => self::PLUGIN_ID));
		}catch(Exception $e){
			//var_dump($e);
		}
	}
}
?>