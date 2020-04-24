<?php
/**
 * @entity shop.SOYShop_Categories
 */
abstract class SOYShop_CategoriesDAO extends SOY2DAO{

	/**
	 * @index id
	 */
    abstract function get();
    
    /**
     * @return list
     */
    abstract function getByItemId($itemId);
    
   	/**
	 * @return id
	 */
   	abstract function insert(SOYShop_Categories $bean);
   	
	abstract function update(SOYShop_Categories $bean);
	
	abstract function delete($id);

	/**
	 * @final
	 */
	function deleteByItemId($itemId){
		try{
			$categories = $this->getByItemId($itemId);
			foreach($categories as $category){
				$id = $category->getId();
				$this->delete($id);
			}
		}catch(Exception $e){
		}
	}
	
	function getItemIdsByCategoryIds($categoryIds){
    	$query = $this->getQuery();
    	$binds = $this->getBinds();
    	
    	$query->sql = "item_id";
    	$query->where = "category_id in (" . implode(",", $categoryIds) . ")";
    	$query->distinct = "item_id";
    	    	
    	$res = array();
		$results = $this->executeQuery($query, $binds);
		
		$itemIds = array();
		foreach($results as $result){
			$itemIds[] = $result["item_id"];
		}
		
		return $itemIds;
    }
    
    /**
     * @columns count(distinct item_id) as item_count
     */
    function countItemsByCategoryIds($categoryIds){
    	$query = $this->getQuery();
    	$binds = $this->getBinds();
    	
    	$query->where = "category_id in (" . implode(",", $categoryIds) . ")";
    	
    	$res = array();
		$result = $this->executeQuery($query, $binds);
		
		if(count($result) > 0){
			return $result[0]["item_count"];
		}
		return 0;
    }
}
?>