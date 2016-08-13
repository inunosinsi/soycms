<?php
/**
 * @entity SOYBoard_Thread
 */
abstract class SOYBoard_ThreadDAO extends SOY2DAO{

    /**
	 * @return id
	 */
    abstract function insert(SOYBoard_Thread $bean);
    
    abstract function update(SOYBoard_Thread $bean);
    
    
    abstract function get();
    
    /**
     * @return object
     */
    abstract function getById($id);
    
	/**
     *	@sql UPDATE  soyboard_thread SET lastsubmitdate = :lastsubmitdate WHERE id = :threadId
     */   
    abstract function updateLastUpdateDate($threadId,$lastsubmitdate);
    
    abstract function deleteById($id);
    
    /**
     * @return object
     */
    abstract function getByPageId($pageId);
}
?>