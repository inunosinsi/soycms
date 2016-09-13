<?php
class LoggingSlipNumberComment extends SOYShopCommentFormBase{
	
	function doPost(SOYShop_Order $order){
		
		if(isset($_POST["LoggingSlipNumber"]) && strlen($_POST["LoggingSlipNumber"])){
			self::getLogic()->save($order->getId(), $_POST["LoggingSlipNumber"]);
		}
	}
	
	function getForm(SOYShop_Order $order){
		$attr = self::getLogic()->getAttribute($order->getId());
		
		if(is_null($attr->getOrderId())){
			SOY2::import("module.plugins.logging_slip_number.form.LoggingSlipNumberFormPage");
			$form = SOY2HTMLFactory::createInstance("LoggingSlipNumberFormPage");
			$form->setPluginObj($this);
			$form->execute();
			return $form->getObject();
		}
	}
	
	private function getLogic(){
		static $logic;
		if(is_null($logic)) $logic = SOY2Logic::createInstance("module.plugins.logging_slip_number.logic.SlipNumberLogic");
		return $logic;
	}
}
SOYShopPlugin::extension("soyshop.comment.form", "logging_slip_number", "LoggingSlipNumberComment");
?>