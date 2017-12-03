<?php
/**
 * @entity Mail
 */
abstract class MailDAO extends SOY2DAO{

	/**
	 * @return id
	 * @trigger onInsert
	 */
    abstract function insert(Mail $mail);
    
    
    /**
     * @trigger onUpdate
     */
    abstract function update(Mail $mail);
    
    abstract function get();
    
    /**
     * @return object
     */
    abstract function getById($id);
    
    abstract function delete($id);
    
    /**
     * @query status = 100
     */
    abstract function getDraftMail();
    
    /**
     * @return column_mail_count
     * @columns count(id) as mail_count
     * @query status = 100
     */
    abstract function countDraftMail();
    
    /**
     * @query status = 200 or status = 300 or status = 0
     */
    abstract function getSendMail();
    
    /**
     * @final
     */
    function getSendMailForJob(){
    	return $this->getSendMailForJobImpl(time());
    }
    
    /**
     * @return object
     * @query status = 200 and (schedule is null or schedule <= :now)
     */
    abstract function getSendMailForJobImpl($now);
    
    /**
     * @return column_mail_count
     * @columns count(id) as mail_count
     * @query status = 200 or status = 300
     */
    abstract function countSendMail();
    
    /**
     * @query status = 400
     */
    abstract function getSendedMail();
    
    /**
     * @return column_mail_count
     * @columns count(id) as mail_count
     * @query status = 400
     */
    abstract function countSendedMail();
    
    /**
	 * @final
	 */
	function onInsert($query,$binds){
		if((int)$binds[":schedule"]===0){
			$binds[":schedule"] = null;
		}
		
		$binds[":createDate"] = time();
		$binds[":updateDate"] = time();
		return array($query,$binds);
	}
    
    /**
     * 更新前に日付を入力する
     * @final
     */
    function onUpdate($query,$binds){
    	if((int)$binds[":schedule"]===0){
			$binds[":schedule"] = null;
		}
		
    	$binds[":updateDate"] = time();
    	return array($query,$binds);
    }
    
}
?>