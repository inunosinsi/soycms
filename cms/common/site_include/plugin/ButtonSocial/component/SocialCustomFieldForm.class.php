<?php

class SocialCustomFieldForm {

	public static function buildForm($entryId){
		SOY2::import("site_include.plugin.ButtonSocial.util.ButtonSocialUtil");
		$imgPath = ButtonSocialUtil::getAttr($entryId)->getValue();

		try{
			$siteConfig = SOY2DAOFactory::create("cms.SiteConfigDAO")->get();
		}catch(Exception $e){
			$siteConfig = new SiteConfig();
		}

		$html = array();
		$html[] = "<div class=\"form-group\">";
		$html[] = "\t<label for=\"custom_field_img\">og:image <a href=\"javascript:void(0);\" title=\"FacebookやTwitterで投稿する時のサムネイル画像の指定\"><i class=\"fa fa-question-circle\"></i></a></label>";
		$html[] = "\t<div class=\"form-inline\">";
		$html[] = "\t\t<input type=\"text\" class=\"ogimage_field_input form-control\" style=\"width:50%\" id=\"ogimage_field\" name=\"" . ButtonSocialUtil::PLUGIN_KEY . "\" value=\"". $imgPath . "\" >";
		$html[] = "\t\t<button type=\"button\" class=\"btn btn-default\" onclick=\"open_ogimage_filemanager($('#ogimage_field'));\" style=\"margin-right:10px;\">ファイルを指定する</button>";
		if(strlen($imgPath) > 0){
			$html[] = "\t\t<a href=\"#\" onclick=\"return preview_ogimage_plugin(\$('#ogimage_field'));\" class=\"btn btn-info\">Preview</a>";
		}
		$html[] = "\t</div>";
		$html[] = "</div>";
		$html[] = "<script type=\"text/javascript\">";
		$html[] = "var \$custom_field_input = \$();";
		$html[] = "function open_ogimage_filemanager(\$form){";
		$html[] = "	\$custom_field_input = \$form;";
		$html[] = "	common_to_layer(\"" . SOY2PageController::createLink("Page.Editor.FileUpload?ogimage_field") . "\");";
		$html[] = "}";
		$html[] = "";
		$html[] = "function preview_ogimage_plugin(\$form){";
		$html[] = "	var domainURL = \"". self::getDomainUrl($siteConfig->getConfigValue("url")) . "\";";
		$html[] = "	var siteURL = \"" . UserInfoUtil::getSiteUrl() . "\";";
		$html[] = "";
		$html[] = "	var url = \"\";";
		$html[] = "	var href = \$form.val();";
		$html[] = "";
		$html[] = "	if(href && href.indexOf(\"/\") == 0){";
		$html[] = "		url = domainURL + href.substring(1, href.length);";
		$html[] = "	}else{";
		$html[] = "		url = siteURL + href;";
		$html[] = "	}";
		$html[] = "";
		$html[] = "	var temp = new Image();";
		$html[] = "	temp.src = url;";
		$html[] = "	temp.onload = function(e){";
		$html[] = "		common_element_to_layer(url, {";
		$html[] = "			height : Math.min(600, Math.max(400, temp.height + 20)),";
		$html[] = "			width  : Math.min(800, Math.max(400, temp.width + 20))";
		$html[] = "		});";
		$html[] = "	};";
		$html[] = "	temp.onerror = function(e){";
		$html[] = "		alert(url + \"が見つかりません。\");";
		$html[] = "	}";
		$html[] = "	return false;";
		$html[] = "}";
		$html[] = "</script>";
		return implode("\n", $html);
	}

	/**
	 * 念の為にURLからサイトIDを除いておく
	 * @param string url
	 * @return string url
	 */
	private static function getDomainUrl($url){
		$siteId = UserInfoUtil::getSite()->getSiteId();

		if(strpos($url, "/" . $siteId . "/")){
			$url = str_replace("/" . $siteId . "/", "/" , $url);
		}

		return $url;
	}
}
