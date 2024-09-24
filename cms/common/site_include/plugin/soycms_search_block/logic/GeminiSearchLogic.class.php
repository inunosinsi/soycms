<?php
SOY2::import("logic.ai.GeminiApiLogic");

class GeminiSearchLogic extends GeminiApiLogic {

	const DETA_SET_PROMPT = "gemini_api_prompt_format";

	/**
	 * @param string
	 * @return array
	 */
	function getRelativeQueries(string $query){
		$prompt = self::getPromptFormat();
		if(soy2_strpos($prompt, "##QUERY##")) $prompt = str_replace("##QUERY##", $query, $prompt);
		
		$result = (strlen($prompt)) ? parent::executePrompt($prompt) : "";
		if(!strlen($result)) return array($query);
		
		$lines = explode("\n", $result);
		if(!count($lines)) return array($query);
		
		$_tmp = array($query);

		foreach($lines as $line){
			if(soy2_strpos($line, "*") !== 0) continue;
			$line = trim(substr($line, 1));
			$_tmp[] = $line;
		}
		
		return $_tmp;
	}

	/**
	 * @return string
	 */
	function getPromptFormat(){
		return DataSets::get(self::DETA_SET_PROMPT, self::_buildPromptFormat());
	}

	/**
	 * @param string
	 */
	function savePromptFormat(string $prompt){
		DataSets::put(self::DETA_SET_PROMPT, $prompt);
	}

	function buildPromptExample(){
		return self::_buildPromptFormat();
	}

	private function _buildPromptFormat(){
		return self::_buildPrompt("##QUERY##");
	}

	/**
	 * @param string
	 * @return string
	 */
	private function _buildPrompt(string $query){
		$url = self::_buildSiteUrl();
		$prompt = "";
		if(strlen($url)) $prompt = $url."で使用されている用語で";
		return $prompt."「".$query."」の類似のキーワードを挙げてください";
	}

	/**
	 * @param string
	 * @return string
	 */
	private function _buildSiteUrl(){
		$domain = $_SERVER["HTTP_HOST"];
		if(preg_match('/^localhost/', $domain) === 1) return "";

		$protocol = (isset($_SERVER["HTTPS"]) && $_SERVER["HTTPS"] == "on") ? "https" : "http";

		return $protocol."://".$domain."/";
	}
}