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
			$url = "https://graph.facebook.com/v" . $cnf["ver"] . "/" . $cnf["bizId"] . "?fields=posts.limit(" . $cnf["limit"] . "){created_time,message,full_picture}&access_token=" . $cnf["token"];
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
			foreach(array("created_time", "message") as $idx){
				$this->addLabel($idx, array(
					"soy2prefix" => "cms",
					"text" => (isset($entity[$idx]) && is_string($entity[$idx])) ? $entity[$idx] : ""
				));
			}

			$src = (isset($entity["full_picture"]) && is_string($entity["full_picture"])) ? $entity["full_picture"] : "";

			$this->addModel("is_picture", array(
				"soy2prefix" => "cms",
				"visible" => (strlen($src))
			));

			$this->addImage("picture", array(
				"soy2prefix" => "cms",
				"src" => $src
			));
		}	
	}
}
