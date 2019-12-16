<?php
SOY2::import("site_include.plugin.tag_cloud.domain.TagCloudDictionary");
/**
 * @entity TagCloudDictionary
 */
abstract class TagCloudDictionaryDAO extends SOY2DAO{

	/**
	 * @return id
	 */
	abstract function insert(TagCloudDictionary $bean);

	/**
	 * @return object
	 */
	abstract function getById($id);

	/**
	 * @return object
	 */
	abstract function getByWord($word);
}
