<?php

class ThumbnailPluginComponent {

	public static function addTags($htmlObj, int $entryId, string $fieldId){
		SOY2::import("site_include.plugin.soycms_thumbnail.util.ThumbnailPluginUtil");
		$tmbImagePathes = ($entryId > 0) ? ThumbnailPluginUtil::getThumbnailPathesByEntryId($entryId) : array();

		foreach(array("upload", "trimming", "resize") as $label){
			$imagePath = (isset($tmbImagePathes[ThumbnailPluginUtil::PREFIX_IMAGE . $label])) ? $tmbImagePathes[ThumbnailPluginUtil::PREFIX_IMAGE . $label] : "";
			if($label == "resize") $label = "thumbnail";

			$htmlObj->addModel($fieldId . "_is_" . $label, array(
				"soy2prefix" => "cms",
				"visible" => (strlen($imagePath) > 0)
			));

			$htmlObj->addModel($fieldId . "_no_" . $label, array(
				"soy2prefix" => "cms",
				"visible" => (strlen($imagePath) === 0)
			));

			$htmlObj->addImage($fieldId . "_" . $label, array(
				"soy2prefix" => "cms",
				"src" => $imagePath,
				"alt" => (isset($tmbImagePathes[ThumbnailPluginUtil::THUMBNAIL_ALT])) ? $tmbImagePathes[ThumbnailPluginUtil::THUMBNAIL_ALT] : ""
			));

			$htmlObj->addLabel($fieldId . "_" . $label . "_text", array(
				"soy2prefix" => "cms",
				"text" => $imagePath
			));

			$htmlObj->addLabel($fieldId . "_" . $label . "_path_text", array(
				"soy2prefix" => "cms",
				"text" => $imagePath
			));
		}
	}
}