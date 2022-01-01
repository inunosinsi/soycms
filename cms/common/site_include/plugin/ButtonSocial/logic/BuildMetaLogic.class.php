<?php

class BuildMetaLogic extends SOY2LogicBase {

	function __construct(){
		SOY2::import("site_include.plugin.ButtonSocial.util.ButtonSocialUtil");
	}

	function buildOgMeta($obj, string $description="", string $image="", int $entryId=0){

		if($entryId > 0){
			$attrImagePath = self::_getImagePathByEntryId($entryId);
			if(strlen($attrImagePath)) $image = $attrImagePath;
		}

		$html = array();

		/** @ToDo いずれ多言語対応 **/
		$html[] = "<meta property=\"og:locale\" content=\"ja_JP\">";
		$html[] = "<meta property=\"og:title\" content=\"".htmlspecialchars(ButtonSocialUtil::getTitle($obj),ENT_QUOTES,"UTF-8")."\">";
		$html[] = "<meta property=\"og:site_name\" content=\"".htmlspecialchars($obj->siteConfig->getName(),ENT_QUOTES,"UTF-8")."\">";
		$html[] = "<meta property=\"og:url\" content=\"".htmlspecialchars(ButtonSocialUtil::getPageUrl(),ENT_QUOTES,"UTF-8")."\">";
		$html[] = "<meta property=\"og:type\" content=\"".htmlspecialchars(self::_getOgType($obj),ENT_QUOTES,"UTF-8")."\">";
		if(strlen($image)) $html[] = "<meta property=\"og:image\" content=\"".htmlspecialchars($image,ENT_QUOTES,"UTF-8")."\">";
		if(strlen($description)) $html[] = "<meta property=\"og:description\" content=\"".htmlspecialchars($description,ENT_QUOTES,"UTF-8")."\">";

		return implode("\n",$html);
	}

	function buildTwitterCardMeta($obj, string $card="", string $twId="", string $description="", string $image="", int $entryId=0){
		if($entryId > 0){
			$attrImagePath = self::_getImagePathByEntryId($entryId);
			if(strlen($attrImagePath)) $image = $attrImagePath;
		}

		$html = array();
		$html[] = "<meta name=\"twitter:card\" content=\"" . $card . "\">";
		if(strlen($twId)) $html[] = "<meta name=\"twitter:site\" content=\"@" . $twId . "\">";
		$html[] = "<meta name=\"twitter:title\" content=\"" . htmlspecialchars(ButtonSocialUtil::getTitle($obj),ENT_QUOTES,"UTF-8") . "\">";
		if(strlen($description)) $html[] = "<meta name=\"twitter:description\" content=\"" . $description . "\">";
		if(isset($image) && strlen($image)) $html[] = "<meta name=\"twitter:image\" content=\"" . $image . "\">";
		return implode("\n", $html);
	}

	function buildFbMeta(string $appId="", string $admins=""){
		$html = array();

		$html[] = (strlen($appId))  ? "<meta property=\"fb:app_id\" content=\"".htmlspecialchars($appId, ENT_QUOTES,"UTF-8")."\">" : "";
		$html[] = (strlen($admins)) ? "<meta property=\"fb:admins\" content=\"".htmlspecialchars($admins,ENT_QUOTES,"UTF-8")."\">" : "";

		return implode("\n",$html);
	}

	function buildFbRoot(string $appId="", string $version=""){
		if(!strlen($appId)) return "";
		
		if(!strlen($version)) $version = "v2.10";

		$html = array();
		$html[] = "<div id=\"fb-root\"></div>";
		$html[] = "<script>(function(d, s, id) {";
		$html[] = "	var js, fjs = d.getElementsByTagName(s)[0];";
		$html[] = "	if (d.getElementById(id)) return;";
		$html[] = "		js = d.createElement(s); js.id = id;";
		$html[] = "		js.src = \"//connect.facebook.net/ja_JP/sdk.js#xfbml=1&version={$version}&appId={$appId}\";";
		$html[] = "		fjs.parentNode.insertBefore(js, fjs);";
		$html[] = "	}(document, 'script', 'facebook-jssdk'));";
		$html[] = "</script>";
		return implode("\n", $html);
	}

	private function _getOgType($obj){
		SOY2::import('site_include.CMSBlogPage');
		switch(get_class($obj)){
			case "CMSBlogPage":
				switch($obj->mode){
					case CMSBlogPage::MODE_ENTRY:
						return "article";
					default:
						return "blog";
				}
			default:
				$uri = $obj->page->getUri();
				if(!strlen($uri) || strpos($uri, "index") === 0){
					return "website";
				}else{
					return "article";
				}
		}
	}

	public static function _getImagePathByEntryId(int $entryId=0){
		if($entryId === 0) return "";
		$attr = ButtonSocialUtil::getAttr($entryId);
		if(strlen($attr->getValue())){
			$http = (isset($_SERVER["HTTPS"]) && $_SERVER["HTTPS"] == "on") ? "https" : "http";
			return $http . "://" . str_replace("//", "/", $_SERVER["HTTP_HOST"]. "/" . $attr->getValue());
		}
		return "";
	}
}
