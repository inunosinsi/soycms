<?php
/**
 * @table soyshop_tag_cloud_category
 */
class SOYShop_TagCloudCategory {

	/**
	 * @id
	 */
	private $id;
	private $label;

	function getId(){
		return $this->id;
	}
	function setId($id){
		$this->id = $id;
	}

	function getLabel(){
		return $this->label;
	}
	function setLabel($label){
		$this->label = $label;
	}
}
