<?php
if(!defined("SOYCMS_ACCESS_LOG_CONFIG_FILEPATH")) define("SOYCMS_ACCESS_LOG_CONFIG_FILEPATH", SOY2::rootDir()."config/log/accesslog.json");

class AccessLogUtil {
	const ON = 1;

	public static function getConfig(){
		static $cfg;
		if(is_null($cfg)){
			if(file_exists(SOYCMS_ACCESS_LOG_CONFIG_FILEPATH)){
				$cfg = json_decode(file_get_contents(SOYCMS_ACCESS_LOG_CONFIG_FILEPATH), true);
			}else{
				$cfg = array(
					"on" => 0,
					"count" => 1000
				);
			}
		}
		return $cfg;	
	}

	/**
	 * @return string
	 */
	public static function getLogDir(){
		return dirname(SOYCMS_ACCESS_LOG_CONFIG_FILEPATH)."/";
	}

	/**
	 * @return bool
	 */
	public static function checkPermission(){
		return is_writable(self::getLogDir());
	}

	public static function saveConfig(array $cfg){
		file_put_contents(SOYCMS_ACCESS_LOG_CONFIG_FILEPATH, json_encode($cfg));
	}

	/**
	 * アクセスログの収集を開始しているか？
	 * @return bool
	 */
	public static function isLoggingOn(){
		$cfg = self::getConfig();
		return (isset($cfg["on"]) && (int)$cfg["on"] === self::ON);
	}

	public static function getLogCount(){
		$cfg = self::getConfig();
		return (isset($cfg["count"]) && is_numeric($cfg["count"])) ? (int)$cfg["count"] : 1000;
	}
}
