<?php
class CommonAddMailOrderDetailMail extends SOYShopOrderDetailMailBase{

	function getMailType($mode){
		SOY2::import("module.plugins.common_add_mail_type.util.AddMailTypeUtil");
		return AddMailTypeUtil::getConfig($mode);
	}
}
SOYShopPlugin::extension("soyshop.order.detail.mail", "common_add_mail_type", "CommonAddMailOrderDetailMail");
