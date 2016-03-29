<?php
/**
 * @entity SOYMailUser
 */
abstract class SOYMailUserDAO extends SOY2DAO{
	
	/**
	 * @return id
	 * @trigger onInsert
	 */
    abstract function insert(SOYMailUser $mail);
    
    /**
     * @trigger onUpdate
     */
    abstract function update(SOYMailUser $mail);
    
    abstract function get();
    
    /**
     * @return object
     */
    abstract function getById($id);
    
    abstract function delete($id);
    
    /**
     * @return column_id
     * @columns id
     * @query mail_address = :email
     */
    abstract function getIdByEmail($email);

	/**
	 * @return column_count_user
	 * @columns count(id) as count_user
	 * @query is_disabled != 1
	 */
	abstract function countUser();
	
	/**
	 * @final
	 */
	function onInsert($query,$binds){
		if($binds[":birthday"]===false){
			$binds[":birthday"] = null;
		}
		
		if((int)$binds[":area"]===0){
			$binds[":area"] = null;
		}
		
		if((int)$binds[":jobArea"]===0){
			$binds[":jobArea"] = null;
		}
		
		$binds[":registerDate"] = time();
		$binds[":updateDate"] = time();
		
		return array($query,$binds);
	}
	
	/**
	 * @final
	 */
	function onUpdate($query,$binds){
		if($binds[":birthday"]===false){
			$binds[":birthday"] = null;
		}
		
		if((int)$binds[":area"]===0){
			$binds[":area"] = null;
		}
		
		if((int)$binds[":jobArea"]===0){
			$binds[":jobArea"] = null;
		}
		
		$binds[":updateDate"] = time();
		
		return array($query,$binds);
	}
}
?>
