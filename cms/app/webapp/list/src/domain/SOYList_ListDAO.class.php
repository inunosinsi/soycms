<?php
/**
 * @entity SOYList_List
 */
abstract class SOYList_ListDAO extends SOY2DAO{

	function get(){
		$sql = "select * from soylist_list";
		$dao = new SOY2DAO();
		$res = $dao->executeQuery($sql,array());
		
		return (count($res) > 0) ? unserialize($res[0]["config"]) : new SOYList_List();
	}
	
	function update(SOYList_List $bean){
		$sql1 = "delete from soylist_list";
		$sql2 = "insert into soylist_list values(:config)";
		$dao = new SOY2DAO();
		$dao->executeUpdateQuery($sql1,array());
		$dao->executeUpdateQuery($sql2,array(":config" => serialize($bean)));
	}	

}
?>