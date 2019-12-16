<?php

class SOYShopAtypeInvoiceFunction extends SOYShopOrderFunction{
	
	/**
	 * title text
	 */
	function getTitle(){
		return "納品書A";
		
	}
	
	/**
	 * @return html
	 */
	function getPage(){
		$html = file_get_contents(dirname(__FILE__) . "/template.html");
		
		SOY2DAOFactory::create("order.SOYShop_ItemModule");
		SOY2DAOFactory::create("config.SOYShop_ShopConfig");
		
		include_once(dirname(__FILE__) . "/class.php");
		
		$page = SOY2HTMLFactory::createInstance("Invoice_IndexPage", array(
			"arguments" => array("main_invoice",$html),
			"orderId" => $this->getOrderId()
		));
		
		$page->setTitle("納品書A");
		$page->build_invoice();
		
		ob_start();
		$page->display();
		$html = ob_get_contents();
		ob_end_clean();
		
		return $html;
		
	}

}

SOYShopPlugin::extension("soyshop.order.function","order_invoice_a","SOYShopAtypeInvoiceFunction");