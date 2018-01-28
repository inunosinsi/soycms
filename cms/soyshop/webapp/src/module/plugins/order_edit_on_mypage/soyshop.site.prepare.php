<?php

class OrderEditOnMypagePrepareAction extends SOYShopSitePrepareAction{

	function prepare(){
		if(isset($_GET["func"]) && $_GET["func"] == "order_edit_on_mypage"){
			//念のためにログインしているか？確認する
			$mypage = MyPageLogic::getMyPage(soyshop_get_cart_id());
			if($mypage->getIsLoggedin()){
				//注文編集モードをアクティブにする。 /** @ToDo 編集モードを解除する方法 **/
				$mypage->setAttribute("order_edit_on_mypage", true);
				$mypage->save();
			}
		}
	}
}
SOYShopPlugin::extension("soyshop.site.prepare", "order_edit_on_mypage", "OrderEditOnMypagePrepareAction");
