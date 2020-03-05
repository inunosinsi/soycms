<?php

class PingUtil {

	public static function checkSended($entryId){
		SOY2::import("site_include.plugin.ping_new.domain.PingDAO");
		try{
			$obj = SOY2DAOFactory::create("PingDAO")->getByEntryId($entryId);
			return (strlen($obj->getSendDate()));
		}catch(Exception $e){
			return false;
		}
	}

	public static function save($entryId){
		$dao = SOY2DAOFactory::create("PingDAO");
		$obj = new Ping();
		$obj->setEntryId($entryId);

		try{
			$dao->insert($obj);
		}catch(Exception $e){
			//
		}
	}
}
