<?php
/**
 * @entity ErrorMail
 */
abstract class ErrorMailDAO extends SOY2DAO{

    /**
	 * @return id
	 */
    abstract function insert(ErrorMail $mail);
    
    abstract function update(ErrorMail $mail);
    
    abstract function get();
    
    /**
     * @return object
     */
    abstract function getById($id);
    
    abstract function delete($id);
    
    /**
     * @return column_mail_count
     * @columns count(id) as mail_count
     */
    abstract function getErrorMailCountByMailId($mailId);
    
    /**
     * @return column_mail_count
     * @columns count(id) as mail_count
     */
    abstract function countErrorMail();
}
?>