<?php

class CalendarExpandSmartOnOutput extends SOYShopSiteOnOutputAction{

	/**
	 * @return string
	 */
	function onOutput($html){

		//プラグイン側からCSSを出力
		if(isset($_GET["output_css_mode"]) && $_GET["output_css_mode"] == "calendar"){
			header("Content-Type: text/css; charset: UTF-8");
			return file_get_contents(dirname(__FILE__) . "/css/calendar.css");
		}

		//カレンダーを設置しているページであれば、JSとCSSを読み込む
		if(strpos($html, "<!-- output calendar plugin -->")){
			//jQueryは事前に読み込んでおいてもらうことにする

			$js = "<script>\n" . file_get_contents(dirname(__FILE__) . "/js/smart.js") . "</script>";
			$css = "<link rel=\"stylesheet\" title=\"output calendar plugin\" type=\"text/css\" href=\"?output_css_mode=calendar\">";
			if(stripos($html, "</html>")){
				$html = str_ireplace("</html>", $js . "\n" . $css . "\n</html>", $html);
			}else{
				$html .= $js . "\n" . $css;
			}
		}

		return $html;
	}
}

SOYShopPlugin::extension("soyshop.site.onoutput", "calendar_expand_smart", "CalendarExpandSmartOnOutput");
