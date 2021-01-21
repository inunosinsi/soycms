<?php

class ZeroSaitodevPlugin{

	const PLUGIN_ID = "0_saitodev";

	function init(){
		CMSPlugin::addPluginMenu(self::PLUGIN_ID, array(
			"name" => "SOY CMS新機能紹介プラグイン",
			"description" => "https://saitodev.coで投稿されたSOY CMS全般の新着記事を取得する。PHPでXMLが使用できない環境では当プラグインは使用できません。",
			"author" => "齋藤毅",
			"url" => "https://saitodev.co/",
			"mail" => "tsuyoshi@saitodev.co",
			"version"=>"1.1"
		));

		//二回目以降の動作
		if(CMSPlugin::activeCheck(self::PLUGIN_ID)){

			//管理側
			if(!defined("_SITE_ROOT_")){
				CMSPlugin::setEvent("onAdminTop", self::PLUGIN_ID, array($this, "onAdminTop"));
			}

        //プラグインの初回動作
		}
	}

	function onAdminTop(){
		$title = "SOY CMSの新機能紹介 (開発者のサイト)";
		$content = "usa";
		if(!function_exists("simplexml_load_string") || !ini_get("allow_url_fopen")){
			SOY2ActionFactory::createInstance("Plugin.ToggleActiveAction", array("pluginId" => self::PLUGIN_ID))->run();
			SOY2PageController::jump("");
		}

		$cacheDir = UserInfoUtil::getSiteDirectory() . ".cache/xml/";
		if(!file_exists($cacheDir)) mkdir($cacheDir);

		$cacheFile = $cacheDir . "soycms.xml";
		if(file_exists($cacheFile) && self::checkCacheOld($cacheFile)){
			$contents = file_get_contents($cacheFile);
		}else{
			$contents = @file_get_contents("https://saitodev.co/soycms.xml", false, $ctx = stream_context_create(array(
				'http' => array(
					'timeout' => 3
				)
			)));
		}

		$html = array();
		if(isset($contents) && strlen($contents)){
			//値をキャッシュに保持する
			file_put_contents($cacheFile, $contents);

			$xml = @simplexml_load_string($contents);
			if(!is_null($xml) && !is_bool($xml) && property_exists($xml, "entries")){
				$entries = $xml->entries;
				if(property_exists($entries, "entry") && count($entries->entry)){
					$html[] = "<div class=\"alert alert-info\" style=\"margin:5px 20px;\">下記で紹介している機能を使用する場合はSOY CMSのバージョンアップを行って下さい。最新版のダウンロードは<a href=\"https://saitodev.co/soycms\" target=\"_blank\" style=\"text-decoration:underline;\">こちら</a>から</div>";
					$html[] = "<ul class=\"soycms_news\">";
					$entCnt = count($entries->entry);
					for($i = 0; $i < $entCnt; ++$i){
						$entry = $entries->entry[$i];
						$html[] = "<li>" . $entry->create_date . "&nbsp;&nbsp;&nbsp;<a href=\"" . $entry->url . "\" target=\"_blank\" rel=\"noopener\">" . $entry->entry_title . "</a></li>";
					}
					$html[] = "</ul>";
				}
			}
		}
		if(!count($html)){
			$html[] = "<div class=\"soycms_news\">SOY CMSの新着情報の取得を失敗しました。</div>";
		}

		//記事一覧へのリンク
		$html[] = "<div class=\"soycms_news text-center\">";
		$html[] = "<a href=\"https://saitodev.co/category/SOY_CMS\" target=\"_blank\" rel=\"noopener\" class=\"btn btn-info\">SOY CMSの記事をもっと読む</a>";
		$html[] = "&nbsp;&nbsp;";
		$html[] = "<a href=\"https://saitodev.co/app/bulletin/board/topic/1\" target=\"_blank\" rel=\"noopener\" class=\"btn btn-warning\">SOY CMSの掲示板を開く</a>";
		$html[] = "</div>";
		$html[] = "<style>" . file_get_contents(dirname(__FILE__) . "/css/style.css") . "</style>";

		return array("title" => $title, "content" => implode("\n", $html));
	}

	private function checkCacheOld($f){
		//キャッシュファイルの作成日のタイムスタンプをY-m-d 00:00:00の値のもので取得する
		$d = explode("-", date("Y-n-j",filectime($f)));
		$ftimestamp = mktime(0, 0, 0, $d[1], $d[2], $d[0]);

		//今の一日前よりも古い場合はキャッシュを削除 falseで返す
		return ($ftimestamp > time() - 24 * 60 * 60);
	}

	public static function register(){
		$obj = CMSPlugin::loadPluginConfig(self::PLUGIN_ID);
		if(is_null($obj)){
			$obj = new ZeroSaitodevPlugin();

			//この時プラグインを強制的に有効にする
			$filepath = CMSPlugin::getSiteDirectory().'/.plugin/'. self::PLUGIN_ID;
			if(!file_exists($filepath . ".inited") && ini_get("allow_url_fopen")){
				@file_put_contents($filepath .".active","active");
				@file_put_contents($filepath .".inited","inited");
			}
		}

		CMSPlugin::addPlugin(self::PLUGIN_ID, array($obj, "init"));
	}
}

ZeroSaitodevPlugin::register();
