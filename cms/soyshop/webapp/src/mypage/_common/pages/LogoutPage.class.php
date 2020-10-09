<?php
class LogoutPage extends MainMyPagePageBase{

	function __construct(){
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
