<?php

class CustomFieldForm {

	public static function buildForm($entryId){
		$html = array();
		$html[] = "<div class=\"form-group\">";
		$html[] = "<label for=\"tag_cloud_plugin\">タグ<span class=\"help\"><i class=\"fa fa-question-circle fa-fw\" data-toggle=\"tooltip\" data-placement=\"right\" title=\"カンマ区切りでタグを登録します\"></i></span></label>";
		$html[] = "<input type=\"text\" id=\"tag_cloud_plugin\" name=\"TagCloudPlugin[tag]\" class=\"form-control\" value=\"" . self::_getRegisteredTags($entryId) . "\" placeholder=\"カンマ区切りでタグを登録します\">";
		$html[] = "</div>";

		SOY2::import("site_include.plugin.tag_cloud.util.TagCloudUtil");
		$cnf = TagCloudUtil::getConfig();
		if(!isset($cnf["tags"]) || !strlen($cnf["tags"])) return implode("\n", $html);

		$html[] = "<label>タグクラウドのタグの候補</label>";
		$html[] = "<div id=\"tag_cloud_word_candidate\" style=\"margin-left:3px;margin-bottom:10px;\"></div>";

		$tags = explode(",", $cnf["tags"]);
		$html[] = "<script>var tag_cloud_plugin_word_list = [\"" . implode("\",\"", $tags) . "\"];</script>";
		$html[] = "<script>";
		$html[] = file_get_contents(dirname(dirname(__FILE__)) . "/js/script.js");
		$html[] = "</script>";

		$html[] = "<style>";
		$html[] = file_get_contents(dirname(dirname(__FILE__)) . "/css/style.css");
		$html[] = "</style>";

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
