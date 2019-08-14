<?php
/**
 * @entity SOYInquiry_Comment
 */
abstract class SOYInquiry_CommentDAO extends SOY2DAO{

    /**
	 * @return id
	 * @trigger onInsert
	 */
    abstract function insert(SOYInquiry_Comment $bean);

    /**
     * @order id desc
     */
    abstract function getByInquiryId($inquiryId);

	/**
	 * @final
	 */
	function onInsert($query, $binds){
		static $i;
		if(is_null($i)) $i = 0;

		for(;;){
			$i++;
			try{
				$res = $this->executeQuery("SELECT id FROM soyinquiry_comment WHERE inquiry_id = :inquiryId AND create_date = :createDate LIMIT 1;", array(":inquiryId" => $binds[":inquiryId"], ":createDate" => $binds[":createDate"] + $i));
			}catch(Exception $e){
				$res = array();
			}

			if(!count($res)) break;
		}
		$binds[":createDate"] += $i;

		return array($query, $binds);
	}
}
