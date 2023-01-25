<?php

class EntryFieldComponent {

	public static function addTags($htmlObj, Entry $entry, string $fieldId){
		$htmlObj->addLabel($fieldId . "_id", array(
			"text" => $entry->getId(),
			"soy2prefix"=>"cms"
		));
		$htmlObj->addLabel($fieldId . "_alias", array(
		   "text" => $entry->getAlias(),
		   "soy2prefix"=>"cms"
		));
		$htmlObj->addLabel($fieldId . "_title", array(
			"text" => $entry->getTitle(),
			"soy2prefix"=>"cms"
		));
		$htmlObj->createAdd($fieldId . "_content", "CMSLabel", array(
			"html" => $entry->getContent(),
			"soy2prefix"=>"cms"
		));
		$htmlObj->createAdd($fieldId . "_more", "CMSLabel", array(
			"html" => $entry->getMore(),
			"soy2prefix"=>"cms"
		));
		$htmlObj->createAdd($fieldId . "_create_date", "DateLabel", array(
			"text" => $entry->getCdate(),
			"soy2prefix"=>"cms"
		));
	}

	public static function addOrderPartsTags($htmlObj, int $labelId, string $fieldId){
		if(!class_exists("EntryFieldUtil")) SOY2::import("site_include.plugin.CustomFieldAdvanced.util.EntryFieldUtil");

		/**
		 * cms:id="{fieldId}_label_caption"
		 * cms:id="{fieldId}_label_alias"
		 */
		$arr = ($labelId > 0) ? EntryFieldUtil::getLabelCaptionAndAliasById($labelId) : array("caption" => "", "alias" => "");
		foreach(array("caption", "alias") as $idx){
			$htmlObj->createAdd($fieldId."_label_".$idx, "CMSLabel", array(
				"text" => (isset($arr[$idx])) ? $arr[$idx] : "",
				"soy2prefix"=>"cms"
			));
		}

		/**
		 * cms:id="{fieldId}_blog_title"
		 * cms:id="{fieldId}_blog_uri"
		 */
		$arr = (isset($arr["caption"]) && strlen($arr["caption"])) ? EntryFieldUtil::getBlogTitleAndUri($labelId, $arr["caption"]) : array("title" => "", "uri" => "");
		foreach(array("title", "uri") as $idx){
			$htmlObj->createAdd($fieldId."_blog_".$idx, "CMSLabel", array(
				"text" => (isset($arr[$idx])) ? $arr[$idx] : "",
				"soy2prefix" => "cms"
			));
		}		
	}
}

