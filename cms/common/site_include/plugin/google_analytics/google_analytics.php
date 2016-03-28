<?php
/**
 * Google Analytics導入プラグイン
 */

GoogleAnalytics::register();

class GoogleAnalytics{

	const PLUGIN_ID = "google_analytics";

	//挿入箇所
	const INSERT_INTO_THE_END_OF_HEAD = 2;
	const INSERT_INTO_THE_BEGINNING_OF_BODY = 1;
	const INSERT_INTO_THE_END_OF_BODY = 0;

	//コード
	var $google_analytics_track_code;
	var $google_analytics_track_code_mobile;
	var $google_analytics_track_code_smartphone;

	//挿入箇所
	var $position = self::INSERT_INTO_THE_END_OF_HEAD;

	//挿入しないページ
	//Array<ページID => 0 | 1> 挿入しないページが1
	var $config_per_page = array();
	//Array<ページID => Array<ページタイプ => 0 | 1>> 挿入しないページが1
	var $config_per_blog = array();

	public static function register(){
		$obj = CMSPlugin::loadPluginConfig(self::PLUGIN_ID);
		if(is_null($obj)){
			$obj = new GoogleAnalytics();
		}
		CMSPlugin::addPlugin(self::PLUGIN_ID,array($obj,"init"));
	}

	function init(){

		CMSPlugin::addPluginMenu(self::PLUGIN_ID,array(
			"name"=>"Google Analytics導入プラグイン",
			"description"=>"Google Analyticsを簡単に導入できます。<br>このプラグインを有効にすると全ページもしくは指定したページの指定した箇所にGoogle Analyticsトラックコードを埋め込むことができます。",
			"author"=>"日本情報化研究所",
			"modifier"=>"Jun Okada",
			"url"=>"http://www.n-i-agroinformatics.com/",
			"mail"=>"soycms@soycms.net",
			"version"=>"1.9"
		));

		if(CMSPlugin::activeCheck(self::PLUGIN_ID)){
			
			CMSPlugin::addPluginConfigPage(self::PLUGIN_ID,array(
				$this,"config"));
	
			CMSPlugin::setEvent('onOutput',self::PLUGIN_ID,array($this,"onOutput"),array("filter"=>"all"));
		}
	}

	/**
	 * 設定画面表示
	 * @return HTML
	 */
	function config(){
		include(dirname(__FILE__)."/config.php");
		$form = SOY2HTMLFactory::createInstance("GoogleAnalyticsPluginConfigPage");
		$form->setPluginObj($this);
		$form->execute();
		return $form->getObject();
	}

	/**
	 * ページ毎の設定が可能かどうか 1.2.7以上
	 * （onOutputでpageとwebPageが取れるかどうか）
	 */
	function isConfigPerPageEnabled(){
		//version_compare("1.2.6x","1.2.6",">")はfalse
		return ( SOYCMS_VERSION == "developing" OR version_compare(SOYCMS_VERSION, "1.2.6", ">"));
	}

	function onOutput($arg){
		$html = &$arg["html"];

		//ダイナミック編集では挿入しない
		if(defined("CMS_PREVIEW_MODE") && CMS_PREVIEW_MODE){
			return null;
		}

		//トラックコードが空なら挿入しない
		if(!strlen(trim($this->google_analytics_track_code.$this->google_analytics_track_code_smartphone.$this->google_analytics_track_code_mobile))){
			return null;
		}

		if($this->isConfigPerPageEnabled()){
			//1.2.7以上

			$page = $arg["page"];
			$webPage = $arg["webPage"];

			//表示しない設定なら挿入しない
			if(@$this->config_per_page[$page->getId()]){
				return null;
			}elseif($page->getPageType() == Page::PAGE_TYPE_BLOG && @$this->config_per_blog[$page->getId()][$webPage->mode]){
				return null;
			}

			//RSSでは挿入しない
			if($page->getPageType() == Page::PAGE_TYPE_BLOG && $webPage->mode == CMSBlogPage::MODE_RSS){
				return null;
			}
		}else{
			//RSSでは挿入しない
			if(
				strpos($html, '<rss version="2.0">') !== false
				||
				strpos($html, '<feed xml:lang="ja" xmlns="http://www.w3.org/2005/Atom">') !== false
			){
				return null;
			}
		}

		/* コードを挿入 */

		//モバイルで見てる時
		if(defined("SOYCMS_IS_MOBILE") && SOYCMS_IS_MOBILE == true){
			$html = $this->insertCodeMobile($html);

		//スマホで見てる時
		}elseif(defined("SOYCMS_IS_SMARTPHONE") && SOYCMS_IS_SMARTPHONE == true){
			$html = $this->insertCode($html,"smartphone");

		//どちらでもない場合はPC
		}else{
			$html = $this->insertCode($html);
		}

		return $html;
	}

	function insertCode($html,$carrier="pc"){

		switch($carrier){
			case "smartphone":
				$code = $this->google_analytics_track_code_smartphone;
				break;
			case "pc":
			default:
				$code = $this->google_analytics_track_code;
				break;
		}

		//</head>の直前
		if($this->position == self::INSERT_INTO_THE_END_OF_HEAD){
			if(stripos($html,'</head>') !== false){
				$html = str_ireplace('</head>',$code."\n".'</head>',$html);
			}elseif(stripos($html,'<body>') !== false){
				$html = str_ireplace('<body>','<body>'."\n".$code,$html);
			}elseif(preg_match('/<body\\s[^>]+>/',$html)){
				$html = preg_replace('/(<body\\s[^>]+>)/',"\$0\n".$code,$html);
			}elseif(stripos($html,'<head>') !== false){
				$html = str_ireplace('<head>','<head>'."\n".$code,$html);
			}elseif(stripos($html,'<html>') !== false){
				$html = str_ireplace('<html>','<html>'."\n".$code,$html);
			}elseif(preg_match('/<html\\s[^>]+>/',$html)){
				$html = preg_replace('/(<html\\s[^>]+>)/',"\$0\n".$code,$html);
			}else{
				$html = $code.$html;
			}

		//<body>の直後
		}elseif($this->position == self::INSERT_INTO_THE_BEGINNING_OF_BODY){
			if(stripos($html,'<body>') !== false){
				$html = str_ireplace('<body>','<body>'."\n".$code,$html);
			}elseif(preg_match('/<body\\s[^>]+>/',$html)){
				$html = preg_replace('/(<body\\s[^>]+>)/',"\$0\n".$code,$html);
			}elseif(stripos($html,'</head>') !== false){
				$html = str_ireplace('</head>',$code."\n".'</head>',$html);
			}elseif(stripos($html,'<head>') !== false){
				$html = str_ireplace('<head>','<head>'."\n".$code,$html);
			}elseif(stripos($html,'<html>') !== false){
				$html = str_ireplace('<html>','<html>'."\n".$code,$html);
			}elseif(preg_match('/<html\\s[^>]+>/',$html)){
				$html = preg_replace('/(<html\\s[^>]+>)/',"\$0\n".$code,$html);
			}else{
				$html = $code.$html;
			}

		//末尾
		//}elseif($this->position == self::INSERT_INTO_THE_END_OF_BODY){
		}else{
			if(stripos($html,'</body>') !== false){
				$html = str_ireplace('</body>',$code."\n".'</body>',$html);
			}else if(stripos($html,'</html>') !== false){
				$html = str_ireplace('</html>',$code."\n".'</html>',$html);
			}else{
				$html = $html.$code;
			}
		}

    	return $html;
	}

	function insertCodeMobile($html){
		$imageTag = $this->googleAnalyticsGetImageUrl();

		if(stripos($html,'</body>') !== false){
			$html = str_ireplace('</body>',$imageTag."\n".'</body>',$html);
		}else if(stripos($html,'</html>') !== false){
			$html = str_ireplace('</html>',$imageTag."\n".'</html>',$html);
		}else{

    	}

		return $html;
	}

	function googleAnalyticsGetImageUrl() {
		$GA_ACCOUNT = $this->google_analytics_track_code_mobile;
		$GA_PIXEL = "/ga.php";
	    $url = "";
	    $url .= $GA_PIXEL . "?";
	    $url .= "utmac=" . $GA_ACCOUNT;
	    $url .= "&utmn=" . rand(0, 0x7fffffff);
	    $referer = (isset($_SERVER["HTTP_REFERER"])) ? $_SERVER["HTTP_REFERER"] : "";
	    $query = (isset($_SERVER["QUERY_STRING"])) ? $_SERVER["QUERY_STRING"] : "";
	    $path = (isset($_SERVER["REQUEST_URI"])) ? $_SERVER["REQUEST_URI"] : "";
	    if (empty($referer)) {
	      $referer = "-";
	    }
	    $url .= "&utmr=" . urlencode($referer);
	    if (!empty($path)) {
	      $url .= "&utmp=" . urlencode($path);
	    }
	    $url .= "&guid=ON";
	    $googleAnalyticsImageUrl = str_replace("&", "&amp;", $url);
	    return "<img src=\"" . $googleAnalyticsImageUrl . "\" />";
	}
}
?>
