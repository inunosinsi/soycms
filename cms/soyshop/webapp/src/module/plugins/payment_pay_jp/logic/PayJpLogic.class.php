<?php

class PayJpLogic extends SOY2LogicBase {

	const FIELD_KEY = "payment_pay_jp_token";

	function __construct(){
		SOY2::import("module.plugins.payment_pay_jp.util.PayJpUtil");
	}

	function initPayJp(){
		$config = $this->getPayJpConfig();

		if(isset($config["secret_key"]) && strlen($config["secret_key"])){
			//PAY.JPのlibを読み込む
			//require_once(SOYSHOP_WEBAPP . "src/module/plugins/payment_pay_jp/payjp/v1/init.php");
			// PAY.JP v2
			require_once SOY2::RootDir() . "module/plugins/payment_pay_jp/payjp/v2/vendor/autoload.php";
			\Payjp\Payjp::setApiKey(trim($config["secret_key"]));
		}
	}

	function getPayJpConfig(){
		static $c;
		if(is_null($c)){
			$cnf = PayJpUtil::getConfig();
			$c = (isset($cnf["sandbox"]) && $cnf["sandbox"] == 1) ? $cnf["test"] : $cnf["production"];
		}
		return $c;
	}

	//カード番号のトークンを作成
	function createToken($card){
		$res = null;
		$err = null;
		try{
			$res = \Payjp\Token::create($card);
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

	function charge($card){
		$res = null;
		$err = null;
		try{
			$res = \Payjp\Charge::create($card);
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

	function retrieveCustomer($token){
		if(is_null($token)) return array(null, null);
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

		return array($res, $err);
	}

	function getCustomerTokenByMailAddress(string $mailAddress){
		$userId = soyshop_get_user_object_by_mailaddress($mailAddress)->getId();
		if(!is_numeric($userId)) return null;
		return self::getCustomerTokenByUserId($userId);
	}

	function getCustomerTokenByUserId(int $userId){
		return self::getTokenAttributeByUserId($userId)->getValue();
	}

	function getTokenAttributeByUserId(int $userId){
		return soyshop_get_user_attribute_object($userId, self::FIELD_KEY);
	}

	function saveCustomerTokenByUserId(string $token, int $userId){
		$attr = soyshop_get_user_attribute_object($userId, self::FIELD_KEY);
		$attr->setValue($token);
		soyshop_save_user_attribute_object($attr);
	}

	function deleteCustomerTokenByUserId($userId){
		try{
			SOY2DAOFactory::create("user.SOYShop_UserAttributeDAO")->delete($userId, self::FIELD_KEY);
		}catch(Exception $e){
			//
		}
	}

	function getErrorMessageListOnJS(){
		$errList = PayJpUtil::getErrorMessageList();

		$script = array();
		$script[] = "var errMsgList = {";
		foreach($errList as $key => $mes){
			$script[] = "\t" . $key . ":\"" . $mes. "\",";
		}

		$script[] = "};";

		return implode("\n", $script);
	}
}
