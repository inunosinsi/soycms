<?php

class RecurringLogic extends SOY2LogicBase {

	const FIELD_KEY = "payment_pay_jp_recurring_token";

	function __construct(){
		SOY2::import("module.plugins.payment_pay_jp_recurring.util.PayJpRecurringUtil");
	}

	function initPayJp(){
		$config = $this->getPayJpConfig();

		//PAY.JPのlibを読み込む
		require_once(SOYSHOP_WEBAPP . "src/module/plugins/payment_pay_jp/payjp/init.php");
		\Payjp\Payjp::setApiKey($config["secret_key"]);
	}

	function getPayJpConfig(){
		static $config;
		if(is_null($config)){
			$conf = PayJpRecurringUtil::getConfig();
			if(isset($conf["sandbox"]) && $conf["sandbox"] == 1){
				$config = (isset($conf["test"]) && is_array($conf["test"])) ? $conf["test"] : array("secret_key" => "abc");
			}else{
				$config = (isset($conf["production"]) && is_array($conf["production"])) ? $conf["production"] : array("secret_key" => "abc");
			}
		}
		return $config;
	}

	function registCustomer($customer){
		$res = null;
		$err = null;
		try{
			$res = \Payjp\Customer::create($customer);
		} catch (\Payjp\Error\Card $e) {
			$err = $e->getJsonBody();
		} catch (\Payjp\Error\InvalidRequest $e) {
			$err = $e->getJsonBody();
		} catch (\Payjp\Error\Authentication $e) {
			$err = $e->getJsonBody();
		} catch (\Payjp\Error\Api $e) {
			$err = $e->getJsonBody();
		} catch (\Payjp\Error\Base $e) {
			$err = $e->getJsonBody();
		} catch (Exception $e) {
			$err = $e->getJsonBody();
		} finally {
			//何もしない
		}

		return array($res, $err);
	}

	function registPlan($plan){
		$res = null;
		$err = null;
		try{
			$res = \Payjp\Plan::create($plan);
		} catch (\Payjp\Error\Card $e) {
			$err = $e->getJsonBody();
		} catch (\Payjp\Error\InvalidRequest $e) {
			$err = $e->getJsonBody();
		} catch (\Payjp\Error\Authentication $e) {
			$err = $e->getJsonBody();
		} catch (\Payjp\Error\Api $e) {
			$err = $e->getJsonBody();
		} catch (\Payjp\Error\Base $e) {
			$err = $e->getJsonBody();
		} catch (Exception $e) {
			$err = $e->getJsonBody();
		} finally {
			//何もしない
		}

		return array($res, $err);
	}

	function subscribe($cusId, $planId){
		$res = null;
		$err = null;
		try{
			$res = \Payjp\Subscription::create(array("customer" => $cusId, "plan" => $planId));
		} catch (\Payjp\Error\Card $e) {
			$err = $e->getJsonBody();
		} catch (\Payjp\Error\InvalidRequest $e) {
			$err = $e->getJsonBody();
		} catch (\Payjp\Error\Authentication $e) {
			$err = $e->getJsonBody();
		} catch (\Payjp\Error\Api $e) {
			$err = $e->getJsonBody();
		} catch (\Payjp\Error\Base $e) {
			$err = $e->getJsonBody();
		} catch (Exception $e) {
			$err = $e->getJsonBody();
		} finally {
			//何もしない
		}

		return array($res, $err);
	}

	function cancel($subscribeId){
		$res = null;
		$err = null;
		try{
			$su = \Payjp\Subscription::retrieve($subscribeId);
			$res = $su->cancel();
		} catch (\Payjp\Error\Card $e) {
			$err = $e->getJsonBody();
		} catch (\Payjp\Error\InvalidRequest $e) {
			$err = $e->getJsonBody();
		} catch (\Payjp\Error\Authentication $e) {
			$err = $e->getJsonBody();
		} catch (\Payjp\Error\Api $e) {
			$err = $e->getJsonBody();
		} catch (\Payjp\Error\Base $e) {
			$err = $e->getJsonBody();
		} catch (Exception $e) {
			$err = $e->getJsonBody();
		} finally {
			//何もしない
		}

		return array($res, $err);
	}

	function retrievePlan($token){
		if(is_null($token)) return array(null, null);
		$res = null;
		$err = null;
		try{
			$res = \Payjp\Subscription::retrieve($token);
		} catch (\Payjp\Error\Card $e) {
			$err = $e->getJsonBody();
		} catch (\Payjp\Error\InvalidRequest $e) {
			$err = $e->getJsonBody();
		} catch (\Payjp\Error\Authentication $e) {
			$err = $e->getJsonBody();
		} catch (\Payjp\Error\Api $e) {
			$err = $e->getJsonBody();
		} catch (\Payjp\Error\Base $e) {
			$err = $e->getJsonBody();
		} catch (Exception $e) {
			$err = $e->getJsonBody();
		} finally {
			//何もしない
		}

		return array($res, $err);
	}

	function changePlan($subscribeToken, $planToken){
		$res = null;
		$err = null;
		try{
			$su = \Payjp\Subscription::retrieve($subscribeToken);
			$su->plan = $planToken;
			$res = $su->save();
		} catch (\Payjp\Error\Card $e) {
			$err = $e->getJsonBody();
		} catch (\Payjp\Error\InvalidRequest $e) {
			$err = $e->getJsonBody();
		} catch (\Payjp\Error\Authentication $e) {
			$err = $e->getJsonBody();
		} catch (\Payjp\Error\Api $e) {
			$err = $e->getJsonBody();
		} catch (\Payjp\Error\Base $e) {
			$err = $e->getJsonBody();
		} catch (Exception $e) {
			$err = $e->getJsonBody();
		} finally {
			//何もしない
		}

		return array($res, $err);
	}

	function getPlanList(){
		//キャッシュファイル形式で対応
		$cacheFilePath = self::getCacheFilePath();
		if(file_exists($cacheFilePath)){
			return soy2_unserialize(file_get_contents($cacheFilePath));
		}

		$list = array();
		try{
			$res = \Payjp\Plan::all(array("limit" => 100));
		} catch (\Payjp\Error\Card $e) {
			$err = $e->getJsonBody();
		} catch (\Payjp\Error\InvalidRequest $e) {
			$err = $e->getJsonBody();
		} catch (\Payjp\Error\Authentication $e) {
			$err = $e->getJsonBody();
		} catch (\Payjp\Error\Api $e) {
			$err = $e->getJsonBody();
		} catch (\Payjp\Error\Base $e) {
			$err = $e->getJsonBody();
		} catch (Exception $e) {
			$err = $e->getJsonBody();
		} finally {
			//何もしない
		}

		if(isset($res)){
			$data = $res->data;
			if(count($data)){
				foreach($data as $v){
					$values = array();
					$planToken = $v->id;
					if(self::checkIsExistsPlanByToken($planToken)){
						$interval = ($v->interval == "month") ? "月" : "?";
						$list[$v->id] = $v->name . "(￥" .$v->amount  ."/" . $interval .")";
					}
				}
			}
		}

		//キャッシュとして保存
		file_put_contents($cacheFilePath, soy2_serialize($list));

		return $list;
	}

	function getCustomerTokenByMailAddress($mailAddress){
		try{
			$userId = (int)SOY2DAOFactory::create("user.SOYShop_UserDAO")->getByMailAddress($mailAddress)->getId();
		}catch(Exception $e){
			return null;
		}
		return self::getCustomerTokenByUserId($userId);
	}

	function getCustomerTokenByUserId(int $userId){
		return soyshop_get_user_attribute_value($userId, self::FIELD_KEY, "string");
	}

	function checkCardExpirationDateByUserId($userId){
		$token = self::getCustomerTokenByUserId($userId);
		if(!strlen($token)) return null;

		$res = null;
		$err = null;
		try{
			$res = \Payjp\Customer::retrieve($token);
		} catch (\Payjp\Error\Card $e) {
			$err = $e->getJsonBody();
		} catch (\Payjp\Error\InvalidRequest $e) {
			$err = $e->getJsonBody();
		} catch (\Payjp\Error\Authentication $e) {
			$err = $e->getJsonBody();
		} catch (\Payjp\Error\Api $e) {
			$err = $e->getJsonBody();
		} catch (\Payjp\Error\Base $e) {
			$err = $e->getJsonBody();
		} catch (Exception $e) {
			$err = $e->getJsonBody();
		} finally {
			//何もしない
		}
		if(isset($res)){
			$cards = $res->cards->data;
			if(count($cards)){
				foreach($cards as $card){
					$res = self::_checkCardExpire($card->exp_year, $card->exp_month);
					if($res) return true;	//一つでもtrueであればtrueを返す
				}
			}
		}
		return false;
	}

	private function _checkCardExpire($year, $month){
		return (strtotime("1 month", mktime(0, 0, 0, $month, 1, $year)) > time());
	}

	function updateCardInfo($userId, $cardToken, $name){
		$token = self::getCustomerTokenByUserId($userId);
		if(!strlen($token)) return false;

		$cu = null;
		$res = null;
		$err = null;
		try{
			$cu = \Payjp\Customer::retrieve($token);
			$res = $cu->cards->create(array(
        		"card" => $cardToken
			));
		} catch (\Payjp\Error\Card $e) {
			$err = $e->getJsonBody();
		} catch (\Payjp\Error\InvalidRequest $e) {
			$err = $e->getJsonBody();
		} catch (\Payjp\Error\Authentication $e) {
			$err = $e->getJsonBody();
		} catch (\Payjp\Error\Api $e) {
			$err = $e->getJsonBody();
		} catch (\Payjp\Error\Base $e) {
			$err = $e->getJsonBody();
		} catch (Exception $e) {
			$err = $e->getJsonBody();
		} finally {
			//何もしない
		}

		// @ToDo エラーハンドリングはどうしよう？
		if(isset($err)){}

		//古いカードの情報を破棄
		if(isset($res)){
			$results = $cu->cards->all(array("limit"=>10, "offset"=>1));
			$cards = $results->data;
			foreach($cards as $card){
				if($card->id != $res->id){
					$card->delete();	//古いカードはすべて削除
				}
			}
		}

		return true;
	}

	function saveCustomerTokenByUserId(string $token="", int $userId){
		$attr = soyshop_get_user_attribute_object($userId, self::FIELD_KEY);
		$attr->setValue($token);
		soyshop_save_user_attribute_object($attr);
	}

	function deleteCustomerTokenByUserId(int $userId){
		self::saveCustomerTokenByUserId("", $userId);
	}

	function getPlanTokenByItemId(int $itemId){
		$token = soyshop_get_item_attribute_value($itemId, self::FIELD_KEY, "string");
		if(strlen($token)) return $token;

		//トークンがなければ作る
		return self::createPlanTokenByItemId($itemId);
	}

	function createPlanTokenByItemId($itemId){
		$item = soyshop_get_item_object($itemId);
		$plan = array("amount" => $item->getSellingPrice(), "currency" => "jpy", "interval" => "month", "name" => $item->getName());

		/** @ToDo いずれは諸々の設定も使えるようにしたい **/
		list($res, $err) = self::registPlan($plan);

		//商品属性に登録
		if(isset($res)){
			$attr = soyshop_get_item_attribute_object($itemId, self::FIELD_KEY);
			$attr->setValue($res->id);
			soyshop_save_item_attribute_object($attr);

			//キャッシュファイルの削除
			$cacheFilePath = self::getCacheFilePath();
			if(file_exists($cacheFilePath)) unlink($cacheFilePath);

			return $attr->getValue();
		}

		return null;
	}

	private function checkIsExistsPlanByToken($token){
		static $attrs;
		if(is_null($attrs)){
			$attrs = array();
			$dao = SOY2DAOFactory::create("shop.SOYShop_ItemAttributeDAO");
			$sql = "SELECT item_id, item_value FROM soyshop_item_attribute ".
					"WHERE item_field_id = :fieldId";
			try{
				$results = $dao->executeQuery($sql, array(":fieldId" => self::FIELD_KEY));
			}catch(Exception $e){
				$results = array();
			}

			if(count($results)){
				foreach($results as $result){
					if(!isset($result["item_id"])) continue;
					$attrs[(int)$result["item_id"]] = $result["item_value"];
				}
			}
		}

		return (array_search($token, $attrs) !== false);
	}

	private function getCacheFilePath(){
		$cacheDir = SOYSHOP_SITE_DIRECTORY . ".cache/pay/";
		if(!file_exists($cacheDir)) mkdir($cacheDir);
		return $cacheDir . "plan.txt";
	}

	function getSubscribeIdAndOrderIdByUserId($userId){
		$orderDao = SOY2DAOFactory::create("order.SOYShop_OrderDAO");
		$sql = "SELECT id, attributes FROM soyshop_order ".
				"WHERE user_id = :userId ".
				"AND payment_status = " . SOYShop_Order::PAYMENT_STATUS_CONFIRMED . " ".
				"AND order_status > " . SOYShop_Order::ORDER_STATUS_INTERIM . " ".
				"AND order_status < " . SOYShop_Order::ORDER_STATUS_CANCELED . " ".
				"ORDER BY id DESC ".
				"LIMIT 1 ".
				"OFFSET ";

		//指定の顧客が何回注文しているか調べる
		try{
			$total = $orderDao->countByUserIdIsRegistered($userId);
		}catch(Exception $e){
			$total = 0;
		}

		$i = 0;
		$res = array();
		if($total > 0){
			for(;;){
				try{
					$res = $orderDao->executeQuery($sql . $i, array(":userId" => $userId));
					if(count($res)) break;
				}catch(Exception $e){
					//
				}

				//すべての注文が付きたら調べるのを止める
				if($i++ > $total) break;
			}
		}

		if(!isset($res[0])) return null;
		$order = $orderDao->getObject($res[0]);

		$attr = $order->getAttribute("payment_pay_jp_recurring.id");
		$subscribeId = (isset($attr["value"])) ? $attr["value"] : null;

		return array($subscribeId, $order->getId());
	}

	function getErrorMessageListOnJS(){
		$errList = PayJpRecurringUtil::getErrorMessageList();

		$script = array();
		$script[] = "var errMsgList = {";
		foreach($errList as $key => $mes){
			$script[] = "\t" . $key . ":\"" . $mes. "\",";
		}

		$script[] = "};";

		return implode("\n", $script);
	}
}
