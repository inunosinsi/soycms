<?php
/*
 */
class CustomSearchFieldCategory extends SOYShopCategoryCustomFieldBase{

	const FIELD_ID = "custom_search_field";
	private $dbLogic;

	function doPost($category){

		if(isset($_POST["custom_search"])){
				self::prepare();
				$this->dbLogic->save($category->getId(), $_POST["custom_search"]);
		}
	}

	function getForm($category){
		self::prepare();

		$values = $this->dbLogic->getByCategoryId($category->getId());

		$html = array();

		SOY2::import("module.plugins." . self::FIELD_ID . ".component.FieldFormComponent");
		foreach(CustomSearchFieldUtil::getCategoryConfig() as $key => $field){
			$html[] = "<label>" . $field["label"] . " (" . CustomSearchFieldUtil::PLUGIN_PREFIX . ":id=\"" . $key . "\")</label><br>";

			$value = (isset($values[$key])) ? $values[$key] : null;
			$html[] = FieldFormComponent::buildForm($key, $field, $value);
		}

		return implode("\n", $html);
	}

	function onDelete($id){}

	private function prepare(){
    if(!$this->dbLogic){
      $this->dbLogic = SOY2Logic::createInstance("module.plugins.custom_search_field.logic.DataBaseLogic", array("mode" => "category"));
      SOY2::import("module.plugins.custom_search_field.util.CustomSearchFieldUtil");
  	}

  	//多言語の方も念のため
  	if(!defined("SOYSHOP_PUBLISH_LANGUAGE")) define("SOYSHOP_PUBLISH_LANGUAGE", "jp");
  }
}

SOYShopPlugin::extension("soyshop.category.customfield", "custom_search_field", "CustomSearchFieldCategory");
