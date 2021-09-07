<?php
class AutoCompletionOnOutput extends SOYShopSiteOnOutputAction{

	/**
	 * @return string
	 */
	function onOutput($html){
		//JSのコードを追加すべきページか？調べる
		$uri = $_SERVER["REQUEST_URI"];
		if(is_numeric(strpos($uri, "?"))) $uri = substr($uri, 0, strpos($uri, "?"));
		$hash = substr(md5($uri), 0, 6);

		if(file_exists(self::_dir() . $hash . ".log")){
			$arr = self::_loadSearchResult($hash);
		}else{
			$arr = array("insert" => false, "jquery" => false, "jquery-ui-js" => false, "jquery-ui-css" => false);
			//HTMLにid="auto_completion"が含まれているか？調べる
			$lines = explode("\n", $html);
			if(count($lines)){
				foreach($lines as $line){
					$line = trim($line);
					if(!strlen($line)) continue;
					if(!$arr["insert"] && is_numeric(stripos($line, "id=\"auto_completion\""))){
						$arr["insert"] = true;
						continue;
					}

					//要検討
					if(!$arr["jquery"] && is_numeric(strpos($line, "jquery.min.js")) || is_numeric(strpos($line, "jquery.js"))){
						$arr["jquery"] = true;
						continue;
					}

					foreach(array("js", "css") as $t){
						if(!$arr["jquery-ui-" . $t] && is_numeric(strpos($line, "jquery-ui.min." . $t)) || is_numeric(strpos($line, "jquery-ui." . $t))){
							$arr["jquery-ui-" . $t] = true;
							continue;
						}
					}

				}
			}
			unset($lines);
			self::_saveSearchResult($hash, $arr);
		}

		if(!$arr["insert"]) return $html;

		$code = "\n<input type=\"hidden\" id=\"auto_completion_url\" value=\"" . soyshop_get_mypage_url() . "?soyshop_action=auto_completion_item_name\">";

		if(!$arr["jquery"]) $code .= "\n<script src=\"https://code.jquery.com/jquery-3.6.0.min.js\" integrity=\"sha256-/xUj+3OJU5yExlq6GSYGSHk7tPXikynS7ogEvDej/m4=\" crossorigin=\"anonymous\"></script>";
		if(!$arr["jquery-ui-js"]) $code .= "\n<script src=\"https://code.jquery.com/ui/1.12.1/jquery-ui.min.js\" integrity=\"sha256-VazP97ZCwtekAsvgPBSUwPFKdrwD3unUfSGVYrahUqU=\" crossorigin=\"anonymous\"></script>";
		if(!$arr["jquery-ui-css"]) $code .= "\n<link rel=\"stylesheet\" href=\"https://ajax.googleapis.com/ajax/libs/jqueryui/1.12.1/themes/smoothness/jquery-ui.css\">";

		$code .= "\n<script>" . file_get_contents(dirname(__FILE__) . "/js/completion.js") . "</script>";
		if(is_numeric(stripos($html,'</body>'))){
			return str_ireplace('</body>', $code."\n</body>", $html);
		}else if(preg_match('/</body\\s[^>]+>/',$html)){
			return preg_replace('/(</body\\s[^>]+>)/',$code."\n\$0",$html);
		}else{
			return $html.$code;
		}
	}

	private function _loadSearchResult(string $hash){
		$arr = explode(",", file_get_contents(self::_dir() . $hash . ".log"));
		$v = array();
		foreach(array("insert", "jquery", "jquery-ui-js", "jquery-ui-css") as $idx => $alias){
			$v[$alias] = (isset($arr[$idx]) && is_numeric($arr[$idx]) && $arr[$idx] == 1);
		}
		return $v;
	}

	private function _saveSearchResult(string $hash, array $arr){
		$v = "";
		foreach(array("insert", "jquery", "jquery-ui-js", "jquery-ui-css") as $t){
			$v .= (isset($arr[$t]) && $arr[$t] == true) ? "1" : "0";
			$v .= ",";
		}
		file_put_contents(self::_dir() . $hash . ".log", rtrim($v, ","));
	}

	private function _dir(){
		$dir = SOYSHOP_SITE_DIRECTORY . ".cache/autocompletion/";
		if(!file_exists($dir)) mkdir($dir);
		return $dir;
	}
}

SOYShopPlugin::extension("soyshop.site.onoutput", "auto_completion_item_name", "AutoCompletionOnOutput");
