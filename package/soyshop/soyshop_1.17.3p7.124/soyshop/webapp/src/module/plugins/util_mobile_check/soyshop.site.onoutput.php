<?php
/*
 * soyshop.site.onoutput.php
 * Created: 2010/03/04
 */

class UtilMobileCheckOnOutput extends SOYShopSiteOnOutputAction{

	/**
	 * @return string
	 */
	function onOutput($html){
	
		//この処理をprepareに移動
//		if(isset($_GET[session_name()])){
//			output_add_rewrite_var(session_name(), session_id());
//			return $html;
			
//			ob_list_handlers();
//			exit;
//		}    
		return $html;
	}
}

SOYShopPlugin::extension("soyshop.site.onoutput", "util_mobile_check", "UtilMobileCheckOnOutput");