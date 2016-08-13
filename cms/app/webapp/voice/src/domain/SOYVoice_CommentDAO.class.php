<?php
/**
 * @entity SOYVoice_Comment
 */
abstract class SOYVoice_CommentDAO extends SOY2DAO{

   	/**
	 * @return id
	 */
	abstract function insert(SOYVoice_Comment $bean);
	
	abstract function update(SOYVoice_Comment $bean);
	
	/**
	 * @return list
	 * @order update_date desc
	 */
	abstract function get();
	
	/**
	 * @return list
	 * @query user_type = 1
	 * @order update_date desc
	 */
	abstract function getByUserType();
	
	/**
	 * @return list
	 * @query is_published = 1
	 * @order comment_date desc
	 */
	abstract function getCommentIsPublished();
	
	/**
	 * @return list
	 * @query is_entry = 0
	 * @order update_date desc
	 */
	abstract function getCommentNoEntry();
	
	/**
	 * @return object
	 */
	abstract function getById($id);

	/**
	 * @return column_count
	 * @columns count(id) as count
	 */
	abstract function count();
	
	abstract function deleteById($id);
}
?>