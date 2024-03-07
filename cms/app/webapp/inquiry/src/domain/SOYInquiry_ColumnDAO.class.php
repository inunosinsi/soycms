<?php
/**
 * @entity SOYInquiry_Column
 */
abstract class SOYInquiry_ColumnDAO extends SOY2DAO{

    /**
	 * @return id
	 */
    abstract function insert(SOYInquiry_Column $bean);
    
    abstract function update(SOYInquiry_Column $bean);
    
    abstract function get();
    
    abstract function getByFormId($formId);
    
    /**
     * @columns count(id) as count_columns
     * @return row_count_columns
     */
    abstract function countByFormId($formId);
    
    /**
     * @order #order#
     */
    abstract function getOrderedColumnsByFormId($formId);
    
    /**
     * @return object
     */
    abstract function getById($id);
    
    abstract function delete($id);
    
    abstract function deleteByFormId($formId);
    
    /**
     * @columns #order#
     * @query id = :id
     */
    abstract function updateDisplayOrder($id, $order);
    
    /**
     * @columns #columnId#
     * @query id = :id
     */
    abstract function updateColumnId($id, $columnId);
    
    /**
     * @final
     */
    function reorderColumns($formId){
    	$columns = $this->getOrderedColumnsByFormId($formId);
    	
    	$count = 1;
    	foreach($columns as $column){
    		$this->updateDisplayOrder($column->getId(),$count * 10);
    		$this->updateColumnId($column->getId(),"column_" . $count);
    		$count++;
    	}
    }   
}