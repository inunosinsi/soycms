<?php
/*
 */
class UtilMultiLanguageItemName extends SOYShopItemNameBase{

	private $itemAttributeDao;

	function getForm(SOYShop_Item $item){
		SOY2::import("module.plugins.util_multi_language.util.UtilMultiLanguageUtil");

		$html = array();
		foreach(UtilMultiLanguageUtil::allowLanguages() as $lang => $title){
			if($lang == UtilMultiLanguageUtil::LANGUAGE_JP) continue;
			$itemName = self::getItemAttribute($item->getId(), $lang)->getValue();

			$html[] = "<dt>商品名(" . $lang . ")&nbsp;<a href=\"" . SOY2PageController::createLink("Config.Detail?plugin=util_multi_language&item_id=" . $item->getId() . "&language=" . $lang) ."\">カスタムフィールドの設定</a></dt>";
			$html[] = "<dd>";
			$html[] = "<input name=\"LanguageConfig[item_name_" . $lang . "]\" value=\"" . $itemName . "\" type=\"text\" class=\"form-control\">";
			$html[] = "</dd>";
		}

		return implode("\n", $html);
	}

	function doPost(SOYShop_Item $item){
		if(isset($_POST["LanguageConfig"])){
			foreach($_POST["LanguageConfig"] as $key => $value){
				$attr = self::getItemAttribute($item->getId(), str_replace("item_name_", "", $key));

				if(is_null($attr->getItemId())){
					$attr->setItemId($item->getId());
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

	private function getItemAttribute($itemId, $language){

		try{
			$attr = $this->getDao()->get($itemId, "item_name_" . $language);
		}catch(Exception $e){
			$attr = new SOYShop_ItemAttribute();
		}

		return $attr;
	}

	private function getDao(){
		if(!$this->itemAttributeDao){
			$this->itemAttributeDao = SOY2DAOFactory::create("shop.SOYShop_ItemAttributeDAO");
		}
		return $this->itemAttributeDao;
	}
}
SOYShopPlugin::extension("soyshop.item.name", "util_multi_language", "UtilMultiLanguageItemName");
