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
		\Payjp\Payjp::setApiKey($config["key"]);
	}

	function getPayJpConfig(){
		static $config;
		if(is_null($config)){
			$conf = PayJpRecurringUtil::getConfig();
			if(isset($conf["sandbox"]) && $conf["sandbox"] == 1){
				$config = $conf["test"];
			}else{
				$config = $conf["public"];
			}
		}
		return $config;
	}

	function registCustomer($customer){
		$res = null;
		$err = null;
		try{
			$res = \Payjp\Customer::create($customer);
		} catch (Error\Card $e) {
			$err = $e->getJsonBody();
		} catch (Error\InvalidRequest $e) {
			$err = $e->getJsonBody();
		} catch (Error\Authentication $e) {
			$err = $e->getJsonBody();
		} catch (Error\Api $e) {
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
		} catch (Error\Card $e) {
			$err = $e->getJsonBody();
		} catch (Error\InvalidRequest $e) {
			$err = $e->getJsonBody();
		} catch (Error\Authentication $e) {
			$err = $e->getJsonBody();
		} catch (Error\Api $e) {
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
		} catch (Error\Card $e) {
			$err = $e->getJsonBody();
		} catch (Error\InvalidRequest $e) {
			$err = $e->getJsonBody();
		} catch (Error\Authentication $e) {
			$err = $e->getJsonBody();
		} catch (Error\Api $e) {
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

	function retrieveCustomer($token){
		if(is_null($token)) return array(null, null);
		$res = null;
		$err = null;
		try{
			$res = \Payjp\Customer::retrieve($token);
		} catch (Error\Card $e) {
			$err = $e->getJsonBody();
		} catch (Error\InvalidRequest $e) {
			$err = $e->getJsonBody();
		} catch (Error\Authentication $e) {
			$err = $e->getJsonBody();
		} catch (Error\Api $e) {
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

	function getCustomerTokenByMailAddress($mailAddress){
		try{
			$userId = SOY2DAOFactory::create("user.SOYShop_UserDAO")->getByMailAddress($mailAddress)->getId();
		}catch(Exception $e){
			return null;
		}
		return self::getCustomerTokenByUserId($userId);
	}

	function getCustomerTokenByUserId($userId){
		return self::getTokenAttributeByUserId($userId)->getValue();
	}

	function getTokenAttributeByUserId($userId){
		try{
			return self::userAttrDao()->get($userId, self::FIELD_KEY);
		}catch(Exception $e){
			$attr = new SOYShop_UserAttribute();
			$attr->setUserId($userId);
			$attr->setFieldId(self::FIELD_KEY);
			return $attr;
		}
	}

	function saveCustomerTokenByUserId($token, $userId){
		$attr = $this->getTokenAttributeByUserId($userId);
		$attr->setValue($token);

		try{
			self::userAttrDao()->insert($attr);
		}catch(Exception $e){
			try{
				self::userAttrDao()->update($attr);
			}catch(Exception $e){
				var_dump($e);
			}
		}
	}

	function deleteCustomerTokenByUserId($userId){
		try{
			self::userAttrDao()->delete($userId, self::FIELD_KEY);
		}catch(Exception $e){
			//
		}
	}

	function getPlanTokenByItemId($itemId){
		$itemAttrDao = SOY2DAOFactory::create("shop.SOYShop_ItemAttributeDAO");
		try{
			$token = $itemAttrDao->get($itemId, self::FIELD_KEY)->getValue();
		}catch(Exception $e){
			$token = null;
		}

		if(isset($token)) return $token;

		//トークンがなければ作る
		return self::createPlanTokenByItemId($itemId);
	}

	function createPlanTokenByItemId($itemId){
		try{
			$item = SOY2DAOFactory::create("shop.SOYShop_ItemDAO")->getById($itemId);
		}catch(Exception $e){
			var_dump($e);
			//return null;
		}

		$plan = array("amount" => $item->getSellingPrice(), "currency" => "jpy", "interval" => "month", "name" => $item->getName());

		/** @ToDo いずれは諸々の設定も使えるようにしたい **/
		list($res, $err) = self::registPlan($plan);

		//商品属性に登録
		if(isset($res)){
			$itemAttrDao = SOY2DAOFactory::create("shop.SOYShop_ItemAttributeDAO");
			$attr = new SOYShop_ItemAttribute();
			$attr->setItemId($itemId);
			$attr->setFieldId(self::FIELD_KEY);
			$attr->setValue($res->id);

			try{
				$itemAttrDao->insert($attr);
			}catch(Exception $e){
				try{
					$itemAttrDao->update($attr);
				}catch(Exception $e){
					var_dump($e);
				}
			}

			return $attr->getValue();
		}

		return null;
	}

	function getSubscribeIdByUserId($userId){
		$orderDao = SOY2DAOFactory::create("order.SOYShop_OrderDAO");
		$sql = "SELECT * FROM soyshop_order ".
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
					if(count($res[0])) break;
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
		return (isset($attr["value"])) ? $attr["value"] : null;
	}

	private function userAttrDao(){
		static $dao;
		if(is_null($dao)) $dao = SOY2DAOFactory::create("user.SOYShop_UserAttributeDAO");
		return $dao;
	}
}
