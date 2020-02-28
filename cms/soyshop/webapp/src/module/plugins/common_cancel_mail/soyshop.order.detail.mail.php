<?php
class CommonCancelMailOrderDetailMail extends SOYShopOrderDetailMailBase{

	function getMailType($mode){
		if($mode == "order"){
			return array("cancel" => array("id" => "cancel", "title" => "キャンセルのメール"));
		}
	}

	//メール種別でメール文面編集画面のGETパラメータと一致すればtrueにする文字列
	function activeKey(){
		return "cancel";
	}

	function autoSendConfig(){
		SOY2::import("domain.order.SOYShop_Order");
		return array(SOYShop_Order::ORDER_STATUS_CANCELED => "cancel");
	}
}
SOYShopPlugin::extension("soyshop.order.detail.mail", "common_cancel_mail", "CommonCancelMailOrderDetailMail");
