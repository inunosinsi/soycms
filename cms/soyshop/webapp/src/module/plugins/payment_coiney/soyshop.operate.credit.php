<?php

class CoineyOperateCredit extends SOYShopOperateCreditBase{

	private $apiLogic;

	function doPostOnOrderDetailPage(SOYShop_Order $order){
/**
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
			} catch (Exception $e) {
				//何もしない
			} finally {
				//何もしない
			}
		}

		//注文詳細の他の機能が停止するのでジャンプしない
**/
	}

	function doPostOnUserDetailPage(SOYShop_User $user){}

	function getFormOnOrderDetailPageTitle(SOYShop_Order $order){
		if(array_key_exists("payment_coiney", $order->getModuleList())){
			return "クレジット決済";
		}else{
			return null;
		}
	}

	function getFormOnOrderDetailPageContent(SOYShop_Order $order){
		if(array_key_exists("payment_coiney", $order->getModuleList())){
			$attr = $order->getAttribute("payment_coiney.id");
			$paymentId = (isset($attr["value"])) ? $attr["value"] : null;

			if(isset($paymentId)){
				self::prepare();

				SOY2::import("module.plugins.payment_coiney.form.CoineyOperatePage");
				$form = SOY2HTMLFactory::createInstance("CoineyOperatePage");
				$form->setOrder($order);
				$form->setInfo($this->apiLogic->getPaymentInfoById($paymentId));
				$form->execute();
				return $form->getObject();
			}
		}
	}

	function getFormOnUserDetailPageTitle(SOYShop_User $user){}
	function getFormOnUserDetailPageContent(SOYShop_User $user){}

	/**
	 * 変更履歴をSOY Shop側でも持っておく
	 */
	private function insertHistory($orderId, $content){
		$dao = self::historyDao();
		$history = new SOYShop_OrderStateHistory();
		$history->setOrderId($orderId);
		$history->setAuthor(SOY2Logic::createInstance("logic.order.OrderHistoryLogic")->getAuthor());
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
		$this->apiLogic = SOY2Logic::createInstance("module.plugins.payment_coiney.logic.CoineyApiLogic");
	}
}

SOYShopPlugin::extension("soyshop.operate.credit", "payment_coiney", "CoineyOperateCredit");
