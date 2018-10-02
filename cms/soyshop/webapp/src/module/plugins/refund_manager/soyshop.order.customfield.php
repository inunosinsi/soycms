<?php

class RefundManagerOrderCustomfield extends SOYShopOrderCustomfield{

	function clear(CartLogic $cart){}
	function doPost($param){}
	function order(CartLogic $cart){}
	function complete(CartLogic $cart){}
	function hasError($param){}
	function getForm(CartLogic $cart){}

	function display($orderId){

		self::prepare();
		list($values, $isProcessed) = RefundManagerUtil::get($orderId);
		if(count($values)){
			$html = array();
			if($isProcessed) $html[] = "【処理済み】";
			$html[] = "種別　　　" . RefundManagerUtil::getTypeTextByOrderId($orderId);
			if(isset($values["refund"]) && (int)$values["refund"] > 0) $html[] = "返金額　　" . $values["refund"] . "円";
			if(isset($values["increase"]) && (int)$values["increase"] > 0) $html[] = "増額　　　" . $values["increase"] . "円";
			if(isset($values["bank_name"]) && strlen($values["bank_name"])) $html[] = "銀行名　　" . $values["bank_name"];
			if(isset($values["account"]) && strlen($values["account"])) $html[] = "銀行口座　" . $values["account"];	//修正前の値の確認用
			if(isset($values["branch"]) && strlen($values["branch"])) $html[] = "支店名　　" . $values["branch"];
			if(isset($values["account_type"]) && strlen($values["account_type"]) === 1) $html[] = "口座種別　" . RefundManagerUtil::getAccountTypeText($values["account_type"]);
			if(isset($values["account_type"]) && strlen($values["account_type"]) > 1) $html[] = "口座種別　" . $values["account_type"];		//修正前の値の確認用
			if(isset($values["account_number"]) && strlen($values["account_number"])) $html[] = "口座番号　" . $values["account_number"];
			if(isset($values["name"]) && strlen($values["name"]))$html[] = "名義人　　" . $values["name"];
			if(isset($values["comment"]) && strlen($values["comment"])) $html[] = "コメント\n" . $values["comment"];
			return array(
				array(
					"name" => "返金関連",
					"value" => implode("\n", $html)
				)
			);
		}

		return array();
	}

	/**
	 * @param int $orderID
	 * @return array labelとformの連想配列を格納
	 */
	function edit($orderId){
		//マイページでは表示しない
		if(!defined("SOYSHOP_MYPAGE_MODE") || !SOYSHOP_MYPAGE_MODE){
			SOY2::import("module.plugins.refund_manager.form.RefundManagerForm");
			$form = SOY2HTMLFactory::createInstance("RefundManagerForm");
			$form->setOrderId($orderId);
			$form->execute();
			return array(array("label" => "返金関連", "form" => $form->getObject()));
		}
	}

	/**
	 * 編集画面で編集するための設定内容を取得する
	 * @param int $orderId
	 * @return array saveするための配列
	 */
	function config($orderId){
		//マイページでは読み込まない
		if(defined("SOYSHOP_MYPAGE_MODE") && SOYSHOP_MYPAGE_MODE) return array();

		if(isset($_POST["Customfield"]["refund_manager"])){
			self::prepare();
			$isProcessed = (isset($_POST["Customfield"]["refund_manager_processed"])) ? 1 : null;
			RefundManagerUtil::save($_POST["Customfield"]["refund_manager"], $isProcessed, $orderId);
		}

		//ここで完結させるため、returnで空の配列を返す
		return array();
	}

	private function prepare(){
		SOY2::import("module.plugins.refund_manager.util.RefundManagerUtil");
	}
}
SOYShopPlugin::extension("soyshop.order.customfield", "refund_manager", "RefundManagerOrderCustomfield");
