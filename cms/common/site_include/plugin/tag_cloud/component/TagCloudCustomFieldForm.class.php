<?php

class TagCloudCustomFieldForm {

	public static function buildForm($entryId){
		SOY2::import("site_include.plugin.tag_cloud.util.TagCloudUtil");
		$tags = TagCloudUtil::getRegisterdTagsByEntryId($entryId);

		$tagValue = (count($tags)) ? self::_tagValue($tags) : "";

		$html = array();
		$html[] = "<div class=\"form-group\">";
		$html[] = "<label for=\"tag_cloud_plugin\">タグ<span class=\"help\"><i class=\"fa fa-question-circle fa-fw\" data-toggle=\"tooltip\" data-placement=\"right\" title=\"カンマ区切りでタグを登録します\"></i></span></label>";
		$html[] = "<input type=\"text\" id=\"tag_cloud_plugin\" name=\"TagCloudPlugin[tag]\" class=\"form-control\" value=\"" . $tagValue . "\" placeholder=\"カンマ区切りでタグを登録します\">";
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

	private static function _tagValue($tags){
		$list = array();
		foreach($tags as $tag){
			if(!isset($tag["word"]) || !strlen($tag["word"])) continue;
			$list[] = $tag["word"];
		}
		return implode(",", $list);
	}
}
