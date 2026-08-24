<?php
/**
 * @entity admin.RequestUri
 */
abstract class RequestUriDAO extends SOY2DAO {

	/**
	 * @return id
	 */
	abstract function insert(RequestUri $bean);

	/**
	 * @return object
	 */
	abstract function getById(int $id);	

	abstract function deleteById(int $id);

	/**
	 * @final
	 */
	function getByUri(string $uri){
		$req = new RequestUri();
		$hash = $req->createHash($uri);
		
		try{
			$res = $this->executeQuery(
				"SELECT * FROM RequestUri WHERE hash = :hash",
				array(":hash" => $hash)
			);
		}catch(Exception $e){
			$res = array();
		}

		if(isset($res[0])){
			return $this->getObject($res[0]);
		}

		$req->setUri($uri);
		$req->setHash($hash);
		return $req;
	}

	function rotateLogs(){
		try{
			$this->executeQuery(
				"DELETE FROM RequestUri ".
				"WHERE id NOT IN (".
					"SELECT requestUriId FROM NotFoundLog".
				")"
			);
		}catch(Exception $e){
			//
		}
	}
}
