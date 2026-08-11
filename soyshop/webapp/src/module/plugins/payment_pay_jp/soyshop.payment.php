<?php

SOY2::import("module.plugins.payment_pay_jp.util.PayJpUtil");
class PayJpPayment extends SOYShopPayment{

	private $payJpLogic;

	function onSelect(CartLogic $cart){
		$module = new SOYShop_ItemModule();
		$module->setId("payment_pay_jp");
		$module->setType("payment_module");//typeを指定しておくといいことがある
		$module->setName("クレジット支払");
		$module->setIsVisible(false);
		$module->setPrice(0);

		$cart->addModule($module);

		//属性の登録
		if(PayJpUtil::isTestMode()){
			$cart->setOrderAttribute("payment_pay_jp", "支払方法", "クレジットカード支払い(PAY.JP※テストモード)");
		}else{
			$cart->setOrderAttribute("payment_pay_jp", "支払方法", "クレジットカード支払い");
		}

		//登録されているカード情報で支払(標準で1)
		$isRepeatCharge = (isset($_POST["payment_pay_jp_repeat_charge"])) ? (int)$_POST["payment_pay_jp_repeat_charge"] : 1;
		$cart->setAttribute("payment_pay_jp_repeat_charge", $isRepeatCharge);
	}

	function getName(){
		if(PayJpUtil::isTestMode()){
			return "クレジットカード支払い(PAY.JP※テストモード)";
		}else{
			return "クレジットカード支払い";
		}

	}

	function getDescription(){
		$html = array();
		$html[] = SOYShop_DataSets::get("payment_pay_jp.description", "クレジットカードで支払います。");

		self::prepare();

		//カートを表示している顧客の情報で、カードのトークンが登録されているか？調べる(メールアドレスから顧客情報をたどる)
		$mailAddress = (string)$this->getCart()->getCustomerInformation()->getMailAddress();
		$token = (strlen($mailAddress)) ? $this->payJpLogic->getCustomerTokenByMailAddress($mailAddress) : null;

		if(is_string($token) && strlen($token)){
			$config = PayJpUtil::getConfig();
			if(isset($config["select"]) && $config["select"] == 1){
				$isRepeatCharge = $this->getCart()->getAttribute("payment_pay_jp_repeat_charge");
				if(is_null($isRepeatCharge)) $isRepeatCharge = 1;

				$html[] = "<input type=\"hidden\" name=\"payment_pay_jp_repeat_charge\" value=\"0\">";

				if($isRepeatCharge){
					$html[] = "<br><label><input type=\"checkbox\" name=\"payment_pay_jp_repeat_charge\" value=\"1\" checked=\"checked\">登録されているカード情報で支払する</label>";
				}else{
					$html[] = "<br><label><input type=\"checkbox\" name=\"payment_pay_jp_repeat_charge\" value=\"1\">登録されているカード情報で支払する</label>";
				}
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
		$config = $this->payJpLogic->getPayJpConfig();
		if(!strlen($config["secret_key"])){
			throw new Exception("秘密鍵が設定されていません。");
		}

		//テストカードモード
		if(!defined("SOYSHOP_CART_TEST_MODE")) define("SOYSHOP_CART_TEST_MODE", PayJpUtil::isTestMode());

		// トークンを保持していれば、ここで注文を終わらせてしまう
		$userId = (int)$cart->getCustomerInformation()->getId();
		$token = ($userId > 0) ? $this->payJpLogic->getCustomerTokenByUserId($userId) : null;

		if(isset($token)){
			//二回目の購入のチェックがあるか？
			$isRepeatCharge = $cart->getAttribute("payment_pay_jp_repeat_charge");
			if($isRepeatCharge){
				$myCard = array(
					'customer' => $token,
					'amount' => $cart->getTotalPrice(),
					'currency' => 'jpy',
					"capture" => PayJpUtil::isCapture(),
					"description" => "payment via soyshop's cart."
				);

				list($res, $err) = $this->payJpLogic->charge($myCard);
				if(is_null($err)){
					//エラーがなければ注文完了
					self::orderComplete($res->id);
					soyshop_redirect_cart();
					exit;
				}

				//削除
				$this->payJpLogic->deleteCustomerTokenByUserId($userId);
			}
		}

		//出力
		SOY2::import("module.plugins.payment_pay_jp.option.PayJpOptionPage");
		$form = SOY2HTMLFactory::createInstance("PayJpOptionPage");
		$form->execute();
		echo $form->getObject();
	}

	function onPostOptionPage(){

		if(soy2_check_token()){
			self::prepare();

			$isMember = (isset($_POST["member"]) && $_POST["member"] == 1) ? 1 : 0;
			PayJpUtil::save("member", $isMember);

			$token = (isset($_POST["token"])) ? $_POST["token"] : null;
			$cart = $this->getCart();

	 		//仮入金モード(captureで指定)
		 	$chargeCard = array(
				'card' => $token,
				'amount' => $cart->getTotalPrice(),
				'currency' => 'jpy',
				"capture" => PayJpUtil::isCapture(),
				"description" => "payment via soyshop's cart.",
				"three_d_secure" => (PayJpUtil::is3DSecureRedirectType()) ? "true" : "false"
			);			

			//作成したカード番号のトークンで購入
			list($res, $err) = $this->payJpLogic->charge($chargeCard);			
			if(is_null($res)) self::redirectCartOnError($err);

			// カードのトークンを保持
			PayJpUtil::save("myCardToken", $token);

			//tokenを更新して注文完了
			self::orderComplete($res->id);
		}
	}

	private function orderComplete(string $payjsId){
		$cart = $this->getCart();

		$orderId = $cart->getAttribute("order_id");
		$order = soyshop_get_order_object($orderId);
	
		//支払を完了する
		$order->setAttribute("payment_pay_jp.id", array(
			"name" => "PAY.JP決済: ID",
			"value" => $payjsId,
			"readonly" => true,
			"hidden" => true,
		));

		//会員情報を登録
		$isMember = PayJpUtil::get("member");
		if($isMember){
			self::saveTokenByUserId($cart->getCustomerInformation());
		}

		//仮入金の場合は支払待ちにする(本売上のみステータスを変更)
		if(PayJpUtil::isCapture()){
			$order->setPaymentStatus(SOYShop_Order::PAYMENT_STATUS_CONFIRMED);
		}

		// 3Dセキュア
		$endpoint = "";
		if(PayJpUtil::is3DSecureRedirectType()){
			$endpoint = self::_buildEndpointUrl($payjsId);

			$order->setStatus(SOYShop_Order::ORDER_STATUS_INTERIM);
			$order->setPaymentStatus(SOYShop_Order::PAYMENT_STATUS_ERROR);
		}

		soyshop_get_hash_table_dao("order")->updateStatus($order);

		//セッションのクリア
		PayJpUtil::clear("myCardToken");
		PayJpUtil::clear("name");
		PayJpUtil::clear("member");
		PayJpUtil::clear("errorCode");

		if(strlen($endpoint)){
			header("location:".$endpoint);
			exit;
		}else{
			// CompleteページにorderComplete()があるが、エラーにならずに回避してくれるのでここでorderComplete()を実行しておく
			$cart->setAttribute("page", "Complete");
			$cart->orderComplete();	
		}
	}

	private function _buildEndpointUrl(string $payjsId){
		// https://pay.jp/docs/charge-tds のリダイレクト型を参照
		$endpoint = "https://api.pay.jp/v1/tds/";
		// $res["id"]はcharge_idに該当
		$endpoint .= $payjsId."/start";

		$cnf = $this->payJpLogic->getPayJpConfig();
		$endpoint .= "?publickey=".$cnf["public_key"];
		$endpoint .= "&back=SOYShop";
		return $endpoint;
	}

	private function prepare(){
		$this->payJpLogic = SOY2Logic::createInstance("module.plugins.payment_pay_jp.logic.PayJpLogic");
		$this->payJpLogic->initPayJp();
	}

	private function redirectCartOnError($body=null){
		//原因不明のエラー
		if(is_null($body) || !isset($body["error"])){
			PayJpUtil::save("errorCode", "other");
			soyshop_redirect_cart("error");
			exit;
		}

		$err = $body["error"];
		$code = (isset($err["code"])) ? $err["code"] : null;

		PayJpUtil::save("errorCode", $code);
		soyshop_redirect_cart("error");
		exit;
	}

	private function saveTokenByUserId(SOYShop_User $user){
		$myCardToken = PayJpUtil::get("myCardToken");
		if(is_null($myCardToken)) return;

		//カード番号のトークンを作成
		list($res, $err) = $this->payJpLogic->createToken(array("card" => $myCardToken));
		if(is_null($res)) self::redirectCartOnError($err);

		$customer["card"] = $myCardToken;
		$customer["email"] = $user->getMailAddress();

		list($res, $err) = $this->payJpLogic->registerCustomer($customer);
		$token = (!is_null($res)) ? $res->id : null;

		if(isset($token)){
			$this->payJpLogic->saveCustomerTokenByUserId($token, $user->getId());
		}else{
			$this->payJpLogic->deleteCustomerTokenByUserId($user->getId());
		}
	}
}

SOYShopPlugin::extension("soyshop.payment",			"payment_pay_jp", "PayJpPayment");
SOYShopPlugin::extension("soyshop.payment.option",	"payment_pay_jp", "PayJpPayment");
