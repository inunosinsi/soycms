<?php
class ReturnsSlipNumberOrderDetailMail extends SOYShopOrderDetailMailBase{

	function getMailType($mode){
		if($mode == "order"){
			return array("return" => array("id" => "return", "title" => "返送完了メール"));
		}
	}

	//メール種別でメール文面編集画面のGETパラメータと一致すればtrueにする文字列
	function activeKey(){
		return "return";
	}

	function autoSendConfig(){
		SOY2::import("module.plugins.returns_slip_number.util.ReturnsSlipNumberUtil");
		return array(ReturnsSlipNumberUtil::STATUS_CODE => "return");
	}
}
SOYShopPlugin::extension("soyshop.order.detail.mail", "returns_slip_number", "ReturnsSlipNumberOrderDetailMail");
