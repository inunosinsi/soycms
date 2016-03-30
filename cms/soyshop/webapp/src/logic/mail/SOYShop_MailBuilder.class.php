<?php

interface SOYShop_MailBuilder{

	//注文者向けメール
	function buildOrderMailBodyForUser(SOYShop_Order $order, SOYShop_User $user);
	
	//管理者向けメール
	function buildOrderMailBodyForAdmin(SOYShop_Order $order, SOYShop_User $user);
}
?>