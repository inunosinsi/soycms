<?php

/**
 * @entity cms.TemplateHistory
 */
abstract class TemplateHistoryDAO extends SOY2DAO{

	/**
	 * @return id
	 */
	abstract function insert(TemplateHistory $bean);
	abstract function update(TemplateHistory $bean);
	abstract function delete($id);
	
	/**
	 * @return object
	 */
	abstract function getById($id);
	
	abstract function get();
	
	/**
	 * @order id desc
	 */
	abstract function getByPageId($pageId);
	
	
	/**
	 * @final
	 */
	function deletePastHistory($pageId,$count = 10){
		$sql = 'SELECT id from TemplateHistory WHERE page_id = :pageId ORDER BY update_date DESC';
		
		$historyIds = $this->executeQuery($sql,array(':pageId'=>$pageId));
		
		if(count($historyIds) <= $count){
			return true;
		}
		
		foreach($historyIds as $entity){
			if($count-- > 0){
				continue;
			}
			$id = $entity['id'];
			try{
				$this->delete($id);	
			}catch(Exception $e){
				return false;
			}
			
		}
		return true;
	}
}
?>