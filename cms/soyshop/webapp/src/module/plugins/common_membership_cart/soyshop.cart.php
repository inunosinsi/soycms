<?php
class CommonMembershipCart extends SOYShopCartBase{

	function displayPage03(CartLogic $cart){}
}
SOYShopPlugin::extension("soyshop.cart","common_membership_cart","CommonMembershipCart");
