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
		return (is_numeric($this->itemId)) ? (int)$this->itemId : 0;
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
