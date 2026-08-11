<?php
class TagCloudCategoryDivListComponent extends HTMLList {

	protected function populateItem($entity, $key){
		$tags = (is_numeric($key)) ? self::_getTags($key) : array();

		$this->addLabel("label", array(
			"text" => (is_string($entity)) ? $entity : ""
		));

		$this->addActionLink("remove_link", array(
			"link" => (is_numeric($key)) ? SOY2PageController::createLink("Config.Detail?plugin=tag_cloud&category&category_id=" . $key) : null,
			"onclick" => "return confirm('削除しますか？')"
		));

		$this->addModel("category_tag_area", array(
			"attr:id" => (is_numeric($key)) ? "category_tag_area_" . $key : ""
		));

		if(!class_exists("TagCloudCategoryTagListComponent")) SOY2::import("module.plugins.tag_cloud.component.TagCloudCategoryTagListComponent");
		$this->createAdd("category_div_tag_list", "TagCloudCategoryTagListComponent", array(
			"list" => $tags
		));
	}

	private function _getTags(int $categoryId){
		try{
			return self::_dicDao()->getByCategoryId($categoryId);
		}catch(Exception $e){
			return array();
		}
	}

	private function _dicDao(){
		static $dao;
		if(is_null($dao)) {
			SOY2::import("module.plugins.tag_cloud.domain.SOYShop_TagCloudDictionaryDAO");
			$dao = SOY2DAOFactory::create("SOYShop_TagCloudDictionaryDAO");
		}
		return $dao;
	}
}
