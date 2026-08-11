<?php
SOY2::import("module.plugins.order_invoice_add_receipt_button.util.ReceiptUtil");
class OrderInvoiceAddReceiptMypageOrder extends SOYShopMypageOrderBase{

	/**
	 * @return string
	 * タイトル横に表示されるリンクのURL
	 */
	function getLink(){
		if(!ReceiptUtil::isMyPageSetting()) return "";

		$uri = $_SERVER["REQUEST_URI"];
		$uri = substr($uri, strpos($uri, "/order/detail/") + 14);
		preg_match('/^(\d*)/', $uri, $tmp);
		$orderId = (isset($tmp[1]) && is_numeric($tmp[1])) ? (int)$tmp[1] : 0;

		$order = soyshop_get_order_object($orderId);
		if((int)$order->getId() < 1) return "";

		return soyshop_get_mypage_url() . "?soyshop_action=order_invoice_add_receipt_button&order_id=" . $orderId;
	}

	/**
	 * @return string
	 * タイトル横に表示されるリンクURLのテキスト部分
	 */
	function getLinkTitle(){
		return (ReceiptUtil::isMyPageSetting()) ? "領収書の発行" : "";
	}

	/**
	 * @return boolean
	 * タイトル横に表示されるリンクURLを別タブで開くか？
	 */
	function getTargetBlank(){
		return (ReceiptUtil::isMyPageSetting());
	}
}
SOYShopPlugin::extension("soyshop.mypage.order", "order_invoice_add_receipt_button", "OrderInvoiceAddReceiptMypageOrder");
