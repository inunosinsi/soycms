<?php
SOY2::import("module.plugins.tag_cloud.domain.SOYShop_TagCloudLanguage");
/**
 * @entity SOYShop_TagCloudLanguage
 */
abstract class SOYShop_TagCloudLanguageDAO extends SOY2DAO{

	/**
	 * @return id
	 */
	abstract function insert(SOYShop_TagCloudLanguage $bean);

	/**
	 * @query word_id = :wordId AND lang = :lang
	 */
	abstract function update(SOYShop_TagCloudLanguage $bean);

	/**
	 * @return object
	 * @query word_id = :wordId AND lang = :lang
	 */
	abstract function get(int $wordId, string $lang);

	/**
	 * @return list
	 */
	abstract function getByLang(string $lang);

	/**
	 * @query word_id = :wordId AND lang = :lang
	 */
	abstract function deleteByWordIdAndLang(int $wordId, string $lang);

	/**
	 * @final
	 */
	function getTranslatedWordList(){
		try{
			$res = $this->executeQuery("SELECT * FROM soyshop_tag_cloud_language");
		}catch(Exception $e){
			$res = array();
		}
		
		if(!isset($res[0])) return array();

		$list = array();
		foreach($res as $arr){
			$l = $arr["lang"];
			if(!isset($list[$l])) $list[$l] = array();
			$list[$l][(int)$arr["word_id"]] = $arr["label"];
		}

		return $list;
	}

	/**
	 * @final
	 */
	function getTranslatedWordListByWordIdAndLang(array $wordIds, string $lang){
		if(!count($wordIds)) return array();

		try{
			$res = $this->executeQuery("SELECT * FROM soyshop_tag_cloud_language WHERE word_id IN (".implode(",", $wordIds).") AND lang = :lang", array(":lang" => $lang));
		}catch(Exception $e){
			$res = array();
		}
		
		if(!isset($res[0])) return array();

		$list = array();
		foreach($res as $arr){
			$list[(int)$arr["word_id"]] = $arr["label"];
		}

		return $list;
	}
}
