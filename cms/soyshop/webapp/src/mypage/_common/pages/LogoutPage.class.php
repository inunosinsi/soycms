<?php
class LogoutPage extends MainMyPagePageBase{

	function __construct(){
		//公開画面から直接ログアウトボタンを押さない限りログアウトさせない
		if(!soy2_check_token()) $this->jumpToTop();

		parent::__construct();
		$this->getMyPage()->logout();

		if(isset($_GET["r"])){
			$param = soyshop_remove_get_value($_GET["r"]);
			soyshop_redirect_designated_page($param);
			exit;
		}

		header("Location:" . soyshop_get_site_url(true));
		exit;
	}
}
