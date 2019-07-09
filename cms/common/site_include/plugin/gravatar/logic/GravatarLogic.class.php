<?php

class GravatarLogic extends SOY2LogicBase{

	const GRAVATAR_URL = "https://www.gravatar.com/";

	function __construct(){
		SOY2::imports("site_include.plugin.gravatar.domain.*");
	}

	function getGravatars(){
		try{
			return self::dao()->get();
		}catch(Exception $e){
			return array();
		}
	}

	function getGravatarByMailAddress($mailAddress){
		try{
			return self::dao()->getByMailAddress($mailAddress);
		}catch(Exception $e){
			return new GravatarAccount();
		}
	}

	function getGravatarByAlias($alias){
		//@ToDo 後にエイリアスのカラムを持つかもしれない
		try{
			return self::dao()->getByName($alias);
		}catch(Exception $e){
			return new GravatarAccount();
		}
	}

	function getGravatarValuesByAccount(GravatarAccount $account){
		if(!strlen($account->getMailAddress())) return array();

		//キャッシュを調べる
		if(self::isCacheFile($account->getId())) return self::readCacheFile($account->getId());

		$values = array();

		$hash = self::createHashByMailAddress($account->getMailAddress());
		$str = @file_get_contents(self::GRAVATAR_URL . $hash . ".xml");
		if($str !== false){
			$xml = simplexml_load_string($str);
			$entry = $xml->entry;

			//nameとreadingは日本語対応に合わせて逆にする
			$values["alias"] = $account->getName();
			$values["name"] = (string)$entry->name->familyName;
			$values["reading"] = (string)$entry->name->givenName;
			$values["fullname"] = (string)$entry->name->formatted;
			$values["displayname"] = (string)$entry->displayName;
			$values["profileUrl"] = (string)$entry->profileUrl;
			$values["aboutMe"] = (string)$entry->aboutMe;
			$values["thumbnailUrl"] = (string)$entry->thumbnailUrl;

			//個々のデータもキャッシュ化
			self::generateCacheFile($values, $account->getId());
		}

		return $values;
	}

	private function createHashByMailAddress($mailAddress){
		return md5( strtolower( trim( $mailAddress ) ) );
	}

	function isCacheFile($fileName="profile"){
		return (file_exists(self::getCacheFilePath($fileName)));
	}

	function readCacheFile($fileName="profile"){
		return soy2_unserialize(file_get_contents(self::getCacheFilePath($fileName)));
	}

	function generateCacheFile($values, $fileName="profile"){
		file_put_contents(self::getCacheFilePath($fileName), soy2_serialize($values));
	}

	function removeCacheFile($fileName="profile"){
		if(self::isCacheFile($fileName)){
			unlink(self::getCacheFilePath($fileName));
		}

		//全員分削除
		$accounts = self::getGravatars();
		if(!count($accounts)) return;

		foreach($accounts as $account){
			if(self::isCacheFile($account->getId())){
				unlink(self::getCacheFilePath($account->getId()));
			}
		}
	}

	private function getCacheFilePath($fileName="profile"){
		$cacheDir = self::getSiteDirectory() . ".cache/gravatar/";
		if(!file_exists($cacheDir)) mkdir($cacheDir);
		return $cacheDir . $fileName . ".txt";
	}

	private function getSiteDirectory(){
		if(defined("_SITE_ROOT_")){
			return _SITE_ROOT_ . "/";
		}else{
			return UserInfoUtil::getSiteDirectory();
		}
	}


	private function dao(){
		static $dao;
		if(is_null($dao)) $dao = SOY2DAOFactory::create("GravatarAccountDAO");
		return $dao;
	}
}
