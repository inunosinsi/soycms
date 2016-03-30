<?php
/*
 */
class CommonCategoryCustomfield extends SOYShopCategoryCustomFieldBase{

	function doPost($category){

		$list = (isset($_POST["custom_field"])) ? $_POST["custom_field"] : array();
		
		$dao = SOY2DAOFactory::create("shop.SOYShop_CategoryAttributeDAO");
		$categoryDAO = SOY2DAOFactory::create("shop.SOYShop_CategoryDAO");
		$array = $dao->getByCategoryId($category->getId());

		$configs = SOYShop_CategoryAttributeConfig::load(true);
		
		foreach($list as $key => $value){
			
			if(!isset($configs[$key]))continue;
			
			$value2 = (isset($list[$key."_option"]) && strlen($list[$key."_option"]) > 0) ? $list[$key."_option"] : "";
			
			try{
				if(isset($array[$key])){
					$obj = $array[$key];
					$obj->setValue($value);
					$obj->setValue2($value2);
					$dao->update($obj);
				}else{
					$obj = new SOYShop_CategoryAttribute();
					$obj->setCategoryId($category->getId());
					$obj->setFieldId($key);
					$obj->setValue($value);
					$obj->setValue2($value2);

					$dao->insert($obj);
				}
			}catch(Exception $e){
			}
		
			if($configs[$key]->isIndex()){
				$$categoryDAO->updateSortValue($catogory->getId(),$key,$value);
			}

		}
		
		//チェックボックスが非選択時の処理
		foreach($configs as $key => $value){
			
			try{			
				if(!isset($list[$key]) && isset($array[$key])){
					$obj = $array[$key];
					$obj->setValue("");
					$obj->setValue2("");
					$dao->update($obj);
				}
			}catch(Exception $e){
			}
		}
	}

	function getForm($category){
		
		$dao = SOY2DAOFactory::create("shop.SOYShop_CategoryAttributeDAO");
		try{
			$array = $dao->getByCategoryId($category->getId());
		}catch(Exception $e){
			echo $e->getPDOExceptionMessage();
		}

		$html = array();
		$list = SOYShop_CategoryAttributeConfig::load();
		
		foreach($list as $config){
			$value = (isset($array[$config->getFieldId()])) ? $array[$config->getFieldId()]->getValue() : null;
			$value2 = (isset($array[$config->getFieldId()])) ? $array[$config->getFieldId()]->getValue2() : null;
			
			$html[] = $config->getForm($value,$value2);
		}

		return implode("\n", $html);
	}

	function onDelete($id){
		$attributeDAO = SOY2DAOFactory::create("shop.SOYShop_CategoryAttributeDAO");
		$attributeDAO->deleteByCategoryId($id);
	}

}

SOYShopPlugin::extension("soyshop.category.customfield","common_category_customfield","CommonCategoryCustomfield");
?>