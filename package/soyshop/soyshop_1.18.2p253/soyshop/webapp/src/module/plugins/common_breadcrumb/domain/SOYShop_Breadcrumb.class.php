<?php
/**
 * @table soyshop_breadcrumb
 */
class SOYShop_Breadcrumb {

	/**
	 * @column item_id
	 */
	private $itemId;

	/**
	 * @column page_id
	 */
	private $pageId;

	function getItemId(){
		return $this->itemId;
	}
	function setItemId($itemId){
		$this->itemId = $itemId;
	}

	function getPageId(){
		return $this->pageId;
	}
	function setPageId($pageId){
		$this->pageId = $pageId;
	}
}
