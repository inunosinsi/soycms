<?php
/**
 * @table soyshop_yayoi_csv_output_date
 */
class SOYShop_YayoiOutput{
	
	/**
	 * @id
	 */
	private $id;
	
	/**
	 * @column output_date
	 */
	private $outputDate;
	
	/**
	 * @column create_date
	 */
	private $createDate;
	
	function getId(){
		return $this->id;
	}
	function setId($id){
		$this->id = $id;
	}
	
	function getOutputDate(){
		return $this->outputDate;
	}
	function setOutputDate($outputDate){
		$this->outputDate = $outputDate;
	}
	
	function getCreateDate(){
		return $this->createDate;
	}
	function setCreateDate($createDate){
		$this->createDate = $createDate;
	}
}
?>