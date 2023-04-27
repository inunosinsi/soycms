<?php
 /**
 * @entity admin.TokenLogin
 */
abstract class TokenLoginDAO extends SOY2DAO{

	/**
	 * @return id
	 */
	abstract function insert(TokenLogin $bean);

	/**
	 * @query user_id = :userId
	 */
	abstract function update(TokenLogin $bean);

	abstract function delete($id);


	/**
	 * @return object
	 */
	abstract function getByUserId(int $userId);
	/**
	 * @return object
	 */
	abstract function getByToken(string $token);

	/**
	 * @query #limit# < :time
	 */
	abstract function deleteByTime(int $time);

	abstract function get();

	abstract function deleteByUserId(int $userId);

	/**
	 * @final
	 */
	function prepare(){
		//期限切れのトークンをすべて削除
		try{
			$this->executeUpdateQUery("DELETE FROM TokenLogin WHERE time_limit <= ".time());
		}catch(Exception $e){
			//
		}
	}
}
