<?php
class CustomSearchFieldChildListCustomField extends SOYShopItemCustomFieldBase{

	private $itemDao;
	private $categoryDao;
	private $breadDao;	//パンくず用のDAO
	private $installedBreadcrumbPlugin = false;

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
		
		//親商品情報の出力タグ
		$parent = self::getParentItem((int)$item->getType());
		$parentItemName = $parent->getOpenItemName();
			
		$parentCategory = self::getParentCategory($parent->getCategory());
		$parentCategoryName = $parentCategory->getOpenCategoryName();
		$parentCategoryAlias = $parentCategory->getAlias();
		
		//親商品のカテゴリの商品一覧ページへのリンクを調べる
		$listPageUri = self::getParentListPageByItemId($parent->getId());
		
		$htmlObj->addLink("parent_item_link", array(
			"soy2prefix" => CustomSearchFieldUtil::PLUGIN_PREFIX,
			"link" => soyshop_get_item_detail_link($parent)
		));
		
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
		
		$htmlObj->addLink("parent_category_link", array(
			"soy2prefix" => CustomSearchFieldUtil::PLUGIN_PREFIX,
			"link" => (isset($listPageUri)) ? soyshop_get_site_url() . $listPageUri . "/" . $parentCategoryAlias : null
		));
	}

	/**
	 * 管理画面側で商品情報を削除した時にオプション設定も一緒に削除する
	 * @param integer id
	 */
	function onDelete($itemId){}
	
	private function getParentItem($parentId){
		static $parents;
		if(is_null($parents)) $parents = array();
		
		if(!is_numeric($parentId)) return new SOYShop_Item();
		if(isset($parents[$parentId])) return $parents[$parentId];
		
		try{
			$parent = $this->itemDao->getById($parentId);
			$parents[$parentId] = $parent;
			return $parent;
		}catch(Exception $e){
			return new SOYShop_Item();
		}
	}
	
	private function getParentCategory($categoryId){
		static $categories;
		if(is_null($categories)) $categories = array();
		
		if(!is_numeric($categoryId)) return new SOYShop_Category();
		if(isset($categories[$categoryId])) return $categories[$categoryId];
		
		try{
			$category = $this->categoryDao->getById($categoryId);
			$categories[$categoryId] = $category;
			return $category;
		}catch(Exception $e){
			return new SOYShop_Category();
		}
	}
	
	private function getParentListPageByItemId($parentId){
		static $results;
		if(is_null($results)) $results = array();
		
		if(!$this->installedBreadcrumbPlugin || is_null($parentId)) return null;
		if(isset($results[$parentId])) return $results[$parentId];
				
		try{
			$uri = $this->breadDao->getPageUriByItemId($parentId);
			$results[$parentId] = $uri;
			return $uri;
		}catch(Exception $e){
			return null;
		}
	}
	
	private function prepare(){
		if(!$this->itemDao) {
			$this->itemDao = SOY2DAOFactory::create("shop.SOYShop_ItemDAO");
			$this->categoryDao = SOY2DAOFactory::create("shop.SOYShop_CategoryDAO");
			
			//パンくずモジュールをインストールしているか？
			SOY2::import("util.SOYShopPluginUtil");
			$this->installedBreadcrumbPlugin = SOYShopPluginUtil::checkIsActive("common_breadcrumb");
			
			if($this->installedBreadcrumbPlugin){
				SOY2::imports("module.plugins.common_breadcrumb.domain.*");
				$this->breadDao = SOY2DAOFactory::create("SOYShop_BreadcrumbDAO");
				SOY2::import("module.plugins.custom_search_field.util.CustomSearchFieldUtil");
			}
		}
	}
}

SOYShopPlugin::extension("soyshop.item.customfield", "custom_search_field_child_list", "CustomSearchFieldChildListCustomField");
?>