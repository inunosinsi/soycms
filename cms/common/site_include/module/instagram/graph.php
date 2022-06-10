<?php
/**
 * InstagramグラフAPI
 * https://developers.facebook.com/docs/instagram-api/?locale=ja_JP
 */
function soycms_graph($html, $htmlObj){
	$obj = $htmlObj->create("soycms_graph", "HTMLTemplatePage", array(
		"arguments" => array("soycms_graph", $html)
	));

	$arr = array();
	if(file_exists(_SITE_ROOT_ . "/.plugin/instagram_graph_api.active")){
		SOY2::import("site_include.plugin.instagram_graph_api.util.InstagramGraphAPIUtil");
		$cnf = InstagramGraphAPIUtil::getConfig();
		if(strlen($cnf["token"]) && strlen($cnf["bizId"])){
			$url = "https://graph.facebook.com/v" . $cnf["ver"] . "/" . $cnf["bizId"] . "?fields=name,media.limit(" . (string)$cnf["limit"] . "){caption,media_type,media_url,thumbnail_url,permalink}&access_token=" . $cnf["token"];
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
				if(isset($json["media"]["data"]) && is_array($json["media"]["data"])){
					$arr = $json["media"]["data"];
				}
			}
		}
	}

	$obj->createAdd("data_list", "InstagramGraphAPIPostListComponent", array(
		"soy2prefix" => "p_block",
		"list" => $arr
	));

	$obj->display();
}

if(!class_exists("InstagramGraphAPIPostListComponent")){
	class InstagramGraphAPIPostListComponent extends HTMLList {
		
		protected function populateItem($entity){
			$mediaUrl = (isset($entity["media_url"]) && is_string($entity["media_url"])) ? $entity["media_url"] : "";
			$mediaType = (isset($entity["media_type"]) && is_string($entity["media_type"])) ? $entity["media_type"] : "";
	
			$this->addLabel("caption", array(
				"soy2prefix" => "cms",
				"html" => (isset($entity["caption"]) && is_string($entity["caption"])) ? nl2br(htmlspecialchars($entity["caption"], ENT_QUOTES, "UTF-8")) : ""
			));
	
			$this->addLabel("media_url_text", array(
				"soy2prefix" => "cms",
				"text" => $mediaUrl
			));
	
			$this->addModel("media_type_image", array(
				"soy2prefix" => "cms",
				"visible" => ($mediaType == "IMAGE")
			));
	
			$this->addImage("media_url_image", array(
				"soy2prefix" => "cms",
				"src" => $mediaUrl
			));
	
			$this->addModel("media_type_carousel_album", array(
				"soy2prefix" => "cms",
				"visible" => ($mediaType == "CAROUSEL_ALBUM")
			));
	
			$this->addImage("media_url_carousel_album", array(
				"soy2prefix" => "cms",
				"src" => $mediaUrl
			));
	
			$this->addImage("media_url_image", array(
				"soy2prefix" => "cms",
				"src" => $mediaUrl
			));
	
			$this->addModel("media_type_video", array(
				"soy2prefix" => "cms",
				"visible" => ($mediaType == "VIDEO")
			));
	
			$this->addModel("media_url_video", array(
				"soy2prefix" => "cms",
				"attr:src" => $mediaUrl,
			));
	
			$this->addLink("permalink", array(
				"soy2prefix" => "cms",
				"link" => (isset($entity["permalink"]) && is_string($entity["permalink"])) ? $entity["permalink"] : ""
			));
		}	
	}
}
