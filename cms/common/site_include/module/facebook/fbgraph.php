<?php
/**
 * FacebookグラフAPI
 * https://developers.facebook.com/docs/graph-api
 */
function soycms_fbgraph($html, $htmlObj){
	$obj = $htmlObj->create("soycms_fbgraph", "HTMLTemplatePage", array(
		"arguments" => array("soycms_fbgraph", $html)
	));

	$arr = array();
	if(CMSPlugin::activeCheck("facebook_graph_api")){
		SOY2::import("site_include.plugin.facebook_graph_api.util.FbGraphAPIUtil");
		$cnf = FbGraphAPIUtil::getConfig();
		if(strlen($cnf["token"]) && strlen($cnf["bizId"])){
			$url = "https://graph.facebook.com/v" . $cnf["ver"] . "/" . $cnf["bizId"] . "?fields=posts.limit(" . $cnf["limit"] . "){created_time,message,full_picture,permalink_url}&access_token=" . $cnf["token"];
			if(function_exists("curl_init")){
				$ch = curl_init();
				curl_setopt($ch, CURLOPT_URL, $url);
				curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
				$resp = curl_exec($ch);
				curl_close($ch);
			}else{
				$resp = @file_get_contents($url);
			}

			if(is_string($resp)){
				$json = json_decode($resp, true);
				if(isset($json["posts"]["data"]) && is_array($json["posts"]["data"])){
					$arr = $json["posts"]["data"];

					if(is_numeric($cnf["limit"]) && count($arr) > $cnf["limit"]){
						$arr = array_slice($arr, 0, $cnf["limit"]);
					}
				}
			}
		}
	}

	$obj->createAdd("data_list", "FacebookGraphAPIPostListComponent", array(
		"soy2prefix" => "p_block",
		"list" => $arr
	));

	$obj->display();
}

if(!class_exists("FacebookGraphAPIPostListComponent")){
	class FacebookGraphAPIPostListComponent extends HTMLList {
		
		protected function populateItem($entity){
			$createdTime = (isset($entity["created_time"]) && is_string($entity["created_time"])) ? $entity["created_time"] : "";
			$this->addLabel("created_time", array(
				"soy2prefix" => "cms",
				"text" => $createdTime
			));

			$this->createAdd("create_date", "DateLabel", array(
				"soy2prefix" => "cms",
				"text" => (strlen($createdTime)) ? self::_str2timestamp($createdTime) : 0
			));

			$this->addLabel("message", array(
				"soy2prefix" => "cms",
				"html" => (isset($entity["message"]) && is_string($entity["message"])) ? self::_convertMessage($entity["message"]) : ""
			));

			$src = (isset($entity["full_picture"]) && is_string($entity["full_picture"])) ? $entity["full_picture"] : "";

			$this->addModel("is_picture", array(
				"soy2prefix" => "cms",
				"visible" => (strlen($src))
			));

			$this->addImage("picture", array(
				"soy2prefix" => "cms",
				"src" => $src
			));

			$this->addLink("permalink", array(
				"soy2prefix" => "cms",
				"link" => (isset($entity["permalink_url"]) && is_string($entity["permalink_url"])) ? $entity["permalink_url"] : ""
			));
		}

		/**
		 * @param string
		 * @return int<timestamp>
		 */
		private function _str2timestamp(string $str){
			$arr = explode("T", $str);
			if(count($arr) != 2) return 0;
			$dateArr = explode("-", $arr[0]);
			$timeArr = explode(":", $arr[1]);
			$min = substr($timeArr[2], 0, 2);
			
			return mktime((int)$timeArr[0], (int)$timeArr[1], (int)$min, (int)$dateArr[1], (int)$dateArr[2], (int)$dateArr[0]);
		}

		/**
		 * ハッシュタグ等の変換
		 * @param string
		 * @return string
		 */
		private function _convertMessage(string $msg){
			preg_match_all('(https?://[-_.!~*\'()a-zA-Z0-9;/?:@&=+$,%#]+)', $msg, $tmps);
			if(isset($tmps[0]) && is_array($tmps[0]) && count($tmps[0])){
				foreach($tmps[0] as $url){
					$cnv = "<a href=\"" . $url . "\" target=\"_blank\" rel=\"noopener\">" . $url . "</a>";
					$msg = str_replace($url, $cnv, $msg);
				}
			}

			
			// ハッシュタグの変換
			if(is_numeric(strpos($msg, "#"))){
				// #ハッシュタグの変換 半角スペースまでで切る
				$tmp = str_replace(array("　", "。", "」"), " ", $msg);
				$lines = explode(" ", $tmp);
				foreach($lines as $l){
					$l = trim($l);
					$res = strpos($l, "#");
					if(is_bool($res)) continue;
					if($res > 0){	//文章の途中にタグの記述がある場合
						$tag = substr($l, $res);
					}else{
						$tag = $l;
					}
					$tag = htmlspecialchars($tag, ENT_QUOTES, "UTF-8");
					$txt = str_replace("#", "", $tag);
					$cnv = "<a href=\"https://www.facebook.com/hashtag/" . urlencode($txt) . "\" target=\"_blank\" rel=\"noopener\">" . $tag . "</a>";
					$msg = str_replace($tag, $cnv, $msg);
				}
			}

			return $msg;
		}
	}
}
