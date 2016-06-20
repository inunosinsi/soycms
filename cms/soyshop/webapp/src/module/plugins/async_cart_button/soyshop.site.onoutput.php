<?php

class AsyncCartButtonOnOutput extends SOYShopSiteOnOutputAction{

	/**
	 * @return string
	 */
	function onOutput($html){

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
?>