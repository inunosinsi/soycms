<?php
/**
 * @entity SOYInquiry_Comment
 */
abstract class SOYInquiry_CommentDAO extends SOY2DAO{

    /**
	 * @return id
	 */
    abstract function insert(SOYInquiry_Comment $bean);
    
    /**
     * @order id desc
     */
    abstract function getByInquiryId($inquiryId);
}
?>