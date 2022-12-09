<?php
/*
 * soyshop.order.mailbuilder.php
 * Created: 2010/02/04
 */

class SampleMailBuilder extends SOYShopOrderMailBuilder{

	function buildOrderMailBodyForUser(SOYShop_Order $order, SOYShop_User $user){
		return "購入者向けメール：サンプルです！";
	}
	function buildOrderMailBodyForAdmin(SOYShop_Order $order, SOYShop_User $user){
		return "管理者向けメール：これもサンプルです！";
	}

}

SOYShopPlugin::extension("soyshop.order.mailbuilder",	"mailbuilder_sample1","SampleMailBuilder");