<?php
class CommonMembershipCart extends SOYShopCartBase{

	function doOperation(){
		
		$mypage = MyPageLogic::getMyPage();
						
		//ログインチェック
		if(!$mypage->getIsLoggedin()){
			header("Location:" . $_SERVER["HTTP_REFERER"]);
			exit;
		}
	}
}
SOYShopPlugin::extension("soyshop.cart","common_membership_cart","CommonMembershipCart");
?>