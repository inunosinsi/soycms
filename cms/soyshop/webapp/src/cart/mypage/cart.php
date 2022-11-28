<?php
/*
 * Created on 2009/07/07
 *
 * To change the template for this generated file go to
 * Window - Preferences - PHPeclipse - PHP - Code Templates
 */
$cart = CartLogic::getCart();
$cart->setAttribute("page", null);
$cart->save();
?>
