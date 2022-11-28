<?php
SOY2::import("module.plugins.tag_cloud.domain.SOYShop_TagCloudCategory");
/**
 * @entity SOYShop_TagCloudCategory
 */
abstract class SOYShop_TagCloudCategoryDAO extends SOY2DAO{

	/**
	 * @return id
	 */
	abstract function insert(SOYShop_TagCloudCategory $bean);

	abstract function update(SOYShop_TagCloudCategory $bean);

	abstract function get();

	/**
	 * @return object
	 */
	abstract function getById($id);

	abstract function deleteById($id);

	/**
	 * @final
	 */
	function getCategoryIdList(){
		try{
			$res = $this->executeQuery("SELECT id FROM soyshop_tag_cloud_category");
		}catch(Exception $e){
			$res = array();
		}
		if(!count($res)) return array();

		$ids = array();
		foreach($res as $v){
			$ids[] = (int)$v["id"];
		}
		return $ids;
	}
}
