<?php
function soyshop_mypage_application($html, $htmlObj){
	
	$mypageId = $htmlObj->getMyPageId();
	$args = $htmlObj->getPageArgs();
	
	ob_start();
	include(SOY2::RootDir() . "mypage/".$mypageId."/page.php");
	$html = ob_get_contents();
	ob_end_clean();

	echo $html;	
}