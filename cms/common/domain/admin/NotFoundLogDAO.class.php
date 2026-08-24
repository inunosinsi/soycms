<?php
/**
 * @entity admin.NotFoundLog
 */
abstract class NotFoundLogDAO extends SOY2DAO {

	/**
	 * @trigger onInsert
	 */
	abstract function insert(NotFoundLog $bean);	

	/**
	 * @final
	 */
	function onInsert(SOY2DAO_Query $query, array $binds){
		$binds[":cdate"] = time();
		return array($query, $binds);
	}

	/**
	 * @final
	 */
	function get(){
		try{
			$res = $this->executeQuery(
				"SELECT log.cdate, ip.v4, req.uri, ip.is_suspicious  FROM NotFoundLog log ".
				"INNER JOIN IpAddress ip ".
				"ON log.ipAddressId = ip.id ".
				"INNER JOIN RequestUri req ".
				"ON log.requestUriId = req.id ".
				"ORDER BY log.cdate DESC ".
				"LIMIT 500"
			);
		}catch(Exception $e){
			$res = array();
		}

		if(!count($res)) return array();

		foreach($res as $idx => $v){
			$ipv4 = long2ip($v["v4"]);
			// if($ipv4 == $_SERVER["REMOTE_ADDR"]) {
				// unset($res[$idx]);
				// continue;
			// }
			
			$res[$idx]["v4"] = $ipv4;
		}

		return $res;
	}

	/**
	 *
	 */
	function rotateLogs(){
		SOY2::import("util.AccessLogUtil");
		$logCnt = AccessLogUtil::getLogCount();

		//現在のログの件数を確認する
		try{
			$res = $this->executeQuery(
				"SELECT cdate FROM NotFoundLog ORDER BY cdate DESC LIMIT ".($logCnt+1)
			);
		}catch(Exception $e){
			return true;
		}

		if(!isset($res[0]["cdate"]) || count($res) < $logCnt || !isset($res[$logCnt])) return true;
		// ローテーションを行う

		try{
			$this->executeUpdateQuery(
				"DELETE FROM NotFoundLog WHERE cdate <= :cdate",
				array(":cdate" => $res[$logCnt]["cdate"])
			);
		}catch(Exception $e){
			//
		}
		return true;
	}
}
