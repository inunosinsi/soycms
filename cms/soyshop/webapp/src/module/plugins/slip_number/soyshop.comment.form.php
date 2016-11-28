<?php
class SlipNumberComment extends SOYShopCommentFormBase{
	
	function doPost(SOYShop_Order $order){
		
		if(isset($_POST["SlipNumber"]) && strlen($_POST["SlipNumber"])){
			self::getLogic()->save($order->getId(), $_POST["SlipNumber"]);
			
			/** @ToDo 履歴を残す様に修正 **/
			return "伝票番号「" . $_POST["SlipNumber"] . "」を登録しました。"; 
		}
		
		return "";
	}
	
	function getForm(SOYShop_Order $order){
		$attr = self::getLogic()->getAttribute($order->getId());
		
		if(is_null($attr->getOrderId())){
			SOY2::import("module.plugins.slip_number.form.SlipNumberFormPage");
			$form = SOY2HTMLFactory::createInstance("SlipNumberFormPage");
			$form->setPluginObj($this);
			$form->execute();
			return $form->getObject();
		}
	}
	
	private function getLogic(){
		static $logic;
		if(is_null($logic)) $logic = SOY2Logic::createInstance("module.plugins.slip_number.logic.SlipNumberLogic");
		return $logic;
	}
}
SOYShopPlugin::extension("soyshop.comment.form", "slip_number", "SlipNumberComment");
?>