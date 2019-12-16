<?php

function soyshop_cart_application($html, $htmlObj){
	$cartId = $htmlObj->getCartId();

	ob_start();
	include(SOY2::RootDir() . "cart/${cartId}/page.php");
	$html = ob_get_contents();
	ob_end_clean();

	echo $html;
}
?>