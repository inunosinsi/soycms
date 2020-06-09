<?php
/*
 * soyshop.site.onoutput.php
 * Created: 2010/03/04
 */

class reCAPTCHAv3OnOutput extends SOYShopSiteOnOutputAction{

	/**
	 * @return string
	 */
	function onOutput($html){
		SOY2::import("module.plugins.reCAPTCHAv3.util.reCAPTCHAUtil");
		$config = reCAPTCHAUtil::getConfig();
		if(!isset($config["page_id"]) || !is_numeric($config["page_id"]) || (int)$config["page_id"] != (int)SOYSHOP_PAGE_ID) return $html;

		if(!isset($config["site_key"]) || !isset($config["secret_key"])) return $html;
		if(!strlen($config["site_key"]) || !strlen($config["secret_key"])) return $html;


		//URLの末尾が.xmlだった時は以下の処理を行わない
		if(strpos($_SERVER["REQUEST_URI"], ".xml") !== false) return $html;

		//カレンダープラグインから出力されるCSSページの場合は以下の処理を行わない
		if(strpos($_SERVER["REQUEST_URI"], "?output_css_mode") !== false) return $html;

		$jsPath = dirname(SOYSHOP_ROOT) . "/common/site_include/plugin/reCAPTCHAv3/js/script.js";
		if(!file_exists($jsPath)) return $html;	// SOYCMSのバージョンが古い時は使えない

		$js = array();
		$js[] = "<script src=\"https://www.google.com/recaptcha/api.js?render=" . $config["site_key"] . "\"></script>";

		//1行ずつ読み込んで処理の節約
		$isConfirmForm = false;
		$lines = explode("\n", $html);
		foreach($lines as $line){
			if($isConfirmForm) break;
			$line = trim($line);
			if(stripos($line, "<script") === 0) continue;
			if(stripos($line, "</") === 0) continue;
			if(stripos($line, "</body") !== false) break;
			if(stripos($line, "<form") !== false){
				if(strpos($line, "soy_inquiry_form")) $isConfirmForm = true;
			}
		}

		//お問い合わせフォームを設置したページのみ
		if($isConfirmForm){
			$js[] = "<script>";
			//SOY CMS側のjsファイルを持ってくる
			$code = file_get_contents($jsPath);
			$js[] = str_replace("##SITE_KEY##", $config["site_key"], $code);
			$js[] = "</script>";
		}
		$script = implode("\n", $js);

		if(stripos($html,'</body>') !== false){
			return str_ireplace('</body>',$script."\n".'</body>',$html);
		}else if(stripos($html,'</html>') !== false){
			return str_ireplace('</html>',$script."\n".'</html>',$html);
		}else{
			return $html.$script;
		}
	}
}

SOYShopPlugin::extension("soyshop.site.onoutput", "reCAPTCHAv3", "reCAPTCHAv3OnOutput");
