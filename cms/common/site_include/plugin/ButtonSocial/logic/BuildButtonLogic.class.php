<?php

class BuildButtonLogic extends SOY2LogicBase {

	function __construct(){
		SOY2::import("site_include.plugin.ButtonSocial.util.ButtonSocialUtil");
	}

	function buildFbButton($appId, $entryLink=null){
		if(!isset($entryLink)) $entryLink = ButtonSocialUtil::getPageUrl();
		return "<div class=\"fb-like fb-like-comment\" data-href=\"" . htmlspecialchars($entryLink, ENT_QUOTES,"UTF-8") . "\" data-send=\"false\" data-layout=\"button_count\" data-width=\"450\" data-show-faces=\"false\"></div>";
	}

	function buildHatenaButton($entryLink=null){
		if(!isset($entryLink)) $entryLink = ButtonSocialUtil::getPageUrl();
		return "<a href=\"https://b.hatena.ne.jp/entry/" . htmlspecialchars($entryLink,ENT_QUOTES,"UTF-8"). "\" " .
				"class=\"hatena-bookmark-button\" " .
				"data-hatena-bookmark-layout=\"standard\" " .
				"title=\"このエントリーをはてなブックマークに追加\">" .
				"<img src=\"https://b.st-hatena.com/images/entry-button/button-only.gif\" " .
				"alt=\"このエントリーをはてなブックマークに追加\" " .
				"width=\"20\" height=\"20\" style=\"border: none;\"></a>" .
				"<script type=\"text/javascript\" " .
				"src=\"https://b.st-hatena.com/js/bookmark_button.js\" charset=\"utf-8\" async=\"async\"></script>";
	}

	function buildTwitterButton($entryLink=null){
		if(!isset($entryLink)) $entryLink = ButtonSocialUtil::getPageUrl();
		return "<a href=\"https://twitter.com/share\" " .
				"class=\"twitter-share-button\" " .
				"data-url=\"".htmlspecialchars($entryLink,ENT_QUOTES,"UTF-8")."\" " .
				"data-count=\"horizontal\">Tweet</a>" .
				"<script type=\"text/javascript\" " .
				"src=\"https://platform.twitter.com/widgets.js\"></script>";
	}

	function buildMixiCheckScript(){
		return "<script type=\"text/javascript\" src=\"https://static.mixi.jp/js/share.js\"></script>";
	}

	function buildMixiLikeButton($key){
		return "<div data-plugins-type=\"mixi-favorite\" data-service-key=\"".$key."\" data-size=\"medium\" data-href=\"\" data-show-faces=\"true\" data-show-count=\"true\" data-show-comment=\"true\" data-width=\"450\"></div>".
				"<script type=\"text/javascript\">(function(d) {var s = d.createElement('script'); s.type = 'text/javascript'; s.async = true;s.src = '//static.mixi.jp/js/plugins.js#lang=ja';d.getElementsByTagName('head')[0].appendChild(s);})(document);</script>";
	}

	function buildPocketButton(){
		return "<a data-pocket-label=\"pocket\" data-pocket-count=\"horizontal\" class=\"pocket-btn\" data-lang=\"en\"></a>".
				"<script type=\"text/javascript\">" .
				"!function(d,i){if(!d.getElementById(i)){var j=d.createElement(\"script\");j.id=i;j.src=\"https://widgets.getpocket.com/v1/j/btn.js?v=1\";var w=d.getElementById(i);d.body.appendChild(j);}}(document,\"pocket-btn-js\");" .
				"</script>";
	}
}
