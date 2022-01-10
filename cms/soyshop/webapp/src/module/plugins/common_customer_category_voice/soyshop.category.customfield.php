<?php
include(dirname(__FILE__) . "/common.php");
class CommonCustomerCategoryVoiceCustomfield extends SOYShopCategoryCustomFieldBase{

	function doPost(SOYShop_Category $category){
		$fieldId = "customer_category_voice_plugin";
		$attr = soyshop_get_category_attribute_object($category->getId(), $fieldId);

		$v = null;
		if(isset($_POST[$fieldId])){

			$names = $_POST["customer_category_voice_plugin"];
			$values = $_POST["customer_category_voice_text"];

			$arr = array();
			for($i = 0; $i < count($names); $i++){
				if(strlen($values[$i]) > 0){
					$obj = array();
					$obj["name"] = $names[$i];
					$obj["value"] = $values[$i];
					$arr[] = $obj;
				}
			}
			if(count($arr)) $v = soy2_serialize($arr);
		}
		$attr->setValue($v);
		soyshop_save_category_attribute_object($attr);
	}

	function getForm(SOYShop_Category $category){

		$class = new CustomerCategoryVoiceClass();

		$values = (is_numeric($category->getId())) ? soy2_unserialize(soyshop_get_category_attribute_value($category->getId(), "customer_category_voice_plugin", "string")) : array();

		$html = array();
		$html[] = "<h4>お客様の声</h4>";

		$counter = 1;
		if(count($values)){
			for($i = 0; $i < count($values); $i++){

				$html[] = $class->buildNameArea($values[$i]["name"]);
				$html[] = $class->buildTextArea($values[$i]["value"]);

				$counter++;
			}
		}

		$html[] = $class->buildNameArea();
		$html[] = $class->buildTextArea();

		return implode("\n", $html);
	}

	function onDelete(int $categoryId){
		SOY2DAOFactory::create("shop.SOYShop_CategoryAttributeDAO")->deleteByCategoryId($categoryId);
	}
}

SOYShopPlugin::extension("soyshop.category.customfield","common_category_customfield","CommonCustomerCategoryVoiceCustomfield");
