<?php

class AppSOY2DAO extends SOY2DAO{

    function &getDataSource(){
    	return AppSOY2DAO::_getDataSource();
    }

    public static function &_getDataSource(){
    	static $pdo;

		if(is_null($pdo)){

			try{
				$pdo = new PDO(SOYCMS_APP_DSN,SOYCMS_APP_USER,SOYCMS_APP_PASS,array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION));
			} catch (PDOException $e) {
				die("Can not get DataSource.");
			}
		}
		return $pdo;
    }
}
?>