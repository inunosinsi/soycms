<?php
/**
 * @entity SOYShop_Breadcrumb
 */
abstract class SOYShop_BreadcrumbDAO extends SOY2DAO{
	
	/**
	 * @index id
	 * @order id desc
	 */
	abstract function get();
	
	/**
	 * @return object
	 */
	abstract function getByItemId($itemId);
   	
   	/**
   	 * @return object
   	 */
   	abstract function getByPageId($pageId);
   	
   	/**
   	 * @param integer item_id
   	 * @return string
   	 */
   	function getPageUriByItemId($itemId){
   		$sql = "SELECT p.uri ".
   				"FROM soyshop_page p ".
   				"INNER JOIN soyshop_breadcrumb b ".
   				"ON p.id = b.page_id ".
   				"WHERE b.item_id = :item_id";
   		$binds = array(":item_id" => (int)$itemId);
   		
   		try{
   			$results = $this->executeQuery($sql, $binds);
   			if(isset($results[0]["uri"])) return $results[0]["uri"];
   		}catch(Exception $e){
   			//
   		}
   		
   		//無かった場合は一番最初に作成した商品一覧ページのuriを取得
   		$sql = "SELECT uri FROM soyshop_page ".
   				"WHERE type = 'list' ".
   				"ORDER BY id ASC ".
   				"LIMIT 1";
   				
   		try{
   			$results = $this->executeQuery($sql, array());
   		}catch(Exception $e){
   			return "";
   		}
   		
   		return (isset($results[0]["uri"])) ? $results[0]["uri"] : "";
   	}
   	
   	abstract function insert(SOYShop_Breadcrumb $bean);
   	
	abstract function update(SOYShop_Breadcrumb $bean);
	
	abstract function deleteByItemId($itemId);
	abstract function deleteByPageId($pageId);
}
?>