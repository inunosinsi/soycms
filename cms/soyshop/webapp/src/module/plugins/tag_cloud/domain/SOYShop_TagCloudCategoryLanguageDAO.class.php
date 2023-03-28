<?php
SOY2::import("module.plugins.tag_cloud.domain.SOYShop_TagCloudCategoryLanguage");
/**
 * @entity SOYShop_TagCloudCategoryLanguage
 */
abstract class SOYShop_TagCloudCategoryLanguageDAO extends SOY2DAO{

	/**
	 * @return id
	 */
	abstract function insert(SOYShop_TagCloudCategoryLanguage $bean);

	/**
	 * @query word_id = :wordId AND lang = :lang
	 */
	abstract function update(SOYShop_TagCloudCategoryLanguage $bean);

	/**
	 * @return object
	 * @query category_id = :categoryId AND lang = :lang
	 */
	abstract function get(int $categoryId, string $lang);

	/**
	 * @return list
	 */
	abstract function getByLang(string $lang);

	/**
	 * @return object
	 * @query lang = :lang AND label = :label
	 */
	abstract function getByLangAndLabel(string $lang, string $label);

	/**
	 * @query category_id = :categoryId AND lang = :lang
	 */
	abstract function deleteByCategoryIdAndLang(int $categoryId, string $lang);

	/**
	 * @final
	 */
	function getTranslatedCategoryList(){
		try{
			$res = $this->executeQuery("SELECT * FROM soyshop_tag_cloud_category_language");
		}catch(Exception $e){
			$res = array();
		}
		
		if(!isset($res[0])) return array();

		$list = array();
		foreach($res as $arr){
			$l = $arr["lang"];
			if(!isset($list[$l])) $list[$l] = array();
			$list[$l][(int)$arr["category_id"]] = $arr["label"];
		}

		return $list;
	}

	/**
	 * @final
	 */
	function getTranslatedCategoryListByCategoryIdAndLang(array $categoryIds, string $lang){
		if(!count($categoryIds)) return array();

		try{
			$res = $this->executeQuery("SELECT * FROM soyshop_tag_cloud_category_language WHERE category_id IN (".implode(",", $categoryIds).") AND lang = :lang", array(":lang" => $lang));
		}catch(Exception $e){
			$res = array();
		}
		
		if(!isset($res[0])) return array();

		$list = array();
		foreach($res as $arr){
			$list[(int)$arr["category_id"]] = $arr["label"];
		}

		return $list;
	}
}
