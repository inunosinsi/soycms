<?php

class PointBaseLogic extends SOY2LogicBase{

	private $cart;
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
		SOY2::import("module.plugins.common_point_grant.util.PointGrantUtil");
		$this->config = PointGrantUtil::getConfig();
		$this->pointDao = SOY2DAOFactory::create("SOYShop_PointDAO");
		$this->pointHistoryDao = SOY2DAOFactory::create("SOYShop_PointHistoryDAO");
		$this->itemAttributeDao = SOY2DAOFactory::create("shop.SOYShop_ItemAttributeDAO");

		$this->percentage = (int)$this->config["percentage"];
	}

	/**
	 * 自由にポイントを投稿する場合
	 * @param int point 追加したいポイント, string history, int userId
	 */
	function insert($point, $history, $userId){
		$res = true;

		$obj = self::getPointByUserId($userId);
		$obj->setTimeLimit(self::getTimeLimit());

		//すでに指定したユーザにポイントがあった場合
		if(!is_null(($obj->getUserId()))) {
			$obj->setPoint((int)$obj->getPoint() + $point);

			try{
				$this->pointDao->update($obj);
			}catch(Exception $e){
				error_log($e);
				$res = false;
			}

		//初回のポイント加算の場合
		} else {
			$obj->setUserId($userId);
			$obj->setPoint($point);

			try{
				$this->pointDao->insert($obj);
			}catch(Exception $e){
				error_log($e);
				$res = false;
			}
		}

		//次に履歴に挿入する
		$content = ($res) ? $history : "ポイント追加を失敗しました";
		self::__insertHistory($userId, null, $point, $content);

		//一応boolean値を返しておく
		return true;
	}

	/**
	 * @param object SOYShop_Order, int point
	 */
	function insertPoint(SOYShop_Order $order, $point){

		$this->point = $point;
		$userId = $order->getUserId();

		//ポイント加算処理のフラグ
		$flag = true;

		if(isset($config["customer"])){
			$user = self::getUser($userId);
			$pass = $user->getPassword();
			if(!isset($pass)) $flag = false;
		}

		if($flag){
			$obj = self::getPointByUserId($userId);
			$obj->setTimeLimit(self::getTimeLimit());

			$res = true;

			//初回の購入
			if(is_null($obj->getUserId())){
				$obj->setUserId($userId);
				$obj->setPoint($point);

				try{
					$this->pointDao->insert($obj);
				}catch(Exception $e){
					error_log($e);
					$res = false;
				}

			//二度目以降の購入
			}else{
				$oldPoint = (int)$obj->getPoint();
				//有効期限切れだった場合は、ポイントを0にリセットしてから加算する
				if(!is_null($obj->getTimeLimit()) && $obj->getTimeLimit() < time()){
					$oldPoint = 0;
					self::insertHistory($order->getId(), $userId, $res, false, true);
				}
				$obj->setPoint($oldPoint + $point);

				try{
					$this->pointDao->update($obj);
				}catch(Exception $e){
					error_log($e);
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

		$res = false;

		//念の為、オブジェクトの中に値があるかチェックする
		if(is_numeric($obj->getUserId())){
			$obj->setPoint($point);

			try{
				$this->pointDao->update($obj);
				$res = true;
			}catch(Exception $e){
				error_log($e);
			}

		//userIdがない場合は新規にポイント加算
		}else{
			$obj->setUserId($userId);
			$obj->setPoint($point);
			$obj->setTimeLimit(self::getTimeLimit());

			try{
				$this->pointDao->insert($obj);
				$res = true;
			}catch(Exception $e){
				error_log($e);
			}
		}

		if($res) self::insertHistory(null, $userId, true, true);
	}

	function insertHistory($orderId, $userId, $result, $update=false, $timeLimit=false){

		//要リファクタリング
		if($update){
			$content = $this->point . SOYShop_PointHistory::POINT_UPDATE;
		}else{
			$content = ($result) ? $this->point . SOYShop_PointHistory::POINT_INCREASE : SOYShop_PointHistory::POINT_FAILED;
		}

		//timeLimitフラグがある場合は、強制的に$contentを書き換える
		if($timeLimit) $content = "有効期限切れのため、ポイントをリセット";

		self::__insertHistory($userId, $orderId, $this->point, $content);
	}

	/**
	 * ポイント履歴の作成
	 * @param int
	 * @param int
	 * @param unsigned int
	 * @param string
	 */
	private function __insertHistory($userId, $orderId, $point, $content){
		$obj = new SOYShop_PointHistory();

		$obj->setUserId($userId);
		$obj->setOrderId($orderId);
		$obj->setPoint($point);
		$obj->setContent($content);

		try{
			$this->pointHistoryDao->insert($obj);
			sleep(1);//同時刻の挿入で順序がおかしくならないようにしておく
		}catch(Exception $e){
			error_log($e);
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

				try{
					$this->pointDao->update($obj);
					$res = true;
				}catch(Exception $e){
					//
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
			$this->__insertHistory(
				$order->getUserId(),
				$order->getId(),
				-1 * $paymentPoint,//使用した際はマイナスの値でログを取る
				$paymentPoint . SOYShop_PointHistory::POINT_PAYMENT
			);
		}
	}

	/**
	 * ポイント支払後に付与するポイントを取得
	 * @param CartLogic $cart, SOYShop_Order $order
	 * @return Integer $totalPoint
	 */
	function getTotalPointAfterPaymentPoint(SOYShop_Order $order){
		$totalPoint = self::getTotalPointOnCart($order->getId());
		$paymentPoint = $this->cart->getAttribute("point_payment");

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
			SOY2::import("module.plugins.common_point_base.util.PointBaseUtil");
			$conf = PointBaseUtil::getConfig();
			if(isset($conf["recalculation"]) && $conf["recalculation"] == 1){
				$itemTotalPrice = (int)$this->cart->getItemPrice();
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

	/**
	 * カート内に入れた商品のポイント合計
	 * @return integer
	 */
	function getTotalPointOnCart($orderId = null){
		$itemOrders = $this->cart->getItems();

		//クレジット支払からの結果通知の場合はCartLogicのitemsは消えているので、再度取得する
		if(isset($orderId) && is_null($itemOrders) || !is_array($itemOrders) || !count($itemOrders)){
			try{
				$itemOrders = SOY2DAOFactory::create("order.SOYShop_ItemOrderDAO")->getByOrderId($orderId);
			}catch(Exception $e){
				$itemOrders = array();
			}
		}

		if(!count($itemOrders)) return 0;

		$total = 0;
		foreach($itemOrders as $itemOrder){
			$total += self::getPointPercentage($itemOrder->getItemId(), $itemOrder->getTotalPrice());
		}
		return (int)$total;
	}

	/**
	 * 注文実行直前に、使用するポイントに不足がないか、有効期限を過ぎていないかをチェックする
	 * @throws Exception
	 */
	public function checkIfPointIsEnoughAndValidBeforeOrder(){
		$pointToUse = $this->cart->getAttribute("point_payment");

		//ポイントを使う場合
		if(strlen($pointToUse) && is_numeric($pointToUse) && $pointToUse > 0){
			$user = $this->cart->getCustomerInformation();
			$pointObj = self::getPointByUserId($user->getId());
			$ownedPoint = $pointObj->getPoint();

			//有効期限チェック
			if(!is_null($pointObj->getTimeLimit()) && $pointObj->getTimeLimit() < time()){
				$this->cart->log("[point] User: ".$user->getId());
				$this->cart->log("[point] limit: ".date("c", $pointObj->getTimeLimit()));

				$this->cart->addErrorMessage("point", MessageManager::get("POINT_ERROR"));
				$this->cart->setAttribute("page", "Cart03");

				$this->cart->removeModule("point_payment");
				$this->cart->clearOrderAttribute("point_payment");
				//エラーを出せるように残しておく
				//$cart->clearAttribute("point_payment");

				$this->cart->save();

				throw new Exception("ポイントの有効期限が切れています。");
			}

			//不足していたら例外を投げる
			if($ownedPoint < $pointToUse){
				$cart->log("[point] User: ".$user->getId());
				$cart->log("[point] own: ".$ownedPoint);
				$cart->log("[point] use: ".$pointToUse);

				$this->cart->addErrorMessage("point", MessageManager::get("POINT_ERROR"));
				$this->cart->setAttribute("page", "Cart03");

				$this->cart->removeModule("point_payment");
				$this->cart->clearOrderAttribute("point_payment");
				//エラーを出せるように残しておく
				// $cart->clearAttribute("point_payment");

				$this->cart->save();

				throw new Exception("ポイントが不足しています。");
			}
		}
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

	private function getTimeLimit(){
		static $timeLimit;
		if(is_null($timeLimit)){
			SOY2::import("module.plugins.common_point_base.util.PointBaseUtil");
			$conf = PointBaseUtil::getConfig();
			$timeLimit = (isset($conf["limit"]) && (int)$conf["limit"] > 0) ? (int)(int)$conf["limit"] : null;
		}
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

	function setCart($cart){
		$this->cart = $cart;
	}
}
