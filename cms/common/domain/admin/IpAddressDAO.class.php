<?php
/**
 * @entity admin.IpAddress
 */
abstract class IpAddressDAO extends SOY2DAO {

	abstract function insert(IpAddress $bean);

	abstract function update(IpAddress $bean);

	/**
	 * @return object
	 */
	abstract function getById(int $id);	

	abstract function deleteById(int $id);

	/**
	 * @final
	 */
	function getByV4(string $v){
		$ip = new IpAddress();

		try{
			$res = $this->executeQuery(
				"SELECT * FROM IpAddress WHERE v4 = :ip",
				array(":ip" => $ip->convertIpv4ToInt($v))
			);
		}catch(Exception $e){
			$res = array();
		}

		if(isset($res[0])){
			return $this->getObject($res[0]);
		}else{
			$ip->setIpv4($v);
			return $ip;
		}
	}

	function getSuspiciousIpAddressList(){
		try{
			$res = $this->executeQuery(
				"SELECT * FROM IpAddress ".
				"WHERE is_suspicious = 1"
			);
		}catch(Exception $e){
			$res = array();
		}
		if(!count($res)) return array();

		foreach($res as $idx => $v){
			$res[$idx]["v4"] = long2ip($v["v4"]);
		}

		return $res;
	}

	function rotateLogs(){
		try{
			$this->executeQuery(
				"DELETE FROM IpAddress ".
				"WHERE id NOT IN (".
					"SELECT ipAddressId FROM NotFoundLog".
				") ".
				"AND is_suspicious = 0"
			);
		}catch(Exception $e){
			//
		}
	}
}
