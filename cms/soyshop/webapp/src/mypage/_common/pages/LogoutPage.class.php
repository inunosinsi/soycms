<?php 
class LogoutPage extends MainMyPagePageBase{
	
	function LogoutPage(){
		WebPage::WebPage();
		$mypage = MyPageLogic::getMyPage();
		$mypage->logout();
		
		if(isset($_GET["r"])){
			$param = soyshop_remove_get_value($_GET["r"]);
			soyshop_redirect_designated_page($param);
			exit;
		}
			
		header("Location:" . soyshop_get_site_url(true));
		exit;
	}
}
?>