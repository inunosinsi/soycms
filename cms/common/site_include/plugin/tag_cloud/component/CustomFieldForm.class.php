<?php

class CustomFieldForm {

	public static function buildForm($entryId){
		$html = array();
		$html[] = "<div class=\"form-group\">";
		$html[] = "<label for=\"tag_cloud_plugin\">タグ<span class=\"help\"><i class=\"fa fa-question-circle fa-fw\" data-toggle=\"tooltip\" data-placement=\"right\" title=\"カンマ区切りでタグを登録します\"></i></span></label>";
		$html[] = "<input type=\"text\" name=\"TagCloudPlugin[tag]\" class=\"form-control\" value=\"" . self::_getRegisteredTags($entryId) . "\" placeholder=\"カンマ区切りでタグを登録します\">";
		$html[] = "</div>";
		return implode("\n", $html);
	}

	private static function _getRegisteredTags($entryId){
		SOY2::imports("site_include.plugin.tag_cloud.domain.*");

		try{
			$links = SOY2DAOFactory::create("TagCloudLinkingDAO")->getByEntryId($entryId);
		}catch(Exception $e){
			$links = array();
		}

		if(!count($links)) return "";

		$dicDao = SOY2DAOFactory::create("TagCloudDictionaryDAO");
		$tagStr = "";
		foreach($links as $link){
			try{
				$tagStr .= $dicDao->getById($link->getWordId())->getWord() . ",";
			}catch(Exception $e){
				//
			}
		}
		return trim($tagStr, ",");
	}
}
