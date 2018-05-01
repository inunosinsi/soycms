<?php

class AsyncCartButtonOnOutput extends SOYShopSiteOnOutputAction{

	/**
	 * @return string
	 */
	function onOutput($html){

		SOY2::import("module.plugins.async_cart_button.util.AsyncCartButtonUtil");

		//通常ページ
		$displayConfig = AsyncCartButtonUtil::getPageDisplayConfig();
		if(defined("SOYSHOP_PAGE_ID")){
			if(isset($displayConfig[SOYSHOP_PAGE_ID]) && $displayConfig[SOYSHOP_PAGE_ID] == AsyncCartButtonUtil::INSERT_TAG_NOT_DISPLAY){
				return $html;
			}
		//カートとマイページ
		}else{
			if(SOYSHOP_CART_MODE){
				if(isset($displayConfig[AsyncCartButtonUtil::PAGE_TYPE_CART]) && $displayConfig[AsyncCartButtonUtil::PAGE_TYPE_CART] == AsyncCartButtonUtil::INSERT_TAG_NOT_DISPLAY){
					return $html;
				}
			}elseif(SOYSHOP_MYPAGE_MODE){
				if(isset($displayConfig[AsyncCartButtonUtil::PAGE_TYPE_MYPAGE]) && $displayConfig[AsyncCartButtonUtil::PAGE_TYPE_MYPAGE] == AsyncCartButtonUtil::INSERT_TAG_NOT_DISPLAY){
					return $html;
				}
			}
		}

		$script = file_get_contents(dirname(__FILE__) . "/js/obj.js");

		/**
		 * @ToDo 全文検索は遅くなるので、どうにかしたい
		 */
		$script = "<script>\n" . str_replace("operationUrl : \"\"", "operationUrl : \"" . soyshop_get_cart_url(true) . "\"", $script) . "\n</script>\n";
		if(strpos($html, "</body>")){
			$html = str_replace("</body>", $script . "</body>", $html);
		}elseif(strpos($html, "</html>")){
			$html = str_replace("</html>", $script . "</html>", $html);
		}else{
			$html = str_replace("</head>", $script . "</head>", $html);
		}


		return $html;
	}
}

SOYShopPlugin::extension("soyshop.site.onoutput", "async_cart_button", "AsyncCartButtonOnOutput");
