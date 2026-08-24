<?php
/**
 * https://github.com/gemini-api-php/client
 */
require SOY2::rootDir().'lib/vendor/autoload.php';

use GeminiAPI\Client;
use GeminiAPI\Resources\ModelName;
use GeminiAPI\Resources\Parts\TextPart;

class GeminiApiLogic extends SOY2LogicBase {

	const DATA_SET_KEY = "gemini_api_key";

	function __construct(){
		SOY2::import("domain.cms.DataSets");
	}

	/**
	 * @param string, string
	 * @return string
	 */
	function executePrompt(string $prompt, string $apiKey=""){
		if(!strlen($apiKey)) $apiKey = self::getApiKey();
		$client = new Client($apiKey);
		try{
			$resp = $client->generativeModel("gemini-2.5-flash")->generateContent(
			    new TextPart($prompt),
			);
			return $resp->text();
		}catch(Exception $e){
			// Gemini-2.0以下のバージョンは廃止になったため、2.5-flashの実行のみにする
		}
		return "";
	}

	/**
	 * @return string
	 */
	function getApiKey(){
		return DataSets::get(self::DATA_SET_KEY, "");
	}

	/**
	 * @param string
	 */
	function saveApiKey(string $key){
		DataSets::put(self::DATA_SET_KEY, $key);
	}
}
