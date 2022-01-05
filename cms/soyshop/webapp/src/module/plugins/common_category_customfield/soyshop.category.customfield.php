<?php
/*
 */
class CommonCategoryCustomfield extends SOYShopCategoryCustomFieldBase{

	function doPost(SOYShop_Category $category){

		$list = (isset($_POST["custom_field"])) ? $_POST["custom_field"] : array();

		$categoryDAO = SOY2DAOFactory::create("shop.SOYShop_CategoryDAO");
		$array = SOY2DAOFactory::create("shop.SOYShop_CategoryAttributeDAO")->getByCategoryId($category->getId());

		$configs = SOYShop_CategoryAttributeConfig::load(true);

		foreach($list as $fieldId => $value){

			if(!isset($configs[$fieldId])) continue;

			//type=checkboxesの時
			if($configs[$fieldId]->getType() === "checkboxes"){
				$value = (isset($value) && count($value)) ? implode(",", $value) : null;
			}

			$value2 = (isset($list[$fieldId."_option"]) && strlen($list[$fieldId."_option"]) > 0) ? $list[$fieldId."_option"] : null;
			
			$attr = (isset($array[$fieldId])) ? $array[$fieldId] : soyshop_get_category_attribute_object($category->getId(), $fieldId);
			$attr->setValue($value);
			$attr->setValue2($value2);
			soyshop_save_category_attribute_object($attr);

			if($configs[$fieldId]->isIndex()){
				$$categoryDAO->updateSortValue($catogory->getId(), $fieldId, $value);
			}
		}

		//チェックボックスが非選択時の処理
		foreach($configs as $fieldId => $cnf){
			if(isset($list[$fieldId])) continue;
			if($cnf->getType() != "checkbox" && $cnf->getType() != "checkboxes" && $cnf->getType() != "radio") continue;
			$attr = soyshop_get_category_attribute_object($category->getId(), $fieldId);
			$attr->setValue(null);
			$attr->setValue2(null);
			soyshop_save_category_attribute_object($attr);
		}
	}

	function getForm(SOYShop_Category $category){

		$dao = SOY2DAOFactory::create("shop.SOYShop_CategoryAttributeDAO");
		try{
			$array = $dao->getByCategoryId($category->getId());
		}catch(Exception $e){
			echo $e->getPDOExceptionMessage();
		}

		$html = array();
		$list = SOYShop_CategoryAttributeConfig::load(true);

		foreach($list as $fieldId => $config){
			$value = (isset($array[$fieldId])) ? (string)$array[$fieldId]->getValue() : "";
			$value2 = (isset($array[$fieldId])) ? (string)$array[$fieldId]->getValue2() : "";
			$html[] = $config->getForm($value, $value2);
		}

		return implode("\n", $html);
	}

	function onDelete(int $categoryId){
		SOY2DAOFactory::create("shop.SOYShop_CategoryAttributeDAO")->deleteByCategoryId($categoryId);
	}
}

SOYShopPlugin::extension("soyshop.category.customfield","common_category_customfield","CommonCategoryCustomfield");
