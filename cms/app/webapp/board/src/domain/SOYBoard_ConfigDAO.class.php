<?php
/**
 * @entity SOYBoard_Config
 */
abstract class SOYBoard_ConfigDAO extends SOY2DAO{

    /**
	 * @return thread_id
	 */
    abstract function insert(SOYBoard_Config $bean);
    
    abstract function update(SOYBoard_Config $bean);
    
    /**
     * @return object
     */
   	abstract function getByThreadId($threadId);
   	
   	/**
   	 * @query thread_id = :threadId
   	 */
   	abstract function deleteByThreadId($threadId);
    
    /**
     * @query thread_id = :threadId
     */
    abstract function updateByThreadId($threadId, SOYBoard_Config $bean);
}
?>