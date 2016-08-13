<?php
/**
 * @table soyvoice_log
 */
class SOYVoice_Log {

	/**
	 * @id
	 */
    private $id;
    private $count;
    
    /**
     * @column export_date
     */
    private $exportDate;
    
    function getId(){
    	return $this->id;
    }
    function setId($id){
    	$this->id = $id;
    }
    
    function getCount(){
    	return $this->count;
    }
    function setCount($count){
    	$this->count = $count;
    }
    
    function getExportDate(){
    	return $this->exportDate;
    }
    function setExportDate($exportDate){
    	$this->exportDate = $exportDate;
    }
}
?>