<?php
class ReturnsSlipNumberComment extends SOYShopCommentFormBase{

	function doPost(SOYShop_Order $order){

		if(isset($_POST["ReturnsSlipNumber"]) && strlen($_POST["ReturnsSlipNumber"])){
			$attr = self::getLogic()->getAttribute($order->getId());
			$slipNumber = $attr->getValue1();
			if(strlen($slipNumber)){
				$slipNumber .= "," . trim($_POST["ReturnsSlipNumber"]);
			}else{
				$slipNumber = trim($_POST["ReturnsSlipNumber"]);
			}

			self::getLogic()->save($order->getId(), $slipNumber);

			//履歴を残す
			return "返送伝票番号「" . self::getLogic()->convert($_POST["ReturnsSlipNumber"]) . "」を登録しました。";
		}

		return "";
	}

	function getForm(SOYShop_Order $order){
		SOY2::import("module.plugins.returns_slip_number.form.ReturnsSlipNumberFormPage");
		$form = SOY2HTMLFactory::createInstance("ReturnsSlipNumberFormPage");
		$form->setPluginObj($this);
		$form->setOrderId($order->getId());
		$form->execute();
		return $form->getObject();
	}

	private function getLogic(){
		static $logic;
		if(is_null($logic)) $logic = SOY2Logic::createInstance("module.plugins.returns_slip_number.logic.ReturnsSlipNumberLogic");
		return $logic;
	}
}
SOYShopPlugin::extension("soyshop.comment.form", "returns_slip_number", "ReturnsSlipNumberComment");
