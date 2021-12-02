<?php

class PointBaseLogic extends SOY2LogicBase{

	private $cart;

	const PLUGIN_ID = "common_point_base";

	function __construct(){}

	/**
	 * 自由にポイントを投稿する場合
	 * @param int point 追加したいポイント, string history, int userId
	 */
	function insert(int $point, string $history, int $userId){
		$res = true;

		$obj = self::_get($userId);
		$obj->setTimeLimit(self::_timeLimit());

		//初回でも下記の処理で問題なく動作する
		$obj->setPoint((int)$obj->getPoint() + $point);

		$res = true;
		try{
			self::_dao()->insert($obj);
		}catch(Exception $e){
			try{
				self::_dao()->update($obj);
			}catch(Exception $e){
				error_log($e);
				$res = false;
			}
		}

		//次に履歴に挿入する
		$content = ($res) ? $history : "ポイント追加を失敗しました";
		self::__insertHistory($userId, 0, $point, $content);

		//一応boolean値を返しておく
		return true;
	}

	/**
	 * @param object SOYShop_Order, int point
	 */
	function insertPoint(SOYShop_Order $order, int $point){

		if(isset($config["customer"])){
			$user = soyshop_get_user_object($order->getUserId());
			$pass = $user->getPassword();
			if(!isset($pass) || !strlen($pass)) return;
		}

		$obj = self::_get($order->getUserId());
		$obj->setTimeLimit(self::_timeLimit());

		$oldPoint = (int)$obj->getPoint();
		//有効期限切れだった場合は、ポイントを0にリセットしてから加算する
		if(is_numeric($obj->getTimeLimit()) && $obj->getTimeLimit() < time()){
			$oldPoint = 0;
			self::_insertHistory($order->getId(), $order->getUserId(), $point, true, false, true);
		}
		$obj->setPoint($oldPoint + $point);

		$res = true;

		try{
			self::_dao()->insert($obj);
		}catch(Exception $e){
			try{
				self::_dao()->update($obj);
			}catch(Exception $e){
				error_log($e);
				$res = false;
			}
		}

		self::_insertHistory($order->getId(), $order->getUserId(), $point, $res);
	}

	/**
	 * @param int point, int userId
	 */
	function updatePoint(int $point, int $userId){
		$obj = self::_get($userId);
		$obj->setPoint($point);
		$obj->setTimeLimit(self::_timeLimit());
		$res = true;
		try{
			self::_dao()->insert($obj);
		}catch(Exception $e){
			try{
				self::_dao()->update($obj);
			}catch(Exception $e){
				var_dump($e);
				$res = false;
				error_log($e);
			}
		}

		if($res) self::_insertHistory(0, $userId, $point, true, true);
	}

	private function _insertHistory(int $orderId, int $userId, int $point, bool $isSuccess, bool $isUpdate=false, bool $isTimeLimit=false){
		self::_historyDao();
		if($isTimeLimit){
			$content = "有効期限切れのため、ポイントをリセット";
		}else if($isUpdate){
			$content = $point . SOYShop_PointHistory::POINT_UPDATE;
		}else{
			$content = ($isSuccess) ? $point . SOYShop_PointHistory::POINT_INCREASE : SOYShop_PointHistory::POINT_FAILED;
		}
		self::__insertHistory($userId, $orderId, $point, $content);
	}

	/**
	 * ポイント履歴の作成
	 * @param int
	 * @param int
	 * @param unsigned int
	 * @param string
	 */
	private function __insertHistory(int $userId, int $orderId, int $point, string $content){
		self::_historyDao();
		$obj = new SOYShop_PointHistory();

		$obj->setUserId($userId);
		if($orderId > 0) $obj->setOrderId($orderId);
		$obj->setPoint($point);
		$obj->setContent($content);

		try{
			self::_historyDao()->insert($obj);
			sleep(1);//同時刻の挿入で順序がおかしくならないようにしておく
		}catch(Exception $e){
			error_log($e);
		}
	}

	/**
	 * @param int paymentPoint, int userId
	 */
	function paymentPoint(int $paymentPoint, int $userId){
		$obj = self::_get($userId);
		//有効期限チェック
		if(is_numeric($obj->getTimeLimit()) && $obj->getTimeLimit() < time()) return false;

		//手持ちのポイント
		$obj->setPoint((int)$obj->getPoint() - $paymentPoint);

		try{
			self::_dao()->update($obj);
			return true;
		}catch(Exception $e){
			//
		}

		return false;
	}

	/**
	 * @param object SOYShop_Order, int paymentPoint
	 */
	function insertPaymentPointHistory(SOYShop_Order $order, $paymentPoint){
		self::_historyDao();
		if(self::paymentPoint($paymentPoint, $order->getUserId())){
			self::__insertHistory(
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
				if($itemTotalPrice < 0) $itemTotalPrice = 0;

				//pointによる支払があった場合は、ここで商品のトータルから引いておく
				$totalPoint = $totalPoint - ceil($paymentPoint * self::_percentage() / 100);
				if($totalPoint < 0) $totalPoint = 0;
			}
		}

		return $totalPoint;
	}

	/**
	 * カート内に入れた商品のポイント合計
	 * @return integer
	 */
	function getTotalPointOnCart(int $orderId=0){
		$itemOrders = $this->cart->getItems();

		//クレジット支払からの結果通知の場合はCartLogicのitemsは消えているので、再度取得する
		if($orderId > 0 && is_null($itemOrders) || !is_array($itemOrders) || !count($itemOrders)){
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
			$pointObj = self::_get($user->getId());
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
	function getPointPercentage(int $itemId, int $totalPrice){

		//親商品がないか調べる
		try{
			$item = soyshop_get_item_object($itemId);
			if(is_numeric($item->getType())) $itemId = (int)$item->getType();
		}catch(Exception $e){
			//
		}

		$percentage = soyshop_get_item_attribute_value($itemId, self::PLUGIN_ID, "int");
		if(!is_numeric($percentage)) $percentage = self::_percentage();

		//ポイント付与プラグインの方の設定を持ってくる
		SOY2::import("util.SOYShopPluginUtil");
		if(SOYShopPluginUtil::checkIsActive("common_point_grant")){
			$percentage = SOY2Logic::createInstance("module.plugins.common_point_grant.logic.PointGrantLogic")->getPercentageAfterCheckSale($itemId, $percentage);
		}

		return floor($totalPrice * $percentage / 100);
	}

	//指定のメールアドレスのユーザがポイントを持っているかチェックする
	function hasPointByUserMailAddress(string $mailaddress){
		$userId = self::_getUserIdByMailAddress($mailaddress);
		if(!is_numeric($userId)) return false;

		try{
			$point = self::_dao()->getByUserId($userId);
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

	private function _getUserIdByMailAddress($mailaddress){
		try{
			return SOY2DAOFactory::create("user.SOYShop_UserDAO")->getByMailAddress($mailaddress)->getId();
		}catch(Exception $e){
			return 0;
		}
	}

	function getPointObjByUserId(int $userId){
		return self::_get($userId);
	}

	private function _get(int $userId){
		try{
			return self::_dao()->getByUserId($userId);
		}catch(Exception $e){
			$obj = new SOYShop_Point();
			$obj->setPoint(0);
			$obj->setUserId($userId);
			return $obj;
		}
	}

	private function _timeLimit(){
		static $timeLimit;
		if(is_null($timeLimit)){
			SOY2::import("module.plugins.common_point_base.util.PointBaseUtil");
			$conf = PointBaseUtil::getConfig();
			$timeLimit = (isset($conf["limit"]) && (int)$conf["limit"] > 0) ? (int)(int)$conf["limit"] : null;
		}
		return (isset($timeLimit)) ? time() + $timeLimit * 60 * 60 * 24 : null;
	}

	//個々の商品のポイント付与率の一括変更
	function setPointCollective(int $percentage){
		try{
			SOY2DAOFactory::create("shop.SOYShop_ItemAttributeDAO")->executeUpdateQuery("UPDATE soyshop_item_attribute SET item_value = :percentage WHERE item_field_id = :fieldId", array(":percentage" => $percentage, ":fieldId" => self::PLUGIN_ID));
		}catch(Exception $e){
			//var_dump($e);
		}
	}

	private function _dao(){
		static $dao;
		if(is_null($dao)){
			SOY2::import("module.plugins.common_point_base.domain.SOYShop_PointDAO");
			$dao = SOY2DAOFactory::create("SOYShop_PointDAO");
		}
		return $dao;
	}

	private function _historyDao(){
		static $dao;
		if(is_null($dao)){
			SOY2::import("module.plugins.common_point_base.domain.SOYShop_PointHistoryDAO");
			$dao = SOY2DAOFactory::create("SOYShop_PointHistoryDAO");
		}
		return $dao;
	}

	private function _percentage(){
		static $p;
		if(is_null($p)){
			$cnf = self::_config();
			$p = (isset($cnf["percentage"])) ? $cnf["percentage"] : 0;
		}
		return $p;
	}

	private function _config(){
		static $cnf;
		if(is_null($cnf)){
			SOY2::import("module.plugins.common_point_grant.util.PointGrantUtil");
			$cnf = PointGrantUtil::getConfig();
		}
		return $cnf;
	}

	function setCart($cart){
		$this->cart = $cart;
	}
}
