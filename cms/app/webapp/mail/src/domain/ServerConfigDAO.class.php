<?php
SOY2DAOFactory::importEntity("ServerConfig");

class ServerConfigDAO extends SOY2DAO{
	
	/**
	 * @final
	 */
	function get(){
		$sql = "select * from soymail_serverconfig";
		$res = $this->executeQuery($sql,array());
		
		return (count($res) > 0) ? unserialize($res[0]["config"]) : new ServerConfig();
	}
	
	/**
	 * @final
	 */
	function update(ServerConfig $bean){
		$sql1 = "delete from soymail_serverconfig";
		$sql2 = "insert into soymail_serverconfig values(:config)";
		$this->executeUpdateQuery($sql1,array());
		$this->executeUpdateQuery($sql2,array(":config" => serialize($bean)));
	}
	
	/**
	 * @final
	 */
	function setJobIsActived($value){
		$config = $this->get();
		$config->setJobIsActived($value);
		$this->update($config);
	}
	
	/**
	 * @final
	 */
	function setJobNextExecuteTime($value){
		$config = $this->get();
		$config->setJobNextExecuteTime($value);
		$this->update($config);
	} 
    
}
?>