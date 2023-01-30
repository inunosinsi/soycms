<?php
SOY2DAOFactory::importEntity("SOYInquiry_ServerConfig");

class SOYInquiry_ServerConfigDAO{
	
	function get(){
		$sql = "select * from soyinquiry_serverconfig";
		$dao = new SOY2DAO();
		$res = $dao->executeQuery($sql,array());
		
		return (count($res) > 0) ? unserialize($res[0]["config"]) : new SOYInquiry_ServerConfig();
	}
	
	function update(SOYInquiry_ServerConfig $bean){
		$sql1 = "delete from soyinquiry_serverconfig";
		$sql2 = "insert into soyinquiry_serverconfig values(:config)";
		$dao = new SOY2DAO();
		$dao->executeUpdateQuery($sql1,array());
		$dao->executeUpdateQuery($sql2,array(":config" => serialize($bean)));
	}
}
