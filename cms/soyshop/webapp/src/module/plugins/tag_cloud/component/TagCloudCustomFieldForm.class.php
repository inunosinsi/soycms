<?php

class TagCloudCustomFieldForm {

	public static function buildForm(int $itemId){
		SOY2::import("module.plugins.tag_cloud.util.TagCloudUtil");
		$tags = TagCloudUtil::getRegisterdTagsByItemId($itemId);
		
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

		//カテゴリを加味
		$tagsWithCategory = self::_tagWithCategory(explode(",", $cnf["tags"]));
		if(count($tagsWithCategory)){
			$html[] = "<script>";
			$html[] = "var tag_cloud_plugin_word_list = [];";
			foreach($tagsWithCategory as $categoryId => $tags){
				$html[] = "tag_cloud_plugin_word_list.push({\"category_id\":" . $categoryId . ",\"tags\":[\"" . implode("\",\"", $tags) . "\"]});";
			}
			$list = TagCloudUtil::getTagCategoryList();
			$html[] = "var tag_cloud_plugin_category_list = [];";
			foreach($list as $categoryId => $label){
				$html[] = "tag_cloud_plugin_category_list[" . $categoryId . "] = \"" . $label . "\";";
			}
			$html[] = "</script>";

			$html[] = "<script>";
			$html[] = file_get_contents(dirname(dirname(__FILE__)) . "/js/script.js");
			$html[] = "</script>";

			$html[] = "<style>";
			$html[] = file_get_contents(dirname(dirname(__FILE__)) . "/css/style.css");
			$html[] = "</style>";
		}


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

	private static function _tagWithCategory(array $tags){
		// タグカテゴリに登録されていないタグをカテゴライズする
		TagCloudUtil::prepareCategory(TagCloudUtil::getTagCategoryList());

		$dao = new SOY2DAO();
		try{
			$res = $dao->executeQuery("SELECT word, category_id FROM soyshop_tag_cloud_dictionary WHERE word IN ('" . implode("','", $tags) . "')");
		}catch(Exception $e){
			$res = array();
		}
		if(!count($res)) return array();
		
		$tags = array();
		foreach($res as $v){
			$id = (isset($v["category_id"])) ? (int)$v["category_id"] : 0;
			if(!isset($tags[$id])) $tags[$id] = array();
			$tags[$id][] = $v["word"];
		}

		return $tags;
	}
}
