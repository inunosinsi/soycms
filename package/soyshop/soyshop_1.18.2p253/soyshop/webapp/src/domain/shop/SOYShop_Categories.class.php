<?php
/**
 * @table soyshop_categories
 */
class SOYShop_Categories {

	/**
	  * @id
	  */
	private $id;
	
	/**
	 * @column item_id
	 */
	private $itemId;
	
	/**
	 * @column category_id
	 */
	private $categoryId;
	private $attribute;
	
	function getId(){
		return $this->id;
	}
	function setId($id){
		$this->id = $id;
	}
	
	function getItemId(){
		return $this->itemId;
	}
	function setItemId($itemId){
		$this->itemId = $itemId;
	}
	
	function getCategoryId(){
		return $this->categoryId;
	}
	function setCategoryId($categoryId){
		$this->categoryId = $categoryId;
	}
	
	function getAttribute(){
		return $this->attribute;
	}
	function setAttribute($attribute){
		$this->attribute = $attribute;
	}
}
?>