<?php
/*
 * Created on 2011/08/08
 *
 * To change the template for this generated file go to
 * Window - Preferences - PHPeclipse - PHP - Code Templates
 */
 SOY2::import("util.UserInfoUtil");
class ButtonSocialCommon{
	
	private $pluginObj;
	private $fieldObj;
	
	function getOgMeta($obj, $description = null, $image = null, $entryId = null){
		$siteConfig = $obj->siteConfig;

		if(!$this->fieldObj){
			$this->fieldObj = (isset($entryId)) ? $this->pluginObj->getOgImageObject($entryId) : new EntryAttribute();
		}
		
		if(isset($this->fieldObj) && strlen($this->fieldObj->getValue()) > 0){
			$http = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == "on") ? "https" : "http";
			$image = $http . "://" . $_SERVER ["HTTP_HOST"]. "/" . $this->fieldObj->getValue();
		}
		
		$html = array();

		$html[] = "<meta property=\"og:title\" content=\"".$this->getTitle($obj)."\" />";
		$html[] = "<meta property=\"og:site_name\" content=\"".$siteConfig->getName()."\" />";
		$html[] = "<meta property=\"og:url\" content=\"".$this->getPageUrl()."\" />";
		$html[] = "<meta property=\"og:type\" content=\"".$this->getOgType($obj)."\" />";
		if(isset($image) && strlen($image) > 0){
			$html[] = "<meta property=\"og:image\" content=\"".$image."\" />";
		}
		$html[] = "<meta property=\"og:description\" content=\"".$description."\" />";

		return implode("\n",$html);
	}

	function getFbMeta($appId,$admins){
		$html = array();

		if(strlen($appId)) $html[] = "<meta property=\"fb:app_id\" content=\"".$appId."\" />";
		if(strlen($admins)) $html[] = "<meta property=\"fb:admins\" content=\"".$admins."\" />";

		return (count($html)) ? implode("\n",$html) : "";
	}
	
	function getFbRoot($appId){
		$html = array();
		
		if(strlen($appId) > 0){
			$html[] = "<div id=\"fb-root\"></div>";
			$html[] = "<script>(function(d, s, id) {";
			$html[] = "var js, fjs = d.getElementsByTagName(s)[0];";
			$html[] = "if (d.getElementById(id)) return;";
			$html[]	= "js = d.createElement(s); js.id = id;";
			$html[] = "js.src = \"//connect.facebook.net/ja_JP/sdk.js#xfbml=1&version=v2.8&appId=" . $appId . "\";";
			$html[] = "fjs.parentNode.insertBefore(js, fjs);";
			$html[] = "}(document, 'script', 'facebook-jssdk'));</script>";
		}
		
		return implode("\n", $html);
	}

	function getFbButton($appId,$entryLink=null){
		static $b;
		if(is_null($b)){
			if(isset($entryLink)){
				$url = $entryLink;
			}else{
				$url = $this->getPageUrl();
			}
			
			$b = "<div class=\"fb-like fb-like-comment\" data-href=\"" . $url . "\" data-send=\"false\" data-layout=\"button_count\" data-width=\"450\" data-show-faces=\"false\"></div>";
		}	
		
		return $b;
	}

	function getTwitterButton($entryLink=null){
		static $b;
		if(is_null($b)){
			if(isset($entryLink)){
				$url = $entryLink;
			}else{
				$url = $this->getPageUrl();
			}
			
			$b = "<a href=\"https://twitter.com/share\" " .
				"class=\"twitter-share-button\" " .
				"data-url=\"".$url."\" " .
				"data-count=\"horizontal\">Tweet</a>" .
				"<script type=\"text/javascript\" " .
				"src=\"https://platform.twitter.com/widgets.js\"></script>";	
		}

		return $b;
	}

	function getTwitterButtonMobile($entryLink=null,$title="記事タイトル"){
		static $b;
		if(is_null($b)){
			if(isset($entryLink)){
				$url = $entryLink;
			}else{
				$url = $this->getPageUrl();
			}
			$url = rawurlencode($url);
	
			$title = rawurlencode(mb_convert_encoding($title,"SJIS-win","UTF-8"));
			
			$b = "http://twtr.jp/share?url=".$url."&text=".$title;
		}
		
		return $b;
	}

	function getHatenaButton($entryLink=null){
		static $b;
		if(is_null($b)){
			if(isset($entryLink)){
				$url = $entryLink;
			}else{
				$url = $this->getPageUrl();
			}
	
			$b = "<a href=\"https://b.hatena.ne.jp/entry/" . $url . "\" " .
					"class=\"hatena-bookmark-button\" " .
					"data-hatena-bookmark-layout=\"standard\" " .
					"title=\"このエントリーをはてなブックマークに追加\">" .
					"<img src=\"//b.st-hatena.com/images/entry-button/button-only.gif\" " .
					"alt=\"このエントリーをはてなブックマークに追加\" " .
					"width=\"20\" height=\"20\" style=\"border: none;\" /></a>" .
					"<script type=\"text/javascript\" " .
					"src=\"//b.st-hatena.com/js/bookmark_button.js\" charset=\"utf-8\" async=\"async\"></script>";
		}

		return $b;
	}
	
	function getPocketButton(){
		return "<a data-pocket-label=\"pocket\" data-pocket-count=\"horizontal\" class=\"pocket-btn\" data-lang=\"en\"></a>".
				"<script type=\"text/javascript\">" .
				"!function(d,i){if(!d.getElementById(i)){var j=d.createElement(\"script\");j.id=i;j.src=\"https://widgets.getpocket.com/v1/j/btn.js?v=1\";var w=d.getElementById(i);d.body.appendChild(j);}}(document,\"pocket-btn-js\");" .
				"</script>";
	}

	function getMixiCheckScript(){
		return "<script type=\"text/javascript\" src=\"https://static.mixi.jp/js/share.js\"></script>";
	}

	function getMixiCheckButtonMobile($url, $key,$title){
		return "<form action=\"http://m.mixi.jp/share.pl?guid=ON\" method=\"POST\" >".
        		"<input type=\"hidden\" name=\"check_key\" value=\"".$key."\" />".
        		"<input type=\"hidden\" name=\"title\" value=\"".$title."\" />".
        		"<input type=\"hidden\" name=\"primary_url\" value=\"".$url."\" />".
        		"<input type=\"submit\" value=\"mixiチェック\" />".
    			"</form>";
	}

	function getMixiLikeButton($key){
		return "<div data-plugins-type=\"mixi-favorite\" data-service-key=\"".$key."\" data-size=\"medium\" data-href=\"\" data-show-faces=\"true\" data-show-count=\"true\" data-show-comment=\"true\" data-width=\"450\"></div>".
				"<script type=\"text/javascript\">(function(d) {var s = d.createElement('script'); s.type = 'text/javascript'; s.async = true;s.src = '//static.mixi.jp/js/plugins.js#lang=ja';d.getElementsByTagName('head')[0].appendChild(s);})(document);</script>";
	}

	function getMixiLikeButtonMobile($url,$title,$key){
		return "<form action=\"http://m.mixi.jp/create_favorite.pl?guid=ON\" method=\"POST\" >".
		        "<input type=\"hidden\" name=\"service_key\" value=\"".$key."\" />".
        		"<input type=\"hidden\" name=\"title\" value=\"".$title."\" />".
				"<input type=\"hidden\" name=\"primary_url\" value=\"".$url."\" />".
				"<input type=\"hidden\" name=\"mobile_url\" value=\"".$url."\" />".
				"<input type=\"submit\" value=\"ｲｲﾈ!\" />".
				"</form>";
	}
	
	function getGooglePlusButton(){
		return "<div class=\"g-plusone\"></div>\n".
				"<script type=\"text/javascript\">\n".
				"  window.___gcfg = {lang: 'ja'};\n".
				"\n".
				"  (function() {\n".
				"    var po = document.createElement('script'); po.type = 'text/javascript'; po.async = true;\n".
				"    po.src = 'https://apis.google.com/js/plusone.js';\n".
				"    var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(po, s);\n".
				"  })();\n".
				"</script>";
	}

	function getTitle($obj){

		//タイトルフォーマットを取得
		$titleFormat = $this->getTitleFormat($obj);

		//置換文字列を変換して返す
		return $this->convertTitle($obj,$titleFormat);

	}

	//og:typeを取得
	function getOgType($obj){

		$pageType = get_class($obj);

		$ogType = "";
		switch($pageType){
			case "CMSBlogPage":
				$mode = $obj->mode;
				switch($mode){
					case CMSBlogPage::MODE_ENTRY:
						$ogType = "article";
						break;
					default:
						$ogType = "blog";
						break;
				}
				break;
			default:
				$ogType = "website";
				break;
		}

		return $ogType;
	}

	//タイトルフォーマットを取得
	function getTitleFormat($obj){

		$page = $obj->page;

		$pageType = get_class($obj);
		switch($pageType){
			case "CMSPage":
			case "CMSApplicationPage":
				$titleFormat = $page->getPageTitleFormat();
				break;
			case "CMSBlogPage":
				$mode = $obj->mode;
				switch($mode){
					case CMSBlogPage::MODE_TOP:
						$titleFormat = $page->getTopTitleFormat();
						break;
					case CMSBlogPage::MODE_ENTRY:
						$titleFormat = $page->getEntryTitleFormat();
						break;
					case CMSBlogPage::MODE_MONTH_ARCHIVE:
						$titleFormat = $page->getMonthTitleFormat();
						break;
					case CMSBlogPage::MODE_CATEGORY_ARCHIVE:
						$titleFormat = $page->getCategoryTitleFormat();
						break;
					default:
						$titleFormat = "";
						break;
				}
				break;
			default:
				$titleFormat = "";
				break;
		}

		return $titleFormat;
	}

	function convertTitle($obj,$format){
		//ページのタイトルを取得
		$page = $obj->page;
		$title = $page->getTitle();

		//サイト名を取得する
		$siteConfig = $obj->siteConfig;
		$siteName = $siteConfig->getName();

		$format = str_replace("%PAGE%",$title,$format);
		$format = str_replace("%BLOG%",$title,$format);
		$format = str_replace("%SITE%",$siteName,$format);

		//ブログページのタイトルフォーマットの置換処理
		$pageType = get_class($obj);
		if($pageType=="CMSBlogPage"){
			$mode = $obj->mode;

			//エントリ名を取得
			if($mode==CMSBlogPage::MODE_ENTRY){
				$entry = $obj->entry;
				$format = str_replace("%ENTRY%",$entry->getTitle(),$format);
			}

			//アーカイブを取得
			if($mode==CMSBlogPage::MODE_MONTH_ARCHIVE){
				$year = $obj->year;
				$month = $obj->month;
				$day = $obj->day;

				$format = str_replace("%YEAR%",$year,$format);
				$format = str_replace("%MONTH%",$month,$format);
				$format = str_replace("%DAY%",$day,$format);
			}

			//カテゴリ名を取得
			if($mode==CMSBlogPage::MODE_CATEGORY_ARCHIVE){
				$label = $obj->label;
				$format = str_replace("%CATEGORY%",$label->getCaption(),$format);
			}
		}

		return $format;
	}

	function getDetailUrl($obj,$entryId){

		$uri = "";

		//ブログページだった場合
		//$objはBlogPage_EntryListなど
		if(property_exists($obj, "entryPageUri")){
			$uri = ltrim($obj->entryPageUri,"/");
		}elseif(property_exists($obj, "entryPageUrl")){
			$uri = ltrim($obj->entryPageUrl,"/");
		}
		
		//uriにドメインが混じっている場合はドメインまでを削除
		if(strpos($uri, $_SERVER["HTTP_HOST"])){
			$pos = strpos($uri, $_SERVER["HTTP_HOST"]);
			$len = strlen($_SERVER["HTTP_HOST"]);
			$uri = substr($uri, $pos + $len);
			//念の為、最初のスラッシュの後から取得
			$uri = substr($uri, strpos($uri, "/") + 1);
		}

		$rootLink = UserInfoUtil::getSiteURLBySiteId("");
		$url = $rootLink.$uri;
		
		$entry = $this->getEntry($entryId);
		$url .= rawurlencode($entry->getAlias());

		return array($url,$entry->getTitle());
	}

	function getPageUrl(){
		if(isset($_SERVER['HTTPS'])){
			return "https://".$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
		}else{
			return "http://".$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
		}
	}

	private $entryDao;

	function getEntry($entryId){
		if(!$this->entryDao){
			$this->entryDao = SOY2DAOFactory::create("cms.EntryDAO");
		}
		$dao = $this->entryDao;

		try{
			$entry = $dao->getById($entryId);
		}catch(Exception $e){
			$entry = new Entry();
		}

		return $entry;

	}
	
	function setPluginObj($pluginObj){
		$this->pluginObj = $pluginObj;
	}
}
?>
