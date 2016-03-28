<?php

class ASPSOY2DAO extends SOY2DAO{

    function &getDataSource(){
    	return ASPSOY2DAO::_getDataSource();
    }
    
    public static function &_getDataSource(){
    	static $pdo;
		
		if(is_null($pdo)){
			
			try{
				$pdo = new PDO(SOYCMS_ASP_DSN,SOYCMS_ASP_USER,SOYCMS_ASP_PASS,array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION));
			} catch (PDOException $e) {
				die("Can not get DataSource.");
			}
		}
		return $pdo;
    }
}
?>