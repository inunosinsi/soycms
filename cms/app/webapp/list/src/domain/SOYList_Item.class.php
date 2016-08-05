<?php
/**
 * @table soylist_item
 */
class SOYList_Item {

	/**
	 * @id
	 */
	private $id;
	private $name;
	private $category;
	private $image;
	private $price;
	private $standard;
	private $description;
	private $url;
	private $sort;
	
	/**
	 * @column create_date
	 */
	private $createDate;
	
	/**
	 * @column update_date
	 */
	private $updateDate;
	
	function getId(){
		return $this->id;
	}
	function setId($id){
		$this->id = $id;
	}
	
	function getName(){
		return $this->name;
	}
	function setName($name){
		$this->name = $name;
	}
	
	function getCategory(){
		return $this->category;
	}
	function setCategory($category){
		$this->category = $category;
	}
	
	function getImage(){
		return $this->image;
	}
	function setImage($image){
		$this->image = $image;
	}
	
	function getPrice(){
		return $this->price;
	}
	function setPrice($price){
		$this->price = $price;
	}
	
	function getStandard(){
		return $this->standard;
	}
	function setStandard($standard){
		$this->standard = $standard;
	}
	
	function getDescription(){
		return $this->description;
	}
	function setDescription($description){
		$this->description = $description;
	}
	
	function getUrl(){
		return $this->url;
	}
	function setUrl($url){
		$this->url = $url;
	}
	
	function getSort(){
		return $this->sort;
	}
	function setSort($sort){
		$this->sort = $sort;
	}
	
	function getCreateDate(){
		return $this->createDate;
	}
	function setCreateDate($createDate){
		$this->createDate = $createDate;
	}
	
	function getUpdateDate(){
		return $this->updateDate;
	}
	function setUpdateDate($updateDate){
		$this->updateDate = $updateDate;
	}

}
?>