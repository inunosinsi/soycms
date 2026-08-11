<?php
/**
 * @table soyshop_stock_history
 */
class SOYShop_StockHistory {

    /**
     * @column item_id
     */
    private $itemId;

	private $memo;

    /**
     * @column create_date
     */
    private $createDate;

    function getItemId(){
    	return (is_numeric($this->itemId)) ? (int)$this->itemId : 0;
    }
    function setItemId($itemId){
    	$this->itemId = $itemId;
    }

    function getMemo(){
    	return $this->memo;
    }
    function setMemo($memo){
    	$this->memo = $memo;
    }

    function getCreateDate(){
    	return $this->createDate;
    }
    function setCreateDate($createDate){
    	$this->createDate = $createDate;
    }
}
