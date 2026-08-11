<?php
class CommonNoticeArrivalOrderDetailMail extends SOYShopOrderDetailMailBase{

	function getMailType(string $mode){
		if($mode == "user"){
			return array(array("id" => "arrival", "title" => "入荷通知メール"));
		}else{
			return array();
		}
	}
}
SOYShopPlugin::extension("soyshop.order.detail.mail", "common_notice_arrival", "CommonNoticeArrivalOrderDetailMail");
