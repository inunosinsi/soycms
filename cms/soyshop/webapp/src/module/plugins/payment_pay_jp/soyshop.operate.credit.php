<?php

class PayJpOperateCredit extends SOYShopOperateCreditBase{

	private $payJpLogic;

	function doPostOnOrderDetailPage(SOYShop_Order $order){

		if(isset($_POST["capture"]) || isset($_POST["cancel"])){
			self::prepare();
			$attr = $order->getAttribute("payment_pay_jp.id");
			$token = (isset($attr["value"])) ? $attr["value"] : null;

			$dao = SOY2DAOFactory::create("order.SOYShop_OrderDAO");

			try{
				$ch = \Payjp\Charge::retrieve($token);

				//支払確定
				if(isset($_POST["capture"])){
					$ch->capture();
					$order->setPaymentStatus(SOYShop_Order::PAYMENT_STATUS_CONFIRMED);
					$content = "支払いを確定しました。";
				}

				//キャンセル
				if(isset($_POST["cancel"])){
					$ch->refund();
					$order->setPaymentStatus(SOYShop_Order::PAYMENT_STATUS_WAIT);
					$order->setStatus(SOYShop_Order::ORDER_STATUS_CANCELED);
					$content = "支払いをキャンセルしました。";
				}

				$dao->updateStatus($order);

				//履歴に登録
				self::insertHistory($order->getId(), $content);

				SOY2PageController::jump("Order.Detail." . $order->getId() . "?updated");
			} catch (Exception $e) {
				//何もしない
			} finally {
				//何もしない
			}
		}

		SOY2PageController::jump("Order.Detail." . $order->getId() . "?error");
	}

	function doPostOnUserDetailPage(SOYShop_User $user){}

	function getFormOnOrderDetailPageTitle(SOYShop_Order $order){
		if(array_key_exists("payment_pay_jp", $order->getModuleList())){
			return "クレジット決済";
		}else{
			return null;
		}
	}

	function getFormOnOrderDetailPageContent(SOYShop_Order $order){
		if(array_key_exists("payment_pay_jp", $order->getModuleList())){
			self::prepare();

			//capture 支払い確定済みか？ expired 支払い確定期限 captured_at 支払い処理日 refunded 返金済みか？
			$params = array("capture" => true, "expired" => null, "captured_at" => null, "refunded" => false);

			//ここで支払状況を調べる
			$attr = $order->getAttribute("payment_pay_jp.id");
			$token = (isset($attr["value"])) ? $attr["value"] : null;
			
			if(isset($token)){
				try{
					$res = \Payjp\Charge::retrieve($token);
					$params["capture"] = $res->captured;
					$params["expired"] = $res->expired_at;
					$params["captured_at"] = $res->captured_at;
					$params["refunded"] = $res->refunded;
				} catch (Exception $e) {
					//何もしない
				} finally {
					//何もしない
				}
			}

			//返金等
			if($params["capture"]){
				$pageName = "Capture";
			} else {
				$pageName = "NoCapture";
			}

			SOY2::import("module.plugins.payment_pay_jp.form.order." . $pageName . "Page");
			$form = SOY2HTMLFactory::createInstance($pageName . "Page");
			$form->setOrder($order);
			$form->setParams($params);
			$form->execute();
			return $form->getObject();
		}
	}

	function getFormOnUserDetailPageTitle(){
		return "クレジットカード会員詳細";
	}

	function getFormOnUserDetailPageContent(SOYShop_User $user){
		SOY2::import("module.plugins.payment_pay_jp.form.user.CreditMemberPage");
		$form = SOY2HTMLFactory::createInstance("CreditMemberPage");
		$form->setUser($user);
		//$form->setParams($params);
		$form->execute();
		return $form->getObject();
	}

	/**
	 * 変更履歴をSOY Shop側でも持っておく
	 */
	private function insertHistory($orderId, $content){
		$dao = self::historyDao();
		$history = new SOYShop_OrderStateHistory();
		$history->setOrderId($orderId);
		$history->setAuthor("管理人");
		$history->setContent($content);
		$history->setDate(time());

		try{
			$dao->insert($history);
		}catch(Exception $e){
			//
		}
	}

	private function historyDao(){
		static $dao;
		if(is_null($dao)) $dao = SOY2DAOFactory::create("order.SOYShop_OrderStateHistoryDAO");
		return $dao;
	}

	private function prepare(){
		$this->payJpLogic = SOY2Logic::createInstance("module.plugins.payment_pay_jp.logic.PayJpLogic");
		$this->payJpLogic->initPayJp();
	}
}

SOYShopPlugin::extension("soyshop.operate.credit", "payment_pay_jp", "PayJpOperateCredit");
