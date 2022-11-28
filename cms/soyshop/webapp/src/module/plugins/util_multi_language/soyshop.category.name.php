<?php
/*
 */
class UtilMultiLanguageCategoryName extends SOYShopCategoryNameBase{

	private $categoryAttributeDao;

	function getForm(SOYShop_Category $category){
		SOY2::import("module.plugins.util_multi_language.util.UtilMultiLanguageUtil");

		$html = array();
		foreach(UtilMultiLanguageUtil::allowLanguages() as $lang => $title){
			if($lang == UtilMultiLanguageUtil::LANGUAGE_JP) continue;
			$categoryName = self::getCategoryAttribute($category->getId(), $lang)->getValue();

			$html[] = "<h4>カテゴリ名(" . $lang . ")</h4>";
			$html[] = "<input name=\"LanguageConfig[category_name_" . $lang . "]\" value=\"" . $categoryName . "\" type=\"text\" class=\"form-control\">";
		}

		return implode("\n", $html);
	}

	function doPost(SOYShop_Category $category){
		if(isset($_POST["LanguageConfig"])){
			foreach($_POST["LanguageConfig"] as $key => $value){
				$attr = self::getCategoryAttribute($category->getId(), str_replace("category_name_", "", $key));

				if(is_null($attr->getCategoryId())){
					$attr->setCategoryId($category->getId());
					$attr->setFieldId($key);
					$attr->setValue($value);
					try{
						$this->getDao()->insert($attr);
					}catch(Exception $e){
						//
					}
				}else{
					$attr->setValue($value);
					try{
						$this->getDao()->update($attr);
					}catch(Exception $e){
						//
					}
				}
			}
		}
	}

	private function getCategoryAttribute($categoryId, $language){

		try{
			$attr = $this->getDao()->get($categoryId, "category_name_" . $language);
		}catch(Exception $e){
			$attr = new SOYShop_CategoryAttribute();
		}

		return $attr;
	}

	private function getDao(){
		if(!$this->categoryAttributeDao){
			$this->categoryAttributeDao = SOY2DAOFactory::create("shop.SOYShop_CategoryAttributeDAO");
		}
		return $this->categoryAttributeDao;
	}
}
SOYShopPlugin::extension("soyshop.category.name", "util_multi_language", "UtilMultiLanguageCategoryName");
