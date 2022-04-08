<?php
class OrderInvoiceAddReceiptDownload extends SOYShopDownload{

	function execute(){
		if(
			!isset($_GET["order_id"]) || 
			!is_numeric($_GET["order_id"]) || 
			!SOYShopPluginUtil::checkIsActive("order_invoice_add_receipt_button")
		){
			self::_error();
		}

		$mypage = MyPageLogic::getMyPage();
		if(!$mypage->getIsLoggedin()) self::_error();

		//任意の注文と注文者が同じであるか？
		if((int)soyshop_get_order_object($_GET["order_id"])->getUserId() !== (int)$mypage->getUserId()) self::_error();

		SOYShopPlugin::load("soyshop.order.function", soyshop_get_plugin_object("order_invoice_add_receipt_button"));
		$html = SOYShopPlugin::display("soyshop.order.function", array(
			"orderId" => (int)$_GET["order_id"],
			"mode" => "select"
		));

		// @ToDo PDFに変換したい

		echo $html;
		exit;
	}

	private function _error(){
		echo "failed";
		exit;
	}
}
SOYShopPlugin::extension("soyshop.download", "order_invoice_add_receipt_button", "OrderInvoiceAddReceiptDownload");
