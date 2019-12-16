<?php 
/**
 * @class ErrorPage
 * @date 2009-07-16T16:47:00+09:00
 * @author SOY2HTMLFactory
 */ 
class ErrorPage extends MobileCartPageBase{
	
	function doPost(){
		$param = null;
		if(SOYSHOP_MOBILE_CARRIER == "DoCoMo"){
			$param = session_name() . "=" . session_id();
		}

		soyshop_redirect_cart($param);
	}
	
	function ErrorPage(){
		parent::__construct();

	}
}


?>