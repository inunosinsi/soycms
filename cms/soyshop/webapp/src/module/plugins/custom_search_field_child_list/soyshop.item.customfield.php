<?php
class CustomSearchFieldChildListCustomField extends SOYShopItemCustomFieldBase{

	private $itemDao;
	private $categories = array();

	/**
	 * 管理画面側で商品情報を更新する際に読み込まれる
	 * 設定内容をデータベースに放り込む
	 * @param object SOYShop_Item
	 */
	function doPost(SOYShop_Item $item){}

	/**
	 * 管理画面側の商品詳細画面でフォームを表示します。
	 * @param object SOYShop_Item
	 * @return string html
	 */
	function getForm(SOYShop_Item $item){}
	
	/**
	 * 公開側のblock:id="item"で囲まれた箇所にフォームを出力する
	 * @param object htmlObj, object SOYShop_Item
	 */
	function onOutput($htmlObj, SOYShop_Item $item){
		
		self::prepare();
		$parentItemName = "";
		$parentCategoryName = "";
		$parentCategoryAlias = "";
		
		//親商品のカテゴリを取得したい
		if(is_numeric($item->getType())){
			$parent = self::getParentItem((int)$item->getType());
			$parentItemName = $parent->getName();
			
			if(!is_null($parent->getCategory())){
				$parentCategory = (isset($this->categories[$parent->getCategory()])) ? $this->categories[$parent->getCategory()] : array();
				$parentCategoryName = (isset($parentCategory["name"])) ? $parentCategory["name"] : "";
				$parentCategoryAlias = (isset($parentCategory["alias"])) ? $parentCategory["alias"] : "";
			}
		}
		
		$htmlObj->addLabel("parent_item_name", array(
			"soy2prefix" => CustomSearchFieldUtil::PLUGIN_PREFIX,
			"text" => $parentItemName
		));
		
		$htmlObj->addLabel("parent_category_name", array(
			"soy2prefix" => CustomSearchFieldUtil::PLUGIN_PREFIX,
			"text" => $parentCategoryName
		));
		
		$htmlObj->addLabel("parent_category_alias", array(
			"soy2prefix" => CustomSearchFieldUtil::PLUGIN_PREFIX,
			"text" => $parentCategoryAlias
		));
	}

	/**
	 * 管理画面側で商品情報を削除した時にオプション設定も一緒に削除する
	 * @param integer id
	 */
	function onDelete($itemId){}
	
	private function prepare(){
		if(!$this->itemDao) {
			$this->itemDao = SOY2DAOFactory::create("shop.SOYShop_ItemDAO");
			SOY2::import("module.plugins.custom_search_field.util.CustomSearchFieldUtil");
			
			//カテゴリを取得しておく
			try{
				$categories = SOY2DAOFactory::create("shop.SOYShop_CategoryDAO")->getByIsOpen(SOYShop_Category::IS_OPEN);
			}catch(Exception $e){
				$categories = array();
			}
			
			if(count($categories)){
				foreach($categories as $category){
					$this->categories[$category->getId()] = array("name" => $category->getName(), "alias" => $category->getAlias());
				}
			}
			
			$categories = array();
		}
	}
	
	private function getParentItem($parentId){
		try{
			return $this->itemDao->getById($parentId);
		}catch(Exception $e){
			return new SOYShop_Item();
		}
	}
}

SOYShopPlugin::extension("soyshop.item.customfield", "custom_search_field_child_list", "CustomSearchFieldChildListCustomField");
?>