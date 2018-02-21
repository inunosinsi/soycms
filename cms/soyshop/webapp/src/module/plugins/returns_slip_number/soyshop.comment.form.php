<?php
class ReturnsSlipNumberComment extends SOYShopCommentFormBase{

	function doPost(SOYShop_Order $order){

		if(isset($_POST["ReturnsSlipNumber"]) && strlen($_POST["ReturnsSlipNumber"])){
			self::getLogic()->save($order->getId(), $_POST["ReturnsSlipNumber"]);

			/** @ToDo 履歴を残す様に修正 **/
			return "返送伝票番号「" . $_POST["ReturnsSlipNumber"] . "」を登録しました。";
		}

		return "";
	}

	function getForm(SOYShop_Order $order){
		$attr = self::getLogic()->getAttribute($order->getId());

		if(is_null($attr->getOrderId())){
			SOY2::import("module.plugins.returns_slip_number.form.ReturnsSlipNumberFormPage");
			$form = SOY2HTMLFactory::createInstance("ReturnsSlipNumberFormPage");
			$form->setPluginObj($this);
			$form->execute();
			return $form->getObject();
		}
	}

	private function getLogic(){
		static $logic;
		if(is_null($logic)) $logic = SOY2Logic::createInstance("module.plugins.returns_slip_number.logic.ReturnsSlipNumberLogic");
		return $logic;
	}
}
SOYShopPlugin::extension("soyshop.comment.form", "returns_slip_number", "ReturnsSlipNumberComment");
