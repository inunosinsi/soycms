<?php
/*
 */
SOY2::import("module.plugins.parts_item_detail.util.PartsItemDetailUtil");
class ItemDetailBeforeOutputField extends SOYShopItemCustomFieldBase{

	function doPost(SOYShop_Item $item){
		if(isset($_POST["BreadCrumbChange"])){
			$changes = $_POST["BreadCrumbChange"];
			if(isset($changes["pageId"])){
				$pageId = (isset($changes["pageId"])) ? (int)$changes["pageId"] : null;
				$attr = PartsItemDetailUtil::getAttr($item->getId(), PartsItemDetailUtil::FIELD_ID);
				$attr->setValue($pageId);
				PartsItemDetailUtil::saveAttr($attr, PartsItemDetailUtil::FIELD_ID);
			}

			if(isset($changes["parent"])){
				$pageId = (isset($changes["parent"])) ? (int)$changes["parent"] : null;
				$attr = PartsItemDetailUtil::getAttr($item->getId(), PartsItemDetailUtil::PARENT_FIELD_ID);
				$attr->setValue($pageId);
				PartsItemDetailUtil::saveAttr($attr, PartsItemDetailUtil::PARENT_FIELD_ID);
			}
		}
	}

	function getForm(SOYShop_Item $item){
		if(!SOYShopPluginUtil::checkIsActive("common_breadcrumb")) return "";

		$list = self::getPageList();
		if(!count($list)) return "";

		$html = array();
		$html[] = "<dt>パンくずナビゲーションの出力するURLの変更</dt>";
		$html[] = "<dd>";

		$html[] = "商品一覧ページ：";
		$html[] = "<select name=\"BreadCrumbChange[pageId]\">";
		$html[] = "<option></option>";

		$attr = PartsItemDetailUtil::getAttr($item->getId(), PartsItemDetailUtil::FIELD_ID);
		foreach($list as $page){
			if((int)$attr->getValue() === (int)$page->getId()){
				$html[] = "<option value=\"" . $page->getId() . "\" selected=\"selected\">" . $page->getName() . "</option>";
			}else{
				$html[] = "<option value=\"" . $page->getId() . "\">" . $page->getName() . "</option>";
			}
		}
		$html[] = "</select>";

		//子商品の場合、親商品のURIの変更も出来る
		if(is_numeric($item->getType())){
			$list = self::getPageListFormParent();
			if(count($list)){
				$html[] = "<br><br>";
				$html[] = "親商品のページ：";
				$html[] = "<select name=\"BreadCrumbChange[parent]\">";
				$html[] = "<option></option>";
				$attr = PartsItemDetailUtil::getAttr($item->getId(), PartsItemDetailUtil::PARENT_FIELD_ID);
				foreach($list as $page){
					$pageTypeTexts = SOYShop_Page::getTypeTexts();
					$title = $page->getName() . "&nbsp;(" . $pageTypeTexts[$page->getType()] . ")";
					if((int)$attr->getValue() === (int)$page->getId()){
						$html[] = "<option value=\"" . $page->getId() . "\" selected=\"selected\">" . $title . "</option>";
					}else{
						$html[] = "<option value=\"" . $page->getId() . "\">" . $title . "</option>";
					}
				}
				$html[] = "</select>";
			}
		}
		$html[] = "</dd>";

		return implode("\n", $html);
	}

	/**
	 * onOutput
	 */
	function onOutput($htmlObj, SOYShop_Item $item){}

	function onDelete(int $itemId){
		SOY2DAOFactory::create("shop.SOYShop_ItemAttributeDAO")->deleteByItemId($id);
	}

	private function getPageList(){
		try{
			return self::pageDao()->getByType(SOYShop_Page::TYPE_LIST);
		}catch(Exception $e){
			return array();
		}
	}

	private function getPageListFormParent(){
		$list = array();
		try{
			$list = self::pageDao()->getByType(SOYShop_Page::TYPE_DETAIL);
		}catch(Exception $e){
			//
		}

		try{
			$flist = self::pageDao()->getByType(SOYShop_Page::TYPE_FREE);
		}catch(Exception $e){
			//
		}

		$list = array_merge($list, $flist);
		return $list;
	}

	private function dao(){
		static $dao;
		if(is_null($dao)) $dao = SOY2DAOFactory::create("shop.SOYShop_ItemAttributeDAO");
		return $dao;
	}

	private function pageDao(){
		static $dao;
		if(is_null($dao)) $dao = SOY2DAOFactory::create("site.SOYShop_PageDAO");
		return $dao;
	}
}

SOYShopPlugin::extension("soyshop.item.customfield", "parts_item_detail", "ItemDetailBeforeOutputField");
