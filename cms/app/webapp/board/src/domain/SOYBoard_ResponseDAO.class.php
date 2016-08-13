<?php
/**
 * @entity SOYBoard_Response
 */
abstract class SOYBoard_ResponseDAO extends SOY2DAO{

    /**
	 * @return id
	 */
    abstract function insert(SOYBoard_Response $bean);
    
    abstract function update(SOYBoard_Response $bean);
    
    abstract function get();
    
    /**
     * @sql SELECT count(id) as count FROM soyboard_response WHERE thread_id = :threadId
     * @return row
     */
    abstract function getResponseNum($threadId);
    
    /**
     * @sql SELECT MAX(response_id) AS last FROM soyboard_response WHERE thread_id = :threadId
     * @return row
     */    
    abstract function getLastResponseId($threadId);
    
   /**
     * @query thread_id = :threadId AND ID >= :offset AND ID < (:offset + :viewcount)
     */
    abstract function getByThreadId($threadId,$offset,$viewcount);
    
    /**
     * @query thread_id = :threadId AND id = :responseId
     */
    abstract function delete($threadId,$responseId);
 
 	/**
 	 * @query thread_id = :threadId
 	 */
 	abstract function deleteByThreadId($threadId);   
}
?>