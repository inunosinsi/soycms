<?php

class PrintShippingLabelAddHatsubaraiFunction extends SOYShopOrderFunction{
	
	/**
	 * title text
	 */
	function getTitle(){
		return "配送伝票生成(発払い)";
		
	}
	
	private $csvLogic;
	
	/**
	 * @return html
	 */
	function getPage(){
		
		SOY2::import("module.plugins.print_shipping_label.util.ShippingLabelUtil");
		
		$tmp = ShippingLabelUtil::COMPANY_KURONEKO;
		$html = file_get_contents(dirname(dirname(__FILE__)) . "/print_shipping_label/template/" . $tmp . ".html");
		
		//何の伝票を印刷するか？
		define("SHIPPING_LABEL_COMPANY", $tmp);
		
		//強制的に発払いにする
		define("USE_LABEL_TYPE", ShippingLabelUtil::TYPE_HATSUBARAI);
		
		SOY2::import("module.plugins.print_shipping_label.page.PrintPage");
		$page = SOY2HTMLFactory::createInstance("PrintPage", array(
			"arguments" => array("main_print", $html),
			"orderId" => $this->getOrderId()
		));		
		
		$page->setTitle("配送伝票");
		$page->build_print();
		
		ob_start();
		$page->display();
		$html = ob_get_contents();
		ob_end_clean();
		
		echo  $html;
		
	}
}

SOYShopPlugin::extension("soyshop.order.function","print_shipping_label_add_hatsubarai","PrintShippingLabelAddHatsubaraiFunction");