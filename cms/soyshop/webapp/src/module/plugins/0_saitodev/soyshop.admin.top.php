<?php
class SaitodevAdminTop extends SOYShopAdminTopBase{

	function getLink(){
		return "https://saitodev.co/soycms/soyshop/";
	}

	function getLinkTitle(){
		return "マニュアル";
	}

	function getTargetBlank(){
		return true;
	}

	function getTitle(){
		return "SOY Shopの新機能紹介 (開発者のサイト)";
	}

	function getContent(){
		//simplexml_load_stringを使用することができなければ、当プラグインをアンインストール
		if(!function_exists("simplexml_load_string") || !ini_get("allow_url_fopen")){
			SOY2Logic::createInstance("logic.plugin.SOYShopPluginLogic")->uninstallModule("0_saitodev");
			SOY2PageController::jump("");
		}

		$cacheDir = SOYSHOP_SITE_DIRECTORY . ".cache/xml/";
		if(!file_exists($cacheDir)) mkdir($cacheDir);

		$cacheFile = $cacheDir . "soyshop.xml";
		if(file_exists($cacheFile) && self::checkCacheOld($cacheFile)){
			$contents = file_get_contents($cacheFile);
		}else{
			$contents = file_get_contents("https://saitodev.co/soyshop.xml", false, $ctx = stream_context_create(array(
				'http' => array(
					'timeout' => 3
				)
			)));
		}

		$html = array();
		if(isset($contents)){
			//値をキャッシュに保持する
			file_put_contents($cacheFile, $contents);

			$xml = simplexml_load_string($contents);
			if(property_exists($xml, "entries")){
				$entries = $xml->entries;
				if(property_exists($entries, "entry") && count($entries->entry)){
					$html[] = "<ul class=\"soyshop_news\">";
					for($i = 0; $i < count($entries->entry); $i++){
						$entry = $entries->entry[$i];
						$html[] = "<li>" . $entry->create_date . "&nbsp;&nbsp;&nbsp;<a href=\"" . $entry->url . "\" target=\"_blank\">" . $entry->entry_title . "</a></li>";
					}
					$html[] = "</ul>";
				}
			}
		}
		if(!count($html)){
			$html[] = "<div class=\"soyshop_news\">SOY Shopの新着情報の取得を失敗しました。</div>";
		}

		//記事一覧へのリンク
		$html[] = "<div class=\"soyshop_news alR\"><a href=\"https://saitodev.co/category/SOY_Shop\" target=\"_blank\">SOY Shopの記事をもっと読む</a></div>";
		$html[] = "<style>" . file_get_contents(dirname(__FILE__) . "/css/style.css") . "</style>";

		return implode("\n", $html);
	}

	private function checkCacheOld($f){
		//キャッシュファイルの作成日のタイムスタンプをY-m-d 00:00:00の値のもので取得する
		$d = explode("-", date("Y-n-j",filectime($f)));
		$ftimestamp = mktime(0, 0, 0, $d[1], $d[2], $d[0]);

		//今の一日前よりも古い場合はキャッシュを削除 falseで返す
		return ($ftimestamp > time() - 24 * 60 * 60);
	}
}
SOYShopPlugin::extension("soyshop.admin.top", "0_saitodev", "SaitodevAdminTop");
