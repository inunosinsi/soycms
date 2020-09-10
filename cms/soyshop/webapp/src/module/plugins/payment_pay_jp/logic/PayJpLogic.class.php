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
			require_once(SOYSHOP_WEBAPP . "src/module/plugins/payment_pay_jp/payjp/init.php");
			\Payjp\Payjp::setApiKey($config["secret_key"]);
		}
	}

	function getPayJpConfig(){
		static $config;
		if(is_null($config)){
			$conf = PayJpUtil::getConfig();
			if(isset($conf["sandbox"]) && $conf["sandbox"] == 1){
				$config = $conf["test"];
			}else{
				$config = $conf["production"];
			}
		}
		return $config;
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

	private function userAttrDao(){
		static $dao;
		if(is_null($dao)) $dao = SOY2DAOFactory::create("user.SOYShop_UserAttributeDAO");
		return $dao;
	}
}
