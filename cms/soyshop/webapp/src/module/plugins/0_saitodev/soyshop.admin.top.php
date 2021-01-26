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
		if(self::checkDisplayAuth()){
			return "SOY Shopの新機能紹介 (開発者のサイト)";
		}
	}

	function getContent(){
		//simplexml_load_stringを使用することができなければ、当プラグインをアンインストール
		if(!function_exists("simplexml_load_string") || !ini_get("allow_url_fopen")){
			SOY2Logic::createInstance("logic.plugin.SOYShopPluginLogic")->uninstallModule("0_saitodev");
			SOY2PageController::jump("");
		}

		//表示する権限を調べる
		if(!self::checkDisplayAuth()) return "";

		$cacheDir = SOYSHOP_SITE_DIRECTORY . ".cache/xml/";
		if(!file_exists($cacheDir)) mkdir($cacheDir);

		$cacheFile = $cacheDir . "soyshop.xml";
		if(file_exists($cacheFile) && self::checkCacheOld($cacheFile)){
			$contents = file_get_contents($cacheFile);
		}else{
			$contents = @file_get_contents("https://saitodev.co/soyshop.xml", false, $ctx = stream_context_create(array(
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
			if(!is_bool($xml) && property_exists($xml, "entries")){
				$entries = $xml->entries;
				if(property_exists($entries, "entry") && count($entries->entry)){
					$html[] = "<div class=\"alert alert-info\">下記で紹介している機能を使用する場合はSOY Shopのバージョンアップを行って下さい。最新版のダウンロードは<a href=\"https://saitodev.co/soycms/soyshop\" target=\"_blank\" rel=\"noopener\" style=\"text-decoration:underline;\">こちら</a>から</div>";
					$html[] = "<ul class=\"soyshop_news\">";
					for($i = 0; $i < count($entries->entry); $i++){
						$entry = $entries->entry[$i];
						$html[] = "<li>" . $entry->create_date . "&nbsp;&nbsp;&nbsp;<a href=\"" . $entry->url . "\" target=\"_blank\" rel=\"noopener\">" . $entry->entry_title . "</a></li>";
					}
					$html[] = "</ul>";
				}
			}
		}
		if(!count($html)){
			$html[] = "<div class=\"soyshop_news\">SOY Shopの新着情報の取得を失敗しました。</div>";
		}

		//記事一覧へのリンク
		$html[] = "<div class=\"soyshop_news text-center\">";
		$html[] = "<a href=\"https://saitodev.co/category/SOY_Shop\" target=\"_blank\" rel=\"noopener\" class=\"btn btn-info\">SOY Shopの記事をもっと読む</a>";
		$html[] = "&nbsp;&nbsp;";
		$html[] = "<a href=\"https://saitodev.co/app/bulletin/board/topic/4\" target=\"_blank\" rel=\"noopener\" class=\"btn btn-warning\">SOY Shopの掲示板を開く</a>";
		$html[] = "</div>";
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

	private function checkDisplayAuth(){
		static $auth;
		if(is_null($auth)){
			SOY2::import("module.plugins.0_saitodev.util.SaitodevUtil");
			$conf = SaitodevUtil::getConfig();
			if(is_array($conf) && count($conf)){	//権限による非表示設定の値がある場合
				//権限を取得
				$session = SOY2ActionSession::getUserSession();

				//初期管理者であれば無条件でtrue
				if($session->getAttribute("isdefault") === 1){
					$auth = true;
				}else{
					$level = (int)$session->getAttribute("app_shop_auth_level");
					$auth = (!is_numeric(array_search($level, $conf)));
				}
			}else{
				$auth = true;	//非表示設定がなければ絶対にtrue
			}
		}

		return $auth;
	}

	function allowDisplay(){
		return (SOYShopAuthUtil::getAuth() != SOYShopAuthUtil::AUTH_STORE_OWNER);
	}
}
SOYShopPlugin::extension("soyshop.admin.top", "0_saitodev", "SaitodevAdminTop");
