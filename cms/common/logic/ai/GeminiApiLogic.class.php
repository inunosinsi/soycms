<?php
/**
 * https://github.com/gemini-api-php/client
 */
require SOY2::rootDir().'lib/vendor/autoload.php';

use GeminiAPI\Client;
use GeminiAPI\Resources\Parts\TextPart;

class GeminiApiLogic extends SOY2LogicBase {

	const DATA_SET_KEY = "gemini_api_key";

	function __construct(){
		SOY2::import("domain.cms.DataSets");
	}

	function executePrompt(string $prompt){
		$client = new Client(self::getApiKey());
		try{
			$resp = $client->geminiPro15()->generateContent(
				new TextPart($prompt)
			);
			return $resp->text();
		}catch(Exception $e){
			try{
				$resp = $client->geminiPro()->generateContent(
					new TextPart($prompt)
				);
				return $resp->text();
			}catch(Exception $e){
				//	
			}
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