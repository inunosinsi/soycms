<?php
class SlipNumberComment extends SOYShopCommentFormBase{

	function doPost(SOYShop_Order $order){

		if(isset($_POST["SlipNumber"]) && strlen($_POST["SlipNumber"])){
			self::getLogic()->add($order->getId(), $_POST["SlipNumber"]);

			//履歴を残す
			return "伝票番号「" . self::getLogic()->convert($_POST["SlipNumber"]) . "」を登録しました。";
		}

		return "";
	}

	function getForm(SOYShop_Order $order){
		SOY2::import("module.plugins.slip_number.form.SlipNumberFormPage");
		$form = SOY2HTMLFactory::createInstance("SlipNumberFormPage");
		$form->setPluginObj($this);
		$form->setOrderId($order->getId());
		$form->execute();
		return $form->getObject();
	}

	private function getLogic(){
		static $logic;
		if(is_null($logic)) $logic = SOY2Logic::createInstance("module.plugins.slip_number.logic.SlipNumberLogic");
		return $logic;
	}
}
SOYShopPlugin::extension("soyshop.comment.form", "slip_number", "SlipNumberComment");
