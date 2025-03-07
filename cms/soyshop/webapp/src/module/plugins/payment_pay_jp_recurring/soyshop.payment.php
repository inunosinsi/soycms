<?php

SOY2::import("module.plugins.payment_pay_jp_recurring.util.PayJpRecurringUtil");
class PayJpRecurringPayment extends SOYShopPayment{

	private $recurringLogic;

	function onSelect(CartLogic $cart){
		$module = new SOYShop_ItemModule();
		$module->setId("payment_pay_jp_recurring");
		$module->setType("payment_module");//typeを指定しておくといいことがある
		$module->setName("定期課金");
		$module->setIsVisible(false);
		$module->setPrice(0);

		$cart->addModule($module);

		//属性の登録
		if(PayJpRecurringUtil::isTestMode()){
			$cart->setOrderAttribute("payment_pay_jp_recurring", "支払方法", "定期課金(PAY.JP※テストモード)");
		}else{
			$cart->setOrderAttribute("payment_pay_jp_recurring", "支払方法", "定期課金");
		}

		//登録されているカード情報で支払(標準で1)
		$isRepeatCharge = (isset($_POST["payment_pay_jp_repeat_recurring"])) ? (int)$_POST["payment_pay_jp_repeat_recurring"] : 1;
		$cart->setAttribute("payment_pay_jp_repeat_recurring", $isRepeatCharge);
	}

	function getName(){
		if(PayJpRecurringUtil::isTestMode()){
			return "定期課金(PAY.JP※テストモード)";
		}else{
			return "定期課金";
		}

	}

	function getDescription(){
		$html = array();
		$html[] = SOYShop_DataSets::get("payment_pay_jp_recurring.description", "定期課金");

		self::prepare();

		//カートを表示している顧客の情報で、カードのトークンが登録されているか？調べる(メールアドレスから顧客情報をたどる)
		$mailAddress = (string)$this->getCart()->getCustomerInformation()->getMailAddress();
		$token = (strlen($mailAddress)) ? $this->recurringLogic->getCustomerTokenByMailAddress($mailAddress) : null;

		if(is_string($token) && strlen($token)){
			$config = PayJpRecurringUtil::getConfig();
			$isRepeatCharge = $this->getCart()->getAttribute("payment_pay_jp_repeat_recurring");
			if(is_null($isRepeatCharge)) $isRepeatCharge = 1;

			$html[] = "<input type=\"hidden\" name=\"payment_pay_jp_repeat_recurring\" value=\"0\">";

			if($isRepeatCharge){
				$html[] = "<br><label><input type=\"checkbox\" name=\"payment_pay_jp_repeat_recurring\" value=\"1\" checked=\"checked\">登録されているカード情報で定期課金を申し込む</label>";
			}else{
				$html[] = "<br><label><input type=\"checkbox\" name=\"payment_pay_jp_repeat_recurring\" value=\"1\">登録されているカード情報で定期課金を申し込む</label>";
			}
		}

		return implode("\n", $html);
	}

	function hasOptionPage(){
		return true;
	}

	function getOptionPage(){
		$cart = $this->getCart();

		//戻る
		if(isset($_GET["back"])){
			$cart->setAttribute("page", "Cart04");
			soyshop_redirect_cart();
			exit;
		}

		//秘密鍵の登録がなければエラー
		self::prepare();
		$config = $this->recurringLogic->getPayJpConfig();
		if(!strlen($config["secret_key"])){
			throw new Exception("秘密鍵が設定されていません。");
		}

		//テストカードモード
		if(!defined("SOYSHOP_CART_TEST_MODE")) define("SOYSHOP_CART_TEST_MODE", PayJpRecurringUtil::isTestMode());

		// トークンを保持していれば、ここで注文を終わらせてしまう
		$userId = (int)$cart->getCustomerInformation()->getId();
		$token = ($userId > 0) ? $this->recurringLogic->getCustomerTokenByUserId($userId) : "";
		
		if(strlen($token)){
			//二回目の購入のチェックがあるか？
			if(strlen((string)$cart->getAttribute("payment_pay_jp_repeat_recurring"))){
				$items = $cart->getItems();
				$itemOrder = array_shift($items);

				//プランIDを登録
				$planToken = $this->recurringLogic->getPlanTokenByItemId($itemOrder->getItemId());
				list($res, $err) = $this->recurringLogic->subscribe($token, $planToken);
				if(is_null($err)){
					//エラーがなければ注文完了
					self::orderComplete($res->id);
					soyshop_redirect_cart();
					exit;
				}
			}
		}

		//出力
		SOY2::import("module.plugins.payment_pay_jp_recurring.option.PayJpRecurringOptionPage");
		$form = SOY2HTMLFactory::createInstance("PayJpRecurringOptionPage");
		$form->execute();
		echo $form->getObject();
	}

	function onPostOptionPage(){

		if(soy2_check_token()){
			self::prepare();

			//会員情報を登録 顧客IDを取得
			$cart = $this->getCart();
			$customerToken = self::registerCustomerByUserId($cart->getCustomerInformation());

			$items = $cart->getItems();
			$itemOrder = array_shift($items);

			//プランIDを登録
			$planToken = $this->recurringLogic->getPlanTokenByItemId($itemOrder->getItemId());

			//定期課金として登録
			list($res, $err) = $this->recurringLogic->subscribe($customerToken, $planToken);
			if(is_null($res)) self::redirectCartOnError($err);

			//tokenを更新して注文完了
			self::orderComplete($res->id);
		}
	}

	private function registerCustomerByUserId(SOYShop_User $user){
		$customer["card"] = $_POST["token"];
		$customer["email"] = $user->getMailAddress();

		list($res, $err) = $this->recurringLogic->registerCustomer($customer);
		unset($err);
		$token = (!is_null($res)) ? $res->id : null;

		if(isset($token)){
			$this->recurringLogic->saveCustomerTokenByUserId((string)$token, (int)$user->getId());
		}else{
			$this->recurringLogic->deleteCustomerTokenByUserId((int)$user->getId());
		}

		return $token;
	}

	private function orderComplete($payjsId){
		$cart = $this->getCart();

		$orderId = $cart->getAttribute("order_id");
		$order = self::getOrderById($orderId);

		//支払を完了する
		$order->setAttribute("payment_pay_jp_recurring.id", array(
			"name" => "PAY.JP定期課金: ID",
			"value" => $payjsId,
			"readonly" => true,
			"hidden" => true,
		));

		$order->setPaymentStatus(SOYShop_Order::PAYMENT_STATUS_CONFIRMED);
		self::orderDao()->updateStatus($order);
		$cart->setAttribute("page", "Complete");

		//セッションのクリア
		PayJpRecurringUtil::clear("myCard");
		PayJpRecurringUtil::clear("name");
		PayJpRecurringUtil::clear("errorCode");
	}

	private function prepare(){
		$this->recurringLogic = SOY2Logic::createInstance("module.plugins.payment_pay_jp_recurring.logic.RecurringLogic");
		$this->recurringLogic->initPayJp();
	}

	private function redirectCartOnError($body=null){
		//原因不明のエラー
		if(is_null($body) || !isset($body["error"])){
			PayJpRecurringUtil::save("errorCode", "other");
			soyshop_redirect_cart("error");
			exit;
		}

		$err = $body["error"];
		$code = (isset($err["code"])) ? $err["code"] : null;

		PayJpRecurringUtil::save("errorCode", $code);
		soyshop_redirect_cart("error");
		exit;
	}

	private function getOrderById($orderId){
		try{
			return self::orderDao()->getById($orderId);
		}catch(Exception $e){
			return new SOYShop_Order();
		}
	}

	private function orderDao(){
		static $dao;
		if(is_null($dao)) $dao = SOY2DAOFactory::create("order.SOYShop_OrderDAO");
		return $dao;
	}
}

SOYShopPlugin::extension("soyshop.payment",			"payment_pay_jp_recurring", "PayJpRecurringPayment");
SOYShopPlugin::extension("soyshop.payment.option",	"payment_pay_jp_recurring", "PayJpRecurringPayment");
