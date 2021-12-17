<?php
include(dirname(__FILE__) . "/common.php");
class CommonCustomerCategoryVoiceCustomfield extends SOYShopCategoryCustomFieldBase{

	function doPost(SOYShop_Category $category){

		$dao = SOY2DAOFactory::create("shop.SOYShop_CategoryAttributeDAO");
		$categoryDAO = SOY2DAOFactory::create("shop.SOYShop_CategoryDAO");
		$array = $dao->getByCategoryId($category->getId());

		$configs = SOYShop_CategoryAttributeConfig::load(true);

		$key = "customer_category_voice_plugin";
		$value = 1;

		try{
			$dao->delete($category->getId(),$key);
		}catch(Exception $e){

		}

		if(isset($_POST["customer_category_voice_plugin"])){

			$names = $_POST["customer_category_voice_plugin"];
			$values = $_POST["customer_category_voice_text"];

			$array = array();
			for($i = 0; $i < count($names); $i++){
				if(strlen($values[$i]) > 0){
					$obj = array();
					$obj["name"] = $names[$i];
					$obj["value"] = $values[$i];
					$array[] = $obj;
				}

			}

			if(count($array) > 0){
				$attr = soyshop_get_category_attribute_object($category->getId(), $key);
				$attr->setValue(soy2_serialize($array));
				soyshop_save_category_attribute_object($attr);
			}
		}
	}

	function getForm(SOYShop_Category $category){

		$class = new CustomerCategoryVoiceClass();

		$values = soy2_unserialize(soyshop_get_category_attribute_value($category->getId(), "customer_category_voice_plugin", "string"));

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

	function onDelete(int $id){
		SOY2DAOFactory::create("shop.SOYShop_CategoryAttributeDAO")->deleteByCategoryId($id);
	}
}

SOYShopPlugin::extension("soyshop.category.customfield","common_category_customfield","CommonCustomerCategoryVoiceCustomfield");
