<?php
/**
 * @entity SOYMail_SOYShopConnector
 */
abstract class SOYMail_SOYShopConnectorDAO extends SOY2DAO{

	function get(){
		$sql = "select * from soymail_soyshop_connector";
		$dao = new SOY2DAO();
		$res = $dao->executeQuery($sql,array());
		
		return (count($res) > 0) ? unserialize($res[0]["config"]) : new SOYMail_SOYShopConnector();
	}
	
	function update(SOYMail_SOYShopConnector $bean){
		$sql1 = "delete from soymail_soyshop_connector";
		$sql2 = "insert into soymail_soyshop_connector values(:config)";
		$dao = new SOY2DAO();
		$dao->executeUpdateQuery($sql1,array());
		$dao->executeUpdateQuery($sql2,array(":config" => serialize($bean)));
	}	

}
?>