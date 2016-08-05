<?php
/**
 * @entity SOYList_Config
 */
abstract class SOYList_ConfigDAO extends SOY2DAO{

	function get(){
		$sql = "select * from soylist_config";
		$dao = new SOY2DAO();
		$res = $dao->executeQuery($sql,array());
		
		return (count($res) > 0) ? unserialize($res[0]["config"]) : new SOYList_Config();
	}
	
	function update(SOYList_Config $bean){
		$sql1 = "delete from soylist_config";
		$sql2 = "insert into soylist_config values(:config)";
		$dao = new SOY2DAO();
		$dao->executeUpdateQuery($sql1,array());
		$dao->executeUpdateQuery($sql2,array(":config" => serialize($bean)));
	}	

}
?>